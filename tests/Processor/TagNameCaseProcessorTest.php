<?php

namespace Xianzhe18\BibTexParser\Test\Processor;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Processor\TagNameCaseProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\TagNameCaseProcessor
 */
class TagNameCaseProcessorTest extends TestCase
{
    public function testLower()
    {
        $listener = new Listener();
        $listener->addProcessor(new TagNameCaseProcessor(\CASE_LOWER));

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-name-uppercased.bib');

        $entries = $listener->export();

        $this->assertArrayHasKey('foo', $entries[0]);
        $this->assertArrayNotHasKey('FoO', $entries[0]);
        $this->assertSame('bAr', $entries[0]['foo']);
    }

    public function testUpper()
    {
        $listener = new Listener();
        $listener->addProcessor(new TagNameCaseProcessor(\CASE_UPPER));

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-name-uppercased.bib');

        $entries = $listener->export();

        $this->assertArrayHasKey('FOO', $entries[0]);
        $this->assertArrayNotHasKey('FoO', $entries[0]);
        $this->assertSame('bAr', $entries[0]['FOO']);
    }
}
