<?php

namespace App;

class MarkdownParser
{
    public function parse(string $markdown): array
    {
        $lines = explode("\n", $markdown);
        $ast = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $matches)) {
                $ast[] = [
                    'type' => 'header',
                    'level' => strlen($matches[1]),
                    'content' => $matches[2]
                ];
            } elseif (preg_match('/^\[(.+?)\]\((.+?)\)$/', $line, $matches)) {
                $ast[] = [
                    'type' => 'link',
                    'text' => $matches[1],
                    'url' => $matches[2]
                ];
            } else {
                $ast[] = [
                    'type' => 'paragraph',
                    'content' => $line
                ];
            }
        }

        return $ast;
    }
}
