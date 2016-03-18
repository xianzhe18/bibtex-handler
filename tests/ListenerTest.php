<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Listener;

class ListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/basic.bib');

        $expected = [[
            'type' => 'basic',
            'foo' => 'bar',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);
    }

    public function testNullableKey()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/no-value.bib');

        $expected = [[
            'type' => 'noValue',
            'foo' => null,
            'bar' => null,
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);

        // because assertEquals() doesn't check variable type
        $this->assertNull($actual[0]['foo']);
        $this->assertNull($actual[0]['bar']);
    }

    public function testValueReading()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-basic.bib');

        $expected = [[
            'type' => 'valuesBasic',
            'kNull' => null,
            'kRaw' => 'raw',
            'kBraced' => ' braced value ',
            'kBracedEmpty' => '',
            'kQuoted' => ' quoted value ',
            'kQuotedEmpty' => '',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);

        // because assertEquals() doesn't check variable type
        $this->assertNull($actual[0]['kNull']);
        $this->assertSame('', $actual[0]['kBracedEmpty']);
        $this->assertSame('', $actual[0]['kQuotedEmpty']);
    }

    public function testValueConcatenation()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/values-multiple.bib');

        $expected = [[
            'type' => 'multipleValues',
            'raw' => 'rawArawB',
            'quoted' => 'quoted aquoted b',
            'braced' => 'braced abraced b',
            'misc' => 'quotedbracedraw',
            'noSpace' => 'rawquotedbraced',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);
    }

    public function testAbbreviation()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/abbreviation.bib');

        $expected = [[
            'type' => 'string',
            'me' => 'Renan',
            'emptyAbbr' => '',
            'nullAbbr' => null,
            'meImportant' => 'Sir Renan'
        ], [
            'type' => 'string',
            'meAccordingToMyMother' => 'Glamorous Sir Renan',
        ], [
            'type' => 'abbreviation',
            'message' => 'Hello Glamorous Sir Renan!',
            'skip' => 'me',
            'mustEmpty' => '',
            'mustNull' => null
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);

        // because assertEquals() doesn't check variable type
        $this->assertSame('', $actual[0]['emptyAbbr']);
        $this->assertNull($actual[0]['nullAbbr']);
        $this->assertSame('', $actual[2]['mustEmpty']);
        $this->assertNull($actual[2]['mustNull']);
    }
}
