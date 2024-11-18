<?php

namespace App;

class MarkdownConverter
{
    public static function convertFile($composerEvent): void
    {
        // Get the arguments passed to the script
        $arguments = $composerEvent->getArguments();
        if (empty($arguments) || !isset($arguments[0])) {
            echo "Error: No file path provided.\n";
            return;
        }
        $filePath = $arguments[0];
        if (!file_exists($filePath)) {
            echo "Error: File not found.\n";
            return;
        }

        $content = file_get_contents($filePath);
        $html = self::convertToHtml($content);
        
        $outputPath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.html';
        file_put_contents($outputPath, $html);
        
        echo "Conversion complete. Output file: $outputPath\n";
    }

    public static function convertToHtml(string $markdown): string
    {
        // Split the markdown content into an array of lines
        $lines = explode("\n", $markdown);
        $html = '';

        // Process each line of markdown
        foreach ($lines as $line) {
            // Remove leading and trailing whitespace
            $line = trim($line);
            // Skip empty lines
            if (empty($line)) continue;

            // Check for headers (e.g., # Header, ## Subheader, etc.)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $level = strlen($matches[1]); // Determine header level (h1, h2, etc.)
                $html .= "<h{$level}>" . htmlspecialchars($matches[2]) . "</h{$level}>\n";
            } 
            // Check for links [link text](URL)
            elseif (preg_match('/^\[(.+?)\]\((.+?)\)$/', $line, $matches)) {
                $html .= '<a href="' . htmlspecialchars($matches[2]) . '">' . htmlspecialchars($matches[1]) . "</a>\n";
            } 
            // If not a header or link, treat as a paragraph
            else {
                $html .= "<p>" . $line . "</p>\n";
            }
        }

        return $html;
    }
}
