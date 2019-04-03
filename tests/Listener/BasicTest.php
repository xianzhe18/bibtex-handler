<?php

namespace Xianzhe18\BibTexParser\Test\Listener;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;

/**
 * @covers \RenanBr\BibTexParser\Listener
 */
class BasicTest extends TestCase
{
    public function testBasicReading()
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/basic.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('basic', $entry['type']);
        $this->assertSame('bar', $entry['foo']);
    }

    public function testTypeOverriding()
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/type-overriding.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('new type value', $entry['type']);
        $this->assertSame('bar', $entry['foo']);
    }
}
