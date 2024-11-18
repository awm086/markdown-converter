<?php

namespace Acme\TextTools\Markdown\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MarkdownDocumentParserUnitTest extends TestCase
{
    private $parser;
    private $reflection;

    protected function setUp(): void
    {
        $this->parser = new \Acme\TextTools\Markdown\MarkdownDocumentParser();
        $this->reflection = new ReflectionClass($this->parser);
    }

    private function setParserState(string $input, int $position = 0): void
    {
        $inputProperty = $this->reflection->getProperty('input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($this->parser, $input);

        $positionProperty = $this->reflection->getProperty('position');
        $positionProperty->setAccessible(true);
        $positionProperty->setValue($this->parser, $position);

        $lengthProperty = $this->reflection->getProperty('length');
        $lengthProperty->setAccessible(true);
        $lengthProperty->setValue($this->parser, strlen($input));
    }

    private function getPosition(): int
    {
        $positionProperty = $this->reflection->getProperty('position');
        $positionProperty->setAccessible(true);
        return $positionProperty->getValue($this->parser);
    }
    /**
     * @dataProvider peekProvider
     */
    public function testPeek(string $input, int $position, ?string $expected): void
    {
        $this->setParserState($input, $position);

        $peekMethod = $this->reflection->getMethod('peek');
        $peekMethod->setAccessible(true);

        $result = $peekMethod->invoke($this->parser);
        $this->assertSame($expected, $result);

        // Ensure peek doesn't advance position
        $this->assertSame($position, $this->getPosition());
    }

    public function peekProvider(): array
    {
        return [
            'peek at first character' => ['Hello', 0, 'H'],
            'peek at middle character' => ['Hello', 2, 'l'],
            'peek at last character' => ['Hello', 4, 'o'],
            'peek past end' => ['Hello', 5, null],
            'peek at empty string' => ['', 0, null],
            'peek at whitespace' => [" \t\n", 0, ' '],
            'peek at special character' => ['#Header', 0, '#'],
        ];
    }

    /**
     * @dataProvider consumeWhitespaceProvider
     */
    public function testConsumeWhitespace(
        string $input,
        int $startPosition,
        int $expectedEndPosition
    ): void {
        $this->setParserState($input, $startPosition);

        $consumeMethod = $this->reflection->getMethod('consumeWhitespace');
        $consumeMethod->setAccessible(true);

        $consumeMethod->invoke($this->parser);

        $this->assertSame($expectedEndPosition, $this->getPosition());
    }

    public function consumeWhitespaceProvider(): array
    {
        return [
            'no whitespace' => ['Hello', 0, 0],
            'single space' => [' Hello', 0, 1],
            'multiple spaces' => ['   Hello', 0, 3],
            'whitespace at end' => ['Hello   ', 5, 8],
            'only whitespace' => ['   ', 0, 3],
            'start in middle' => ['Hello  World', 5, 7],
        ];
    }
    /**
     * @dataProvider parseLinkProvider
     */
    public function testParseLink(
        string $input,
        int $startPosition,
        array $expectedResult,
        int $expectedEndPosition
    ): void {
        $this->setParserState($input, $startPosition);

        $parseLinkMethod = $this->reflection->getMethod('parseLink');
        $parseLinkMethod->setAccessible(true);

        $result = $parseLinkMethod->invoke($this->parser);

        // Verify the structure matches
        $this->assertEquals($expectedResult['type'], $result['type']);
        if ($result['type'] === 'Link') {
            $this->assertEquals($expectedResult['url'], $result['url']);
            $this->assertEquals($expectedResult['children'], $result['children']);
        } else {
            $this->assertEquals($expectedResult['value'], $result['value']);
        }

        // Verify final position
        $this->assertSame($expectedEndPosition, $this->getPosition());
    }

    public function parseLinkProvider(): array
    {
        return [
            'simple link' => [
                '[text](url)',
                0,
                [
                    'type' => 'Link',
                    'url' => 'url',
                    'children' => [
                        [
                            'type' => 'Text',
                            'value' => 'text'
                        ]
                    ]
                ],
                11
            ],
            'link with spaces' => [
                '[anchor text](http://example.com)',
                0,
                [
                    'type' => 'Link',
                    'url' => 'http://example.com',
                    'children' => [
                        [
                            'type' => 'Text',
                            'value' => 'anchor text'
                        ]
                    ]
                ],
                33
            ],
            'nested link' => [
                '[outer [inner](inner-url)](outer-url)',
                0,
                [
                    'type' => 'Link',
                    'url' => 'outer-url',
                    'children' => [
                        [
                            'type' => 'Text',
                            'value' => 'outer '
                        ],
                        [
                            'type' => 'Link',
                            'url' => 'inner-url',
                            'children' => [
                                [
                                    'type' => 'Text',
                                    'value' => 'inner'
                                ]
                            ]
                        ]
                    ]
                ],
                37
            ]
        ];
    }

    /**
     * @dataProvider malformedLinkProvider
     */
    public function testParseLinkWithMalformedInput(
        string $input,
        int $startPosition,
        array $expectedResult,
        int $expectedEndPosition
    ): void {
        $this->setParserState($input, $startPosition);

        $parseLinkMethod = $this->reflection->getMethod('parseLink');
        $parseLinkMethod->setAccessible(true);

        $result = $parseLinkMethod->invoke($this->parser);

        // Verify the structure matches
        $this->assertEquals($expectedResult['type'], $result['type']);
        $this->assertEquals($expectedResult['value'], $result['value']);

        // Verify final position
        $this->assertSame($expectedEndPosition, $this->getPosition());
    }

    public function malformedLinkProvider(): array
    {
        return [
            'unclosed bracket' => [
                '[text',
                0,
                [
                    'type' => 'Text',
                    'value' => '['
                ],
                1
            ],
            'unclosed parenthesis' => [
                '[text](url',
                0,
                [
                    'type' => 'Text',
                    'value' => '['
                ],
                1
            ],
            'missing url' => [
                '[text]',
                0,
                [
                    'type' => 'Text',
                    'value' => '['
                ],
                1
            ]
        ];
    }

    /**
     * @dataProvider isValidLinkProvider
     */
    public function testIsValidLink(string $input, int $startPosition, bool $expectedResult): void
    {
        $method = $this->reflection->getMethod('isValidLink');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->parser, [$input, $startPosition]);
        $this->assertSame($expectedResult, $result);
    }

    public function isValidLinkProvider(): array
    {
        return [
            'valid simple link' => ['[text](url)', 0, true],
            'valid link with spaces' => ['[anchor text](http://example.com)', 0, true],
            'valid nested link' => ['[outer [inner](url)](outer-url)', 0, true],
            'unclosed bracket' => ['[text', 0, false],
            'unclosed parenthesis' => ['[text](url', 0, false],
            'missing url section' => ['[text]', 0, false],
            'empty link' => ['[]()', 0, true],
            'no content' => ['[]', 0, false],
        ];
    }
}
