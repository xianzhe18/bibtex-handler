<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\ParseException;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/basic.bib');

        $this->assertCount(3, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('basic', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('foo', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('bar', $call['text']);
    }

    public function testKeyWithoutValue()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/no-value.bib');

        $this->assertCount(3, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('noValue', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('foo', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('bar', $call['text']);
    }

    public function testValueReading()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-basic.bib');

        $this->assertCount(13, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('valuesBasic', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kNull', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kStillNull', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kRaw', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('raw', $call['text']);

        $call = $listener->calls[5];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kBraced', $call['text']);

        $call = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame(' braced value ', $call['text']);

        $call = $listener->calls[7];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kBracedEmpty', $call['text']);

        $call = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('', $call['text']);

        $call = $listener->calls[9];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kQuoted', $call['text']);

        $call = $listener->calls[10];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame(' quoted value ', $call['text']);

        $call = $listener->calls[11];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('kQuotedEmpty', $call['text']);

        $call = $listener->calls[12];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('', $call['text']);
    }

    public function testValueScaping()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-escaped.bib');

        $this->assertCount(5, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('valuesEscaped', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('braced', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('the } " \\ % braced', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('quoted', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('the } " \\ % quoted', $call['text']);
    }

    public function testMultipleValues()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-multiple.bib');

        $this->assertCount(18, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('multipleValues', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('raw', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('rawA', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('rawB', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('quoted', $call['text']);

        $call = $listener->calls[5];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('quoted a', $call['text']);

        $call = $listener->calls[6];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('quoted b', $call['text']);

        $call = $listener->calls[7];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('braced', $call['text']);

        $call = $listener->calls[8];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('braced a', $call['text']);

        $call = $listener->calls[9];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('braced b', $call['text']);

        $call = $listener->calls[10];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('misc', $call['text']);

        $call = $listener->calls[11];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('quoted', $call['text']);

        $call = $listener->calls[12];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('braced', $call['text']);

        $call = $listener->calls[13];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('raw', $call['text']);

        $call = $listener->calls[14];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('noSpace', $call['text']);

        $call = $listener->calls[15];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('raw', $call['text']);

        $call = $listener->calls[16];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('quoted', $call['text']);

        $call = $listener->calls[17];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('braced', $call['text']);
    }

    public function testCommentIgnoring()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/comment.bib');

        $this->assertCount(9, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('comment', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('key', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('value', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('still', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('here', $call['text']);

        $call = $listener->calls[5];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('insideQuoted', $call['text']);

        $call = $listener->calls[6];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('before--after', $call['text']);

        $call = $listener->calls[7];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('commentAfterKey', $call['text']);

        $call = $listener->calls[8];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('commentAfterRaw', $call['text']);
    }

    public function testValueSlashes()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-slashes.bib');

        $this->assertCount(5, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('valuesSlashes', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('braced', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('\\}\\"\\%\\', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('quoted', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::QUOTED_VALUE, $call['state']);
        $this->assertSame('\\}\\"\\%\\', $call['text']);
    }

    public function testValueNestedBraces()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-nested-braces.bib');

        $this->assertCount(7, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('valuesBraces', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('link', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('\url{https://github.com}', $call['text']);

        $call = $listener->calls[3];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('twoLevels', $call['text']);

        $call = $listener->calls[4];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('a{b{c}d}e', $call['text']);

        $call = $listener->calls[5];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('escapedBrace', $call['text']);

        $call = $listener->calls[6];
        $this->assertSame(Parser::BRACED_VALUE, $call['state']);
        $this->assertSame('before{}}after', $call['text']);
    }

    public function testFileDoesNotExist()
    {
        $parser = new Parser;

        $this->expectException(\PHPUnit_Framework_Error_Warning::class);
        $parser->parseFile(__DIR__ . '/resources/does-not-exist');
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($file, $message)
    {
        $parser = new Parser;

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($message);
        $parser->parseFile($file);
    }

    public function invalidProvider()
    {
        $dir = __DIR__ . '/resources/invalid';
        return [
            [$dir . '/brace-missing.bib', "'\\0' at line 3 column 1"],
            [$dir . '/multiple-braced-values.bib', "'{' at line 2 column 33"],
            [$dir . '/multiple-quoted-values.bib', "'\"' at line 2 column 33"],
            [$dir . '/multiple-raw-values.bib', "'b' at line 2 column 31"],
            [$dir . '/space-after-at-sign.bib', "' ' at line 1 column 2"],
            [$dir . '/splitted-key.bib', "'k' at line 2 column 14"],
            [$dir . '/splitted-type.bib', "'T' at line 1 column 11"],
            [$dir . '/no-comment.bib', "'i' at line 1 column 1"],
            [$dir . '/double-concat.bib', "'#' at line 2 column 20"],
        ];
    }

    public function testStringParser()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseString(file_get_contents(__DIR__ . '/resources/basic.bib'));

        $this->assertCount(3, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('basic', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('foo', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('bar', $call['text']);
    }

    public function testBasicOffsetContext()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/basic.bib');

        $this->assertCount(3, $listener->calls);

        $context = $listener->calls[0]['context'];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(5, $context['length']);

        $context = $listener->calls[1]['context'];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame(13, $context['offset']);
        $this->assertSame(3, $context['length']);

        $context = $listener->calls[2]['context'];
        $this->assertSame(Parser::RAW_VALUE, $context['state']);
        $this->assertSame(19, $context['offset']);
        $this->assertSame(3, $context['length']);
    }

    public function testOffsetContextWithEscapedChar()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);

        // This file is interesting because the values have escaped chars, which
        // means the value length in the file is not equal to the triggered one
        $parser->parseFile(__DIR__ . '/resources/values-escaped.bib');

        $this->assertCount(5, $listener->calls);

        $context = $listener->calls[0]['context'];
        $this->assertSame(Parser::TYPE, $context['state']);
        $this->assertSame(1, $context['offset']);
        $this->assertSame(13, $context['length']);

        $context = $listener->calls[1]['context'];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame(21, $context['offset']);
        $this->assertSame(6, $context['length']);

        $context = $listener->calls[2]['context'];
        $this->assertSame(Parser::BRACED_VALUE, $context['state']);
        $this->assertSame(31, $context['offset']);
        $this->assertSame(21, $context['length']);

        $context = $listener->calls[3]['context'];
        $this->assertSame(Parser::KEY, $context['state']);
        $this->assertSame(59, $context['offset']);
        $this->assertSame(6, $context['length']);

        $context = $listener->calls[4]['context'];
        $this->assertSame(Parser::QUOTED_VALUE, $context['state']);
        $this->assertSame(69, $context['offset']);
        $this->assertSame(21, $context['length']);
    }

    public function testTrailingComma()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/trailing-comma.bib');

        $this->assertCount(3, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('trailingComma', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('foo', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('bar', $call['text']);
    }

    public function testTagNameWithUnderscore()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/tag-name-with-underscore.bib');

        $this->assertCount(3, $listener->calls);

        $call = $listener->calls[0];
        $this->assertSame(Parser::TYPE, $call['state']);
        $this->assertSame('tagNameWithUnderscore', $call['text']);

        $call = $listener->calls[1];
        $this->assertSame(Parser::KEY, $call['state']);
        $this->assertSame('foo_bar', $call['text']);

        $call = $listener->calls[2];
        $this->assertSame(Parser::RAW_VALUE, $call['state']);
        $this->assertSame('fubar', $call['text']);
    }
}
