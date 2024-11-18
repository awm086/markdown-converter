<?php

namespace App;

/**
 * MarkdownParser class
 * 
 * This class is responsible for parsing Markdown text and converting it to HTML.
 * It currently supports headers (h1 to h6) and links (including nested links).
 */
class MarkdownParser
{
    /**
     * Parse the given Markdown text and convert it to HTML
     *
     * @param string $markdown The input Markdown text
     * @return string The resulting HTML
     */
    public function parse(string $markdown): string {
        $lines = explode("\n", $markdown);
        $html = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue; // Skip empty lines
            }

            // Check if the line is a header
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]); // Determine header level (h1 to h6)
                $content = $this->parseInline($matches[2]);
                $html .= "<h{$level}>{$content}</h{$level}>\n";
            } else {
                // If not a header, treat as a paragraph
                $content = $this->parseInline($line);
                $html .= "<p>{$content}</p>\n";
            }
        }

        return $html;
    }

    /**
     * Parse inline Markdown elements (currently only links)
     *
     * @param string $text The text to parse for inline elements
     * @return string The text with inline elements converted to HTML
     */
    private function parseInline(string $text): string {
        // Parse links
        $text = preg_replace_callback(
            '/\[([^\]]+)\]\(([^\)]+)\)/',
            function ($matches) {
                $linkText = $this->parseInline($matches[1]); // Allow nested parsing
                return "<a href=\"{$matches[2]}\">{$linkText}</a>";
            },
            $text
        );

        return $text;
    }
}
