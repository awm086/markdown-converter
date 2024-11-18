<?php

namespace Acme\TextTools\Markdown\Tests;

use PHPUnit\Framework\TestCase;
use Acme\TextTools\Markdown\HtmlDocumentRenderer;

class HtmlDocumentRendererTest extends TestCase
{
    private HtmlDocumentRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new HtmlDocumentRenderer();
    }

    /**
     * @dataProvider documentProvider
     */
    public function testDocumentRendering(array $document, string $expectedHtml): void
    {
        $result = $this->renderer->render($document);
        $this->assertEquals($expectedHtml, $result);
    }

    public function documentProvider(): array
    {
        return [
            'simple document' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Header',
                            'level' => 1,
                            'children' => [
                                ['type' => 'Text', 'value' => 'Hello World']
                            ]
                        ],
                        [
                            'type' => 'Paragraph',
                            'children' => [
                                ['type' => 'Text', 'value' => 'This is a paragraph']
                            ]
                        ]
                    ]
                ],
                "<h1>Hello World</h1>\n<p>This is a paragraph</p>"
            ],
            'document with links' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Paragraph',
                            'children' => [
                                ['type' => 'Text', 'value' => 'Click '],
                                [
                                    'type' => 'Link',
                                    'url' => 'http://example.com',
                                    'children' => [
                                        ['type' => 'Text', 'value' => 'here']
                                    ]
                                ],
                                ['type' => 'Text', 'value' => ' now']
                            ]
                        ]
                    ]
                ],
                '<p>Click <a href="http://example.com">here</a> now</p>'
            ],
            'document with special characters' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Paragraph',
                            'children' => [
                                ['type' => 'Text', 'value' => 'Special chars: < > & " \'']
                            ]
                        ]
                    ]
                ],
                '<p>Special chars: &lt; &gt; &amp; &quot; &apos;</p>'
            ],
            'nested links' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Paragraph',
                            'children' => [
                                ['type' => 'Text', 'value' => 'Start '],
                                [
                                    'type' => 'Link',
                                    'url' => 'http://outer.com',
                                    'children' => [
                                        ['type' => 'Text', 'value' => 'outer '],
                                        [
                                            'type' => 'Link',
                                            'url' => 'http://inner.com',
                                            'children' => [
                                                ['type' => 'Text', 'value' => 'inner']
                                            ]
                                        ],
                                        ['type' => 'Text', 'value' => ' text']
                                    ]
                                ],
                                ['type' => 'Text', 'value' => ' end']
                            ]
                        ]
                    ]
                ],
                '<p>Start <a href="http://outer.com">outer <a href="http://inner.com">inner</a> text</a> end</p>'
            ]
        ];
    }

    /**
     * @dataProvider edgeCaseProvider
     */
    public function testEdgeCases(array $document, string $expectedHtml): void
    {
        $result = $this->renderer->render($document);
        $this->assertEquals($expectedHtml, $result);
    }

    public function edgeCaseProvider(): array
    {
        return [
            'empty document' => [
                ['type' => 'Document', 'children' => []],
                ''
            ],
            'empty header' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Header',
                            'level' => 1,
                            'children' => []
                        ]
                    ]
                ],
                '<h1></h1>'
            ],
            'empty paragraph' => [
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Paragraph',
                            'children' => []
                        ]
                    ]
                ],
                '<p></p>'
            ]
        ];
    }

    public function testThrowsExceptionForInvalidDocument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->render(['type' => 'InvalidType']);
    }

    public function testThrowsExceptionForInvalidNode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->renderer->renderNode(['type' => 'InvalidType']);
    }
}