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

class TagContentProcessorTest extends TestCase
{
    public function testAddTagContentProcessor()
    {
        $listener = new Listener();
        $listener->addTagContentProcessor(function (&$text, $tag) {
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

    public function testAddTagContentProcessorOrder()
    {
        $listener = new Listener();
        $addA = function (&$text, $tag) {
            $text .= "A";
        };
        $addB = function (&$text, $tag) {
            $text .= "B";
        };

        $listener->addTagContentProcessor($addA);
        $listener->addTagContentProcessor($addB);

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('basicAB', $entry['type']);
        $this->assertSame('barAB', $entry['foo']);
    }

    public function testAddTagContentProcessorArrays()
    {
        $listener = new Listener();
        $addA = function (&$text, $tag) {
            $text .= "A";
        };
        $addB = function (&$text, $tag) {
            $text .= "B";
        };
        $addC = function (&$text, $tag) {
            $text .= "C";
        };

        $listener->addTagContentProcessor([$addA, $addB]);
        $listener->addTagContentProcessor([$addC]);

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('basicABC', $entry['type']);
        $this->assertSame('barABC', $entry['foo']);
    }

    public function testInvalidAddTagContentProcessor()
    {
        $listener = new Listener();
        $this->expectException(\InvalidArgumentException::class);
        $listener->addTagContentProcessor("foo");
    }

    public function testInvalidAddTagContentProcessorInArray()
    {
        $listener = new Listener();
        $this->expectException(\InvalidArgumentException::class);
        $listener->addTagContentProcessor(["foo", "bar"]);
    }

    public function testAddTagContentProcessorWithCallableArray()
    {
        $listener = new Listener();
        $my_callable_array = ['RenanBr\BibTexParser\Test\DummyProcessor', 'myCallbackMethod'];
        $listener->addTagContentProcessor($my_callable_array);

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $entries = $listener->export();
        $entry = $entries[0];
        $this->assertSame('dummy-callback-basic', $entry['type']);
        $this->assertSame('dummy-callback-bar', $entry['foo']);
    }
}
