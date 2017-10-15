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

class BasicTest extends TestCase
{
    public function testBasic()
    {
        $listener = new DummyListener;

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/basic.bib');

        $this->assertCount(4, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('basic', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(5, $context['length']);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('foo', $text);
        $this->assertSame(13, $context['offset']);
        $this->assertSame(3, $context['length']);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_TAG_CONTENT, $context['state']);
        $this->assertSame('bar', $text);
        $this->assertSame(19, $context['offset']);
        $this->assertSame(3, $context['length']);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/basic.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(24, $context['length']);
    }
}
