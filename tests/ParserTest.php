<?php declare (strict_types = 1);

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

        $expected = [
            ['type', 'basic'],
            ['key', 'foo'],
            ['value', 'bar', Parser::RAW_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testKeyWithoutValue()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/no-value.bib');

        $expected = [
            ['type', 'noValue'],
            ['key', 'foo'],
            ['key', 'bar'],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testValueReading()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-basic.bib');

        $expected = [
            ['type', 'valuesBasic'],
            ['key', 'kNull'],
            ['key', 'kRaw'],
                ['value', 'raw', Parser::RAW_VALUE],
            ['key', 'kBraced'],
                ['value', ' braced value ', Parser::DELIMITED_VALUE],
            ['key', 'kBracedEmpty'],
                ['value', '', Parser::DELIMITED_VALUE],
            ['key', 'kQuoted'],
                ['value', ' quoted value ', Parser::DELIMITED_VALUE],
            ['key', 'kQuotedEmpty'],
                ['value', '', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testValueScaping()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-escaped.bib');

        $expected = [
            ['type', 'valuesEscaped'],
            ['key', 'braced'],
                ['value', 'the } " \\ % braced', Parser::DELIMITED_VALUE],
            ['key', 'quoted'],
                ['value', 'the } " \\ % quoted', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testMultipleValues()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-multiple.bib');

        $expected = [
            ['type', 'multipleValues'],
            ['key', 'raw'],
                ['value', 'rawA', Parser::RAW_VALUE],
                ['value', 'rawB', Parser::RAW_VALUE],
            ['key', 'quoted'],
                ['value', 'quoted a', Parser::DELIMITED_VALUE],
                ['value', 'quoted b', Parser::DELIMITED_VALUE],
            ['key', 'braced'],
                ['value', 'braced a', Parser::DELIMITED_VALUE],
                ['value', 'braced b', Parser::DELIMITED_VALUE],
            ['key', 'misc'],
                ['value', 'quoted', Parser::DELIMITED_VALUE],
                ['value', 'braced', Parser::DELIMITED_VALUE],
                ['value', 'raw', Parser::RAW_VALUE],
            ['key', 'noSpace'],
                ['value', 'raw', Parser::RAW_VALUE],
                ['value', 'quoted', Parser::DELIMITED_VALUE],
                ['value', 'braced', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testCommentIgnoring()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/comment.bib');

        $expected = [
            ['type', 'comment'],
            ['key', 'key'],
                ['value', 'value', Parser::RAW_VALUE],
            ['key', 'still'],
                ['value', 'here', Parser::RAW_VALUE],
            ['key', 'insideQuoted'],
                ['value', 'before--after', Parser::DELIMITED_VALUE],
            ['key', 'commentAfterKey'],
                ['value', 'commentAfterRaw', Parser::RAW_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testValueSlashes()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-slashes.bib');

        $expected = [
            ['type', 'valuesSlashes'],
            ['key', 'braced'],
                ['value', '\\}\\"\\%\\', Parser::DELIMITED_VALUE],
            ['key', 'quoted'],
                ['value', '\\}\\"\\%\\', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testValueNestedBraces()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-nested-braces.bib');

        $expected = [
            ['type', 'valuesBraces'],
            ['key', 'link'],
                ['value', '\url{https://github.com}', Parser::DELIMITED_VALUE],
            ['key', 'twoLevels'],
                ['value', 'a{b{c}d}e', Parser::DELIMITED_VALUE],
            ['key', 'escapedBrace'],
                ['value', 'before{}}after', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testFileDoesNotExist()
    {
        $parser = new Parser;

        $this->setExpectedException(\PHPUnit_Framework_Error_Warning::class);
        $parser->parseFile(__DIR__ . '/resources/does-not-exist');
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $file, string $message)
    {
        $parser = new Parser;

        $this->setExpectedException(ParseException::class);
        $this->expectExceptionMessage($message);
        $parser->parseFile($file);
    }

    public function invalidProvider(): array
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
            [$dir . '/trailing-comma.bib', "'}' at line 3 column 1"],
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

        $expected = [
            ['type', 'basic'],
            ['key', 'foo'],
            ['value', 'bar', Parser::RAW_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }
}
