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
            'citation-key' => 'foo',
            'bar' => null,
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);

        // because assertEquals() doesn't check variable type
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
            'citation-key' => 'kNull',
            'kStillNull' => null,
            'kRaw' => 'raw',
            'kBraced' => ' braced value ',
            'kBracedEmpty' => '',
            'kQuoted' => ' quoted value ',
            'kQuotedEmpty' => '',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);

        // because assertEquals() doesn't check variable type
        $this->assertNull($actual[0]['kStillNull']);
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

    public function testTypeOverriding()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/type-overriding.bib');

        $expected = [[
            'type' => 'new type value',
            'foo' => 'bar',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);
    }

    public function testCitationKey()
    {
        $listener = new Listener;

        $parser = new Parser;
        $parser->addListener($listener);
        $parser->parseFile(__DIR__ . '/resources/citation-key.bib');

        $expected = [[
            'type' => 'citationKey',
            'citation-key' => 'Someone2016',
            'foo' => 'bar',
        ]];
        $actual = $listener->export();
        $this->assertEquals($expected, $actual);
    }

    public function testTagNameCase()
    {
        $listenerStandard = new Listener;

        $listenerUpper = new Listener;
        $listenerUpper->setTagNameCase(\CASE_UPPER);

        $listenerLower = new Listener;
        $listenerLower->setTagNameCase(\CASE_LOWER);

        $parser = new Parser;
        $parser->addListener($listenerStandard);
        $parser->addListener($listenerUpper);
        $parser->addListener($listenerLower);
        $parser->parseFile(__DIR__ . '/resources/tag-name-uppercased.bib');

        $expectedStandard = [[
            'type' => 'tagNameUppercased',
            'FoO' => 'bAr',
        ]];
        $actualStandard = $listenerStandard->export();
        ksort($expectedStandard);
        ksort($actualStandard);
        $this->assertEquals($expectedStandard, $actualStandard);

        $expectedUpper = [[
            'TYPE' => 'tagNameUppercased',
            'FOO' => 'bAr',
        ]];
        $actualUpper = $listenerUpper->export();
        ksort($expectedUpper);
        ksort($actualUpper);
        $this->assertEquals($expectedUpper, $actualUpper);

        $expectedLower = [[
            'type' => 'tagNameUppercased',
            'foo' => 'bAr',
        ]];
        $actualLower = $listenerLower->export();
        ksort($expectedLower);
        ksort($actualLower);
        $this->assertEquals($expectedLower, $actualLower);
    }
}
