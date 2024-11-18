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
                case 'link':
                    $html .= '<a href="' . htmlspecialchars($node['url']) . '">' . htmlspecialchars($node['text']) . "</a>\n";
                    break;
                case 'paragraph':
                    $html .= "<p>" . htmlspecialchars($node['content']) . "</p>\n";
                    break;
            }
        }

        return $html;
    }
}
