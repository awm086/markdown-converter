<?php
namespace Acme\TextTools\Markdown\Tests;

use PHPUnit\Framework\TestCase;
use Acme\TextTools\Markdown\MarkdownDocumentParser;

class MarkdownDocumentParserTest extends TestCase {
    private MarkdownDocumentParser $parser;

    protected function setUp(): void {
        $this->parser = new MarkdownDocumentParser();
    }

    /**
     * @dataProvider headerProvider
     */
    public function testHeaderParsing(
        string $input,
        int $expectedLevel,
        array $expectedChildren
    ): void {
        $result = $this->parser->parse($input);
        $header = $result['children'][0];
        
        $this->assertEquals('Header', $header['type']);
        $this->assertEquals($expectedLevel, $header['level']);
        $this->assertEquals($expectedChildren, $header['children']);
    }

    public function headerProvider(): array {
        return [
            'simple h1' => [
                '# Header 1',
                1,
                [['type' => 'Text', 'value' => 'Header 1']]
            ],
            'h2 with link' => [
                '## Header with [link](http://example.com)',
                2,
                [
                    ['type' => 'Text', 'value' => 'Header with '],
                    [
                        'type' => 'Link',
                        'url' => 'http://example.com',
                        'children' => [['type' => 'Text', 'value' => 'link']]
                    ]
                ]
            ],
            'h6' => [
                '###### Deep header',
                6,
                [['type' => 'Text', 'value' => 'Deep header']]
            ],
            'header with multiple links' => [
                '# Start [first](http://first.com) middle [second](http://second.com) end',
                1,
                [
                    ['type' => 'Text', 'value' => 'Start '],
                    [
                        'type' => 'Link',
                        'url' => 'http://first.com',
                        'children' => [['type' => 'Text', 'value' => 'first']]
                    ],
                    ['type' => 'Text', 'value' => ' middle '],
                    [
                        'type' => 'Link',
                        'url' => 'http://second.com',
                        'children' => [['type' => 'Text', 'value' => 'second']]
                    ],
                    ['type' => 'Text', 'value' => ' end']
                ]
            ]
        ];
    }

    /**
     * @dataProvider paragraphProvider
     */
    public function testParagraphParsing(
        string $input,
        array $expectedChildren
    ): void {
        $result = $this->parser->parse($input);
        $paragraph = $result['children'][0];
        
        $this->assertEquals('Paragraph', $paragraph['type']);
        $this->assertEquals($expectedChildren, $paragraph['children']);
    }

    public function paragraphProvider(): array {
        return [
            'simple text' => [
                'Simple paragraph',
                [['type' => 'Text', 'value' => 'Simple paragraph']]
            ],
            'text with link' => [
                'Text with [link](http://example.com) in it',
                [
                    ['type' => 'Text', 'value' => 'Text with '],
                    [
                        'type' => 'Link',
                        'url' => 'http://example.com',
                        'children' => [['type' => 'Text', 'value' => 'link']]
                    ],
                    ['type' => 'Text', 'value' => ' in it']
                ]
            ],
            'nested links' => [
                'Start [outer [inner](http://inner.com) text](http://outer.com) end',
                [
                    ['type' => 'Text', 'value' => 'Start '],
                    [
                        'type' => 'Link',
                        'url' => 'http://outer.com',
                        'children' => [
                            ['type' => 'Text', 'value' => 'outer '],
                            [
                                'type' => 'Link',
                                'url' => 'http://inner.com',
                                'children' => [['type' => 'Text', 'value' => 'inner']]
                            ],
                            ['type' => 'Text', 'value' => ' text']
                        ]
                    ],
                    ['type' => 'Text', 'value' => ' end']
                ]
            ]
        ];
    }

    /**
     * @dataProvider documentProvider
     */
    public function testFullDocumentParsing(
        string $input,
        array $expectedNodes
    ): void {
        $result = $this->parser->parse($input);
        $this->assertEquals('Document', $result['type']);
        $this->assertEquals($expectedNodes, $result['children']);
    }

    public function documentProvider(): array {
        return [
            'mixed content' => [
                "# Header 1\nParagraph text\n## Header 2",
                [
                    [
                        'type' => 'Header',
                        'level' => 1,
                        'children' => [['type' => 'Text', 'value' => 'Header 1']]
                    ],
                    [
                        'type' => 'Paragraph',
                        'children' => [['type' => 'Text', 'value' => 'Paragraph text']]
                    ],
                    [
                        'type' => 'Header',
                        'level' => 2,
                        'children' => [['type' => 'Text', 'value' => 'Header 2']]
                    ]
                ]
            ],
            'complex document' => [
                "# Main Title [link](http://example.com)\nFirst paragraph\n## Subtitle\nSecond [nested [inner](http://inner.com)](http://outer.com) paragraph",
                [
                    [
                        'type' => 'Header',
                        'level' => 1,
                        'children' => [
                            ['type' => 'Text', 'value' => 'Main Title '],
                            [
                                'type' => 'Link',
                                'url' => 'http://example.com',
                                'children' => [['type' => 'Text', 'value' => 'link']]
                            ]
                        ]
                    ],
                    [
                        'type' => 'Paragraph',
                        'children' => [['type' => 'Text', 'value' => 'First paragraph']]
                    ],
                    [
                        'type' => 'Header',
                        'level' => 2,
                        'children' => [['type' => 'Text', 'value' => 'Subtitle']]
                    ],
                    [
                        'type' => 'Paragraph',
                        'children' => [
                            ['type' => 'Text', 'value' => 'Second '],
                            [
                                'type' => 'Link',
                                'url' => 'http://outer.com',
                                'children' => [
                                    ['type' => 'Text', 'value' => 'nested '],
                                    [
                                        'type' => 'Link',
                                        'url' => 'http://inner.com',
                                        'children' => [['type' => 'Text', 'value' => 'inner']]
                                    ]
                                ]
                            ],
                            ['type' => 'Text', 'value' => ' paragraph']
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider edgeCaseProvider
     */
    public function testEdgeCases(
        string $input,
        array $expectedResult
    ): void {
        $result = $this->parser->parse($input);
        $this->assertEquals($expectedResult, $result);
    }

    public function edgeCaseProvider(): array {
        return [
            'empty document' => [
                '',
                ['type' => 'Document', 'children' => []]
            ],
            'only whitespace' => [
                "  \n  \n  ",
                ['type' => 'Document', 'children' => []]
            ],
            'multiple empty lines between content' => [
                "# Header\n\n\nParagraph",
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Header',
                            'level' => 1,
                            'children' => [['type' => 'Text', 'value' => 'Header']]
                        ],
                        [
                            'type' => 'Paragraph',
                            'children' => [['type' => 'Text', 'value' => 'Paragraph']]
                        ]
                    ]
                ]
            ],
            'header without content is text' => [
                "#",
                [
                    'type' => 'Document',
                    'children' => [
                        [
                            'type' => 'Paragraph',
                            'children' => [['type' => 'Text', 'value' => '#']]
                        ]
                    ]
                ]
            ]
        ];
    }
}