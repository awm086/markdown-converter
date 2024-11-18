<?php

namespace Acme\TextTools\Markdown;

interface DocumentRendererInterface
{
    /**
     * Renders a document AST to the target format
     *
     * @param array $document The document AST to render
     * @return string The rendered document
     */
    public function render(array $document): string;

    /**
     * Renders a single node from the AST
     *
     * @param array $node The node to render
     * @return string The rendered node
     */
    public function renderNode(array $node): string;
}