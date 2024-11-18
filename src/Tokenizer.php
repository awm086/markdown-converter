<?php

namespace App;

class Tokenizer
{
    private $input;
    private $position;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->position = 0;
    }

    public function nextToken(): array
    {
        if ($this->position >= strlen($this->input)) {
            return ['type' => 'EOF'];
        }

        $char = $this->input[$this->position];

        if ($char === '#') {
            return $this->headerToken();
        } elseif ($char === '[') {
            return $this->linkToken();
        } elseif ($char === "\n") {
            $this->position++;
            return ['type' => 'NEWLINE'];
        } else {
            return $this->textToken();
        }
    }

    private function headerToken(): array
    {
        $level = 0;
        $start = $this->position;
        while ($this->position < strlen($this->input) && $this->input[$this->position] === '#') {
            $level++;
            $this->position++;
        }
        if ($this->position < strlen($this->input) && $this->input[$this->position] === ' ') {
            $this->position++; // Skip the space after #
        }
        $contentStart = $this->position;
        while ($this->position < strlen($this->input) && $this->input[$this->position] !== "\n") {
            $this->position++;
        }
        $content = trim(substr($this->input, $contentStart, $this->position - $contentStart));
        return ['type' => 'HEADER', 'level' => $level, 'content' => $content];
    }

    private function linkToken(): array
    {
        $start = $this->position;
        $nesting = 1;
        $this->position++;

        while ($this->position < strlen($this->input) && $nesting > 0) {
            if ($this->input[$this->position] === '[') {
                $nesting++;
            } elseif ($this->input[$this->position] === ']') {
                $nesting--;
            }
            $this->position++;
        }

        if ($this->position < strlen($this->input) && $this->input[$this->position] === '(') {
            while ($this->position < strlen($this->input) && $this->input[$this->position] !== ')') {
                $this->position++;
            }
            $this->position++; // Include the closing parenthesis
        }

        $content = substr($this->input, $start, $this->position - $start);
        return ['type' => 'LINK', 'content' => $content];
    }

    private function textToken(): array
    {
        $start = $this->position;
        while ($this->position < strlen($this->input) && 
               $this->input[$this->position] !== '#' && 
               $this->input[$this->position] !== '[' && 
               $this->input[$this->position] !== "\n") {
            $this->position++;
        }
        $content = substr($this->input, $start, $this->position - $start);
        return ['type' => 'TEXT', 'content' => trim($content)];
    }
}
