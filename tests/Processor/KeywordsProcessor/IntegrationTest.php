<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\KeywordsProcessor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Processor\KeywordsProcessor;

class IntegrationTest extends TestCase
{
    public function testUsage()
    {
        $listener = new Listener();
        $listener->addTagContentProcessor(new KeywordsProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../../resources/valid/keywords-simple.bib');
        $entries = $listener->export();

        // Some sanity checks to make sure KeywordsProcessor didn't screw the rest of the entry
        $this->assertCount(1, $entries);
        $this->assertCount(4, $entries[0]);
        $this->assertSame('keywordsSimple', $entries[0]['type']);
        $this->assertSame('keyOfKeywords', $entries[0]['citation-key']);

        $this->assertCount(2, $entries[0]['keywords']);
        $this->assertSame('foo', $entries[0]['keywords'][0]);
        $this->assertSame('bar', $entries[0]['keywords'][1]);
    }
}
