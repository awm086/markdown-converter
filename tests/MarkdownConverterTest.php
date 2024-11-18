<?php

namespace Tests;

use App\MarkdownConverter;
use PHPUnit\Framework\TestCase;

class MarkdownConverterTest extends TestCase
{
    public function testConvertHeading1()
    {
        $markdown = "# Heading 1";
        $expected = "<h1>Heading 1</h1>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertHeading2()
    {
        $markdown = "## Heading 2";
        $expected = "<h2>Heading 2</h2>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertHeading6()
    {
        $markdown = "###### Heading 6";
        $expected = "<h6>Heading 6</h6>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertUnformattedText()
    {
        $markdown = "Unformatted text without hash";
        $expected = "<p>Unformatted text without hash</p>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertLink()
    {
        $markdown = "[Link text](link.com)";
        $expected = "<a href=\"link.com\">Link text</a>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testConvertNestedLink()
    {
        $markdown = "[Link text [nested link text](link.com)](link.com)";
        $expected = "<a href=\"link.com\">Link text <a href=\"link.com\">nested link text</a></a>\n";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }

    public function testIgnoreBlankLine()
    {
        $markdown = "\n";
        $expected = "";
        $result = MarkdownConverter::convertToHtml($markdown);
        $this->assertEquals($expected, $result);
    }
}
