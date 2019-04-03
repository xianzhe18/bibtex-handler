<?php

namespace Xianzhe18\BibTexParser\Test\Listener;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;

/**
 * @covers \RenanBr\BibTexParser\Listener
 */
class ProcessorTest extends TestCase
{
    public function testProcessorIsCalled()
    {
        $listener = new Listener();
        $listener->addProcessor(function ($entry) {
            $entry['type'] .= ' processed';
            $entry['foo'] .= ' processed';

            return $entry;
        });

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('basic processed', $entry['type']);
        $this->assertSame('bar processed', $entry['foo']);
    }

    public function testProcessorsAreCalledInCorrectOrder()
    {
        $listener = new Listener();
        $listener->addProcessor(function ($entry) {
            $entry['type'] .= ' 1';
            $entry['foo'] .= ' 1';

            return $entry;
        });
        $listener->addProcessor(function ($entry) {
            $entry['type'] .= ' 2';
            $entry['foo'] .= ' 2';

            return $entry;
        });

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('basic 1 2', $entry['type']);
        $this->assertSame('bar 1 2', $entry['foo']);
    }

    public function testDiscardingEntry()
    {
        $listener = new Listener();
        $listener->addProcessor(function ($entry) {
            if ('deleteMe' === $entry['type']) {
                return null;
            }

            return $entry;
        });

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseString('@keepMe{} @deleteMe{}');

        $entries = $listener->export();
        $this->assertCount(1, $entries);
        $this->assertSame('keepMe', $entries[0]['type']);
    }
}
