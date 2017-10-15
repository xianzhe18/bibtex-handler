<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Test\DummyListener;

class MultipleEntriesTest extends TestCase
{
    public function testMultipleEntries()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/multiples-entries.bib');

        $this->assertCount(8, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('entryFooWithSpaces', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('foo', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('oof', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $this->assertSame('@entryFooWithSpaces { foo = oof }', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('entryBarWithoutSpaces', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('bar', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rab', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $this->assertSame('@entryBarWithoutSpaces{bar=rab}', $text);
    }
}
