<?php

namespace Xianzhe18\BibTexParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Test\DummyListener;

/**
 * @covers \RenanBr\BibTexParser\Parser
 */
class TagNameParsingTest extends TestCase
{
    public function testTagNameWithoutTagContent()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/no-tag-content.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $type, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $type);
        $this->assertSame('noTagContent', $text);

        list($text, $type, $context) = $listener->calls[1];
        $this->assertSame(Parser::CITATION_KEY, $type);
        $this->assertSame('foo', $text);

        list($text, $type, $context) = $listener->calls[2];
        $this->assertSame(Parser::TAG_NAME, $type);
        $this->assertSame('bar', $text);

        list($text, $type, $context) = $listener->calls[3];
        $this->assertSame(Parser::ENTRY, $type);
        $original = trim(file_get_contents(__DIR__.'/../resources/valid/no-tag-content.bib'));
        $this->assertSame($original, $text);
    }
}
