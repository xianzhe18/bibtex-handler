<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Listener;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

class KeyReadingTest extends TestCase
{
    public function testWhenFirstKeyIsNullItMustBeReadAsTypeValueInstead()
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/citation-key.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('citationKey', $entry['type']);
        $this->assertSame('Someone2016', $entry['citation-key']);
        $this->assertSame('bar', $entry['foo']);
    }

    public function testMultipleNullKeys()
    {
        $listener = new Listener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/no-value.bib');

        $entries = $listener->export();
        $this->assertCount(1, $entries);

        $entry = $entries[0];
        $this->assertSame('noValue', $entry['type']);
        $this->assertSame('foo', $entry['citation-key']);
        $this->assertNull($entry['bar']);
    }
}
