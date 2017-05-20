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

class TagValueProcessorTest extends TestCase
{
    public function testTagValueProcessor()
    {
        $listener = new Listener();
        $listener->setTagValueProcessor(function (&$text, $tag) {
            $text = "processed-$tag-$text";
        });

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('processed-type-basic', $entry['type']);
        $this->assertSame('processed-foo-bar', $entry['foo']);
    }
}
