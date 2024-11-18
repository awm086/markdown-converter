<?php

namespace Acme\TextTools\Markdown;

class HtmlDocumentRenderer implements DocumentRendererInterface
{
    /**
     * @var array<string, array{tag: string, attributes?: array<string, string>}>
     */
    private array $nodeConfig = [
        'Header' => [
            'tag' => 'h%level%',
        ],
        'Paragraph' => [
            'tag' => 'p',
        ],
        'Link' => [
            'tag' => 'a',
            'attributes' => [
                'href' => '%url%',
            ],
        ],
        'Text' => [
            'tag' => '',
        ],
    ];

    /**
     * HTML encode string for safe output
     */
    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function render(array $document): string
    {
        if ($document['type'] !== 'Document') {
            throw new \InvalidArgumentException('Root node must be of type Document');
        }

        $html = '';
        foreach ($document['children'] as $node) {
            $html .= $this->renderNode($node) . "\n";
        }

        return trim($html);
    }

    public function renderNode(array $node): string
    {
        if (!isset($node['type']) || !isset($this->nodeConfig[$node['type']])) {
            throw new \InvalidArgumentException("Unknown node type: {$node['type']}");
        }

        $config = $this->nodeConfig[$node['type']];

        switch ($node['type']) {
            case 'Text':
                return $this->escape($node['value']);

            case 'Link':
                return $this->renderTag(
                    $config['tag'],
                    $this->renderChildren($node),
                    ['href' => $node['url']]
                );

            case 'Header':
                $tag = str_replace('%level%', $node['level'], $config['tag']);
                return $this->renderTag($tag, $this->renderChildren($node));

            case 'Paragraph':
                return $this->renderTag($config['tag'], $this->renderChildren($node));

            default:
                throw new \InvalidArgumentException("Unsupported node type: {$node['type']}");
        }
    }

    private function renderChildren(array $node): string
    {
        $html = '';
        if (isset($node['children'])) {
            foreach ($node['children'] as $child) {
                $html .= $this->renderNode($child);
            }
        }
        return $html;
    }

    private function renderTag(string $tag, string $content, array $attributes = []): string
    {
        if (empty($tag)) {
            return $content;
        }

        $attributesStr = '';
        foreach ($attributes as $name => $value) {
            $attributesStr .= ' ' . $name . '="' . $this->escape($value) . '"';
        }

        return "<{$tag}{$attributesStr}>{$content}</{$tag}>";
    }
}