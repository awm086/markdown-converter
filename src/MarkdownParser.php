<?php

namespace App;

class MarkdownParser
{
    public function parse(string $markdown): array
    {
        $tokenizer = new Tokenizer($markdown);
        $ast = [];
        $currentNode = null;

        while (($token = $tokenizer->nextToken())['type'] !== 'EOF') {
            switch ($token['type']) {
                case 'HEADER':
                    $currentNode = [
                        'type' => 'header',
                        'level' => $token['level'],
                        'content' => $token['content'],
                    ];
                    $ast[] = $currentNode;
                    break;
                case 'LINK':
                    $linkNode = $this->parseLinkContent($token['content']);
                    if ($currentNode && $currentNode['type'] === 'paragraph') {
                        $currentNode['content'][] = $linkNode;
                    } else {
                        $currentNode = [
                            'type' => 'paragraph',
                            'content' => [$linkNode]
                        ];
                        $ast[] = $currentNode;
                    }
                    break;
                case 'TEXT':
                    if ($currentNode && ($currentNode['type'] === 'header' || $currentNode['type'] === 'paragraph')) {
                        if ($currentNode['type'] === 'header') {
                            $currentNode['content'] .= $token['content'];
                        } else {
                            $currentNode['content'][] = $token['content'];
                        }
                    } else {
                        $currentNode = [
                            'type' => 'paragraph',
                            'content' => [$token['content']]
                        ];
                        $ast[] = $currentNode;
                    }
                    break;
                case 'NEWLINE':
                    if ($currentNode && $currentNode['type'] === 'paragraph') {
                        $currentNode = null;
                    }
                    break;
            }
        }

        return $ast;
    }

    private function parseLinkContent(string $content): array
    {
        preg_match('/\[(.+)\]\((.+)\)/', $content, $matches);
        if (count($matches) === 3) {
            return [
                'type' => 'link',
                'text' => $matches[1],
                'url' => $matches[2]
            ];
        }
        return ['type' => 'text', 'content' => $content];
    }
}
