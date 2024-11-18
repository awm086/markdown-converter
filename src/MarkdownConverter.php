<?php

namespace App;

use App\Tokenizer;
class MarkdownConverter
{
    private $parser;
    public function __construct()
    {
        $this->parser = new MarkdownParser();
    }

    static function convertFile($composerEvent): void
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
         $converter = new MarkdownConverter();
        $html = $converter->convertToHtml($content);

        $outputPath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.html';
        file_put_contents($outputPath, $html);

        echo "Conversion complete. Output file: $outputPath\n";
    }

    public function convertToHtml(string $markdown): string
    {
        // get the lines 
        $html = $this->parser->parse($markdown);
        return $html;
    }
}
