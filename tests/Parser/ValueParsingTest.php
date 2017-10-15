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

class ValueParsingTest extends TestCase
{
    /**
     * Tests if parser is able to handle raw, null, braced and quoted values ate the same time.
     */
    public function testMultipleNature()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/values-basic.bib');

        $this->assertCount(14, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesBasic', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kNull', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kStillNull', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kRaw', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kBraced', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame(' braced value ', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kBracedEmpty', $text);

        list($text, $context) = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('', $text);

        list($text, $context) = $listener->calls[9];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kQuoted', $text);

        list($text, $context) = $listener->calls[10];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame(' quoted value ', $text);

        list($text, $context) = $listener->calls[11];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('kQuotedEmpty', $text);

        list($text, $context) = $listener->calls[12];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('', $text);

        list($text, $context) = $listener->calls[13];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/values-basic.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueScaping()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/values-escaped.bib');

        $this->assertCount(6, $listener->calls);

        // we test also the "offset" and "length" because this file contains
        // values with escaped chars, which means that the value length in the
        // file is not equal to the triggered one

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesEscaped', $text);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(13, $context['length']);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('braced', $text);
        $this->assertSame(21, $context['offset']);
        $this->assertSame(6, $context['length']);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % braced', $text);
        $this->assertSame(31, $context['offset']);
        $this->assertSame(21, $context['length']);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('quoted', $text);
        $this->assertSame(59, $context['offset']);
        $this->assertSame(6, $context['length']);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        // here we have two scaped characters ("}" and "%"), then the length
        // returned in the context (21) is bigger than the $text value (18)
        $this->assertSame('the } " \\ % quoted', $text);
        $this->assertSame(69, $context['offset']);
        $this->assertSame(21, $context['length']);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/values-escaped.bib'));
        $this->assertSame($original, $text);
        $this->assertSame(0, $context['offset']);
        $this->assertSame(93, $context['length']);
    }

    public function testMultipleValues()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/values-multiple.bib');

        $this->assertCount(19, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('multipleValues', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rawA', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('rawB', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted a', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted b', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced a', $text);

        list($text, $context) = $listener->calls[9];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced b', $text);

        list($text, $context) = $listener->calls[10];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('misc', $text);

        list($text, $context) = $listener->calls[11];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[12];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[13];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[14];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('noSpace', $text);

        list($text, $context) = $listener->calls[15];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame('raw', $text);

        list($text, $context) = $listener->calls[16];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[17];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[18];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/values-multiple.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueSlashes()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/values-slashes.bib');

        $this->assertCount(6, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesSlashes', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('braced', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('\\}\\"\\%\\', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('quoted', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame('\\}\\"\\%\\', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/values-slashes.bib'));
        $this->assertSame($original, $text);
    }

    public function testValueNestedBraces()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/../resources/valid/values-nested-braces.bib');

        $this->assertCount(8, $listener->calls);

        list($text, $context) = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame('valuesBraces', $text);

        list($text, $context) = $listener->calls[1];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('link', $text);

        list($text, $context) = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('\url{https://github.com}', $text);

        list($text, $context) = $listener->calls[3];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('twoLevels', $text);

        list($text, $context) = $listener->calls[4];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('a{b{c}d}e', $text);

        list($text, $context) = $listener->calls[5];
        $this->assertSame(Parser::TAG_NAME, $context['state']);
        $this->assertSame('escapedBrace', $text);

        list($text, $context) = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame('before{}}after', $text);

        list($text, $context) = $listener->calls[7];
        $this->assertSame(Parser::ORIGINAL_ENTRY, $context['state']);
        $original = trim(file_get_contents(__DIR__ . '/../resources/valid/values-nested-braces.bib'));
        $this->assertSame($original, $text);
    }
}
