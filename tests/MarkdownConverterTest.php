<?php

namespace Tests;

use App\MarkdownConverter;
use PHPUnit\Framework\TestCase;

class MarkdownConverterTest extends TestCase
{
    private MarkdownConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new MarkdownConverter();
    }
    public function testConvertHeading1()
    {
        $markdown = "# Heading 1";
        $expected = "<h1>Heading 1</h1>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertHeading2()
    {
        $markdown = "## Heading 2";
        $expected = "<h2>Heading 2</h2>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertHeading6()
    {
        $markdown = "###### Heading 6";
        $expected = "<h6>Heading 6</h6>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertUnformattedText()
    {
        $markdown = "Unformatted text without hash";
        $expected = "<p>Unformatted text without hash</p>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertLink()
    {
        $markdown = "[Link text](link.com)";
        $expected = "<a href=\"link.com\">Link text</a>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertNestedLink()
    {
        $markdown = "[Link text [nested link text](link.com)](link.com)";
        $expected = "<a href=\"link.com\">Link text <a href=\"link.com\">nested link text</a></a>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testIgnoreBlankLine()
    {
        $markdown = "\n";
        $expected = "";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertNestedMarkdown()
    {
        $markdown = "# Main Heading\n\nThis is a [link with [nested] content](http://example.com) in a paragraph.\n\n## Sub Heading\n\nAnother [simple link](http://example.org).";
        $expected = "<h1>Main Heading</h1>\n<p>This is a <a href=\"http://example.com\">link with [nested] content</a> in a paragraph.</p>\n<h2>Sub Heading</h2>\n<p>Another <a href=\"http://example.org\">simple link</a>.</p>\n";
        $result = $this->converter->convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }
}
