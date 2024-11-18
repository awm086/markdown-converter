<?php

namespace App;

class MarkdownConverter
{
    private $parser;
    private $renderer;

    public function __construct()
    {
        $this->parser = new MarkdownParser();
        $this->renderer = new HtmlRenderer();
    }

    public function convertFile($composerEvent): void
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
        $html = $this->convertToHtml($content);
        
        $outputPath = pathinfo($filePath, PATHINFO_DIRNAME) . '/' . pathinfo($filePath, PATHINFO_FILENAME) . '.html';
        file_put_contents($outputPath, $html);
        
        echo "Conversion complete. Output file: $outputPath\n";
    }

    public function convertToHtml(string $markdown): string
    {
        $ast = $this->parser->parse($markdown);
        return $this->renderer->render($ast);
    }
}
