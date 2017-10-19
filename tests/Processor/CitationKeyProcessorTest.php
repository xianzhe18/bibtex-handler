<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\CitationKeyProcessor;

class CitationKeyProcessorTest extends TestCase
{
    public function testCitationKeyDetecting()
    {
        $listener = new Listener();
        $listener->addProcessor(new CitationKeyProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/citation-key.bib');

        $entries = $listener->export();

        $this->assertFalse(array_key_exists('Someone2016', $entries[0]));
        $this->assertTrue(array_key_exists('citation-key', $entries[0]));
        $this->assertSame('Someone2016', $entries[0]['citation-key']);
    }

    public function testNullFilling()
    {
        $listener = new Listener();
        $listener->addProcessor(new CitationKeyProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();

        $this->assertTrue(array_key_exists('citation-key', $entries[0]));
        $this->assertNull($entries[0]['citation-key']);
    }
}
