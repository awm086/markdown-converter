<?php

namespace App;

class HtmlRenderer
{
    public function render(array $ast): string
    {
        $html = '';

        foreach ($ast as $node) {
            switch ($node['type']) {
                case 'header':
                    $html .= "<h{$node['level']}>" . htmlspecialchars($node['content']) . "</h{$node['level']}>\n";
                    break;
                case 'paragraph':
                    $html .=  $this->renderParagraphContent($node['content']);
                    break;
            }
        }

        return $html;
    }

    private function renderParagraphContent(array $content): string
    {
        $result = '';
        foreach ($content as $item) {
            if (is_string($item)) {
                $result .= "<p>" . htmlspecialchars($item) . "</p>\n";
            } elseif (is_array($item) && $item['type'] === 'link') {
                $result .= '<a href="' . htmlspecialchars($item['url']) . '">' . htmlspecialchars($item['text']) . '</a>';
            }
        }
        return $result;
    }
}
