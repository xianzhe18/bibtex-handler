<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/basic.bib');

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
        $parser->parse(__DIR__ . '/resources/no-value.bib');

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
        $parser->parse(__DIR__ . '/resources/values-basic.bib');

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
        $parser->parse(__DIR__ . '/resources/values-escaped.bib');

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
        $parser->parse(__DIR__ . '/resources/values-multiple.bib');

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
        $parser->parse(__DIR__ . '/resources/comment.bib');

        $expected = [
            ['type', 'comment'],
            ['key', 'key'],
                ['value', 'value', Parser::RAW_VALUE],
            ['key', 'still'],
                ['value', 'here', Parser::RAW_VALUE],
            ['key', 'insideQuoted'],
                ['value', 'before--after', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testValueSlashes()
    {
        $listener = new DummyListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/values-slashes.bib');

        $expected = [
            ['type', 'valuesSlashes'],
            ['key', 'braced'],
                ['value', '\\}\\"\\%\\', Parser::DELIMITED_VALUE],
            ['key', 'quoted'],
                ['value', '\\}\\"\\%\\', Parser::DELIMITED_VALUE],
        ];

        $this->assertEquals($expected, $listener->calls);
    }

    public function testFileDoesNotExist()
    {
        $parser = new Parser;

        $this->setExpectedException(\PHPUnit_Framework_Error_Warning::class);
        $parser->parse(__DIR__ . '/resources/does-not-exist');
    }
}
