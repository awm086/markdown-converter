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
    public function parse(string $markdown): string
    {
        // Normalize all line endings 
        $nomalizedText = str_replace(["\r\n", "\r"], "\n", $markdown);
        $lines = explode("\n", $nomalizedText);
        //  ignore empty line 
        $lines = array_filter(array_map(fn($s) => trim($s), $lines));

        $html = '';

        foreach ($lines as $line) {

            // Check if the line is a header
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]); // Determine header level (h1 to h6)
                $content = $this->parseInline($matches[2]);
                $html .= "<h{$level}>{$content}</h{$level}>\n";
            } else {
                // If not a header, treat as a text
                $content = $this->parseInline($line);
                $html .= "<p>{$content}</p>\n";
            }
        }

        return $html;
    }

    /**
     * Parse inline links
     *
     * @param string $text The text to parse for inline elements
     * @return string The text with inline elements converted to HTML
     */
    private function parseInline(string $text): string
    {

        $html = $text;
        if (strpos($text, '](') !== FALSE && preg_match_all('/\[([^\]]+)\]\(([^\)]+)\)/', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $raw = $match[0];
                $linkText = $match[1];
                $url = $match[2];
                $element = '<a href="' . $url . '">' . $linkText . '</a>';
                // @todo, can we use substitution?
                $html = str_replace($raw, $element, $html);
            }
        }

        return $html;
    }

}
