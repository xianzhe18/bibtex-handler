<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $listener = new LogListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/basic.bib');

        $expected = [
            ['type', 'basic'],
            ['key', 'foo'],
            ['value', 'bar', true],
        ];

        $this->assertEquals($expected, $listener->log);
    }

    public function testKeyWithoutValue()
    {
        $listener = new LogListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/no-value.bib');

        $expected = [
            ['type', 'noValue'],
            ['key', 'foo'],
            ['key', 'bar'],
        ];

        $this->assertEquals($expected, $listener->log);
    }

    public function testValueReading()
    {
        $listener = new LogListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/values-basic.bib');

        $expected = [
            ['type', 'valuesBasic'],
            ['key', 'kRaw'],
                ['value', 'raw', true],
            ['key', 'kBraced'],
                ['value', ' braced value ', false],
            ['key', 'kQuoted'],
                ['value', ' quoted value ', false],
        ];

        $this->assertEquals($expected, $listener->log);
    }

    public function testValueScaping()
    {
        $listener = new LogListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/values-escaped.bib');

        $expected = [
            ['type', 'valuesEscaped'],
            ['key', 'braced'],
                ['value', 'the } " \\ braced', false],
            ['key', 'quoted'],
                ['value', 'the } " \\ quoted', false],
        ];

        $this->assertEquals($expected, $listener->log);
    }

    public function testMultipleValues()
    {
        $listener = new LogListener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parse(__DIR__ . '/resources/values-multiple.bib');

        $expected = [
            ['type', 'multipleValues'],
            ['key', 'raw'],
                ['value', 'rawA', true],
                ['value', 'rawB', true],
            ['key', 'quoted'],
                ['value', 'quoted a', false],
                ['value', 'quoted b', false],
            ['key', 'braced'],
                ['value', 'braced a', false],
                ['value', 'braced b', false],
            ['key', 'misc'],
                ['value', 'quoted', false],
                ['value', 'braced', false],
                ['value', 'raw', true],
            ['key', 'noSpace'],
                ['value', 'raw', true],
                ['value', 'quoted', false],
                ['value', 'braced', false],
        ];

        $this->assertEquals($expected, $listener->log);
    }
}
