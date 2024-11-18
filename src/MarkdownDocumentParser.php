<?php
namespace Acme\TextTools\Markdown;

class MarkdownDocumentParser
{
    private string $input;
    private int $position = 0;
    private int $length;

    public function parse(string $markdown): array
    {
        $this->input = $this->sanitizie($markdown);
        $this->position = 0;
        $this->length = strlen($this->input);
      
        return [
            'type' => 'Document',
            'children' => $this->parseNodes()
        ];
    }

    public function sanitizie(string $markdown): string
    {
        // Remove empty lines first
        $markdown = preg_replace('/^\s*\n/m', '', $markdown);
        return trim($markdown);
    }

    public function parseNodes(): array
    {
        $nodes = [];

        while ($this->position < $this->length) {
            if ($this->peek() === "\n") {
                $this->position++;
                continue;
            }

            if ($this->peek() === '#') {
                // Store current position to revert if needed
                $startPosition = $this->position;

                // Count number of # symbols
                $level = 0;
                while ($this->position < $this->length && $this->peek() === '#' && $level < 6) {
                    $level++;
                    $this->position++;
                }

                // Skip whitespace after #
                $this->consumeWhitespace();

                if ($this->headerHasContent()) {
                    $nodes[] = [
                        'type' => 'Header',
                        'level' => $level,
                        'children' => $this->parseInlineElements("\n")
                    ];
                } else {
                    // No content after #, treat as paragraph
                    $this->position = $startPosition;
                    $nodes[] = $this->parseParagraph();
                }
            } else {
                $nodes[] = $this->parseParagraph();
            }
        }

        return $nodes;
    }

    private function headerHasContent(): bool
    {
        $tempPosition = $this->position;
        
        while ($tempPosition < $this->length && $this->input[$tempPosition] !== "\n") {
            if (!ctype_space($this->input[$tempPosition])) {
                return true;
            }
            $tempPosition++;
        }
        
        return false;
    }

    private function isValidLink(string $input, int $startPos): bool
    {
        if ($startPos >= strlen($input) - 2) { // Need at least []
            return false;
        }

        // Find closing bracket
        $bracketPos = $startPos;
        $bracketDepth = 1;
        while (++$bracketPos < strlen($input)) {
            if ($input[$bracketPos] === '[') {
                $bracketDepth++;
            } elseif ($input[$bracketPos] === ']') {
                $bracketDepth--;
                if ($bracketDepth === 0) {
                    break;
                }
            }
        }

        // No matching closing bracket or it's the last character
        if ($bracketDepth !== 0 || $bracketPos >= strlen($input) - 1) {
            return false;
        }

        // Check for URL part
        if ($input[$bracketPos + 1] !== '(') {
            return false;
        }

        // Find closing parenthesis
        $parenPos = $bracketPos + 1;
        while (++$parenPos < strlen($input)) {
            if ($input[$parenPos] === ')') {
                return true;
            }
        }

        return false;
    }

    public function parseHeader(): array
    {
        $level = 0;
        while ($this->position < $this->length && $this->peek() === '#' && $level < 6) {
            $level++;
            $this->position++;
        }

        $this->consumeWhitespace();

        return [
            'type' => 'Header',
            'level' => $level,
            'children' => $this->parseInlineElements("\n")
        ];
    }

    public function parseParagraph(): array
    {
        return [
            'type' => 'Paragraph',
            'children' => $this->parseInlineElements("\n")
        ];
    }

    public function parseInlineElements(string $terminator): array
    {
        $elements = [];
        $textBuffer = '';

        while ($this->position < $this->length && $this->peek() !== $terminator) {
            if ($this->peek() === '[') {
                // Check if it's a valid link
                if ($this->isValidLink($this->input, $this->position)) {
                    if ($textBuffer !== '') {
                        $elements[] = [
                            'type' => 'Text',
                            'value' => $textBuffer
                        ];
                        $textBuffer = '';
                    }
                    $elements[] = $this->parseLink();
                } else {
                    // Treat as regular text if link is malformed
                    $textBuffer .= $this->input[$this->position++];
                }
            } else {
                $textBuffer .= $this->input[$this->position++];
            }
        }

        if ($textBuffer !== '') {
            $elements[] = [
                'type' => 'Text',
                'value' => rtrim($textBuffer)  // Keep leading spaces, trim only trailing
            ];
        }

        if ($this->position < $this->length && $this->peek() === $terminator) {
            $this->position++;
        }

        return $elements;
    }

    private function parseLink(): array
    {
        if (!$this->isValidLink($this->input, $this->position)) {
            $text = $this->input[$this->position++];
            return [
                'type' => 'Text',
                'value' => $text
            ];
        }

        $this->position++; // Skip [

        $linkText = [];
        $textBuffer = '';

        while ($this->position < $this->length && $this->peek() !== ']') {
            if ($this->peek() === '[') {
                if ($textBuffer !== '') {
                    $linkText[] = [
                        'type' => 'Text',
                        'value' => $textBuffer
                    ];
                    $textBuffer = '';
                }
                $linkText[] = $this->parseLink();
            } else {
                $textBuffer .= $this->input[$this->position++];
            }
        }

        if ($textBuffer !== '') {
            $linkText[] = [
                'type' => 'Text',
                'value' => $textBuffer
            ];
        }

        if ($this->position < $this->length) {
            $this->position++; // Skip ]
        }

        $url = '';
        if ($this->peek() === '(') {
            $this->position++; // Skip (
            while ($this->position < $this->length && $this->peek() !== ')') {
                $url .= $this->input[$this->position++];
            }
            if ($this->position < $this->length) {
                $this->position++; // Skip )
            }
        }

        return [
            'type' => 'Link',
            'url' => $url,
            'children' => $linkText
        ];
    }

    private function peek(): ?string
    {
        return $this->position < $this->length ? $this->input[$this->position] : null;
    }

    private function consumeWhitespace(): void
    {
        while ($this->position < $this->length && ctype_space($this->peek())) {
            $this->position++;
        }
    }
}