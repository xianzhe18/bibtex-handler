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

class StringParsingTest extends TestCase
{
    /** @dataProvider validFileProvider */
    public function testStringParserAndFileParserMustWorkIdentically($file)
    {
        $listenerFile = new DummyListener();
        $parserFile = new Parser();
        $parserFile->addListener($listenerFile);
        $parserFile->parseFile($file);

        $listenerString = new DummyListener();
        $parserString = new Parser();
        $parserString->addListener($listenerString);
        $parserString->parseString(file_get_contents($file));

        $this->assertSame($listenerFile->calls, $listenerString->calls);
    }

    public function validFileProvider()
    {
        $dir = __DIR__ . '/../resources/valid';

        return [
            'abbreviation' => [$dir . '/abbreviation.bib'],
            'basic' => [$dir . '/basic.bib'],
            'citation key' => [$dir . '/citation-key.bib'],
            'multiples entries' => [$dir . '/multiples-entries.bib'],
            'no value' => [$dir . '/no-value.bib'],
            'uppercased tag' => [$dir . '/tag-name-uppercased.bib'],
            'tag with underscore' => [$dir . '/tag-name-with-underscore.bib'],
            'trailing comma' => [$dir . '/trailing-comma.bib'],
            'type overriding' => [$dir . '/type-overriding.bib'],
            'basic values' => [$dir . '/values-basic.bib'],
            'escaped values' => [$dir . '/values-escaped.bib'],
            'multiple values' => [$dir . '/values-multiple.bib'],
            'values with nested braces' => [$dir . '/values-nested-braces.bib'],
            'values with slashs' => [$dir . '/values-slashes.bib'],
        ];
    }
}
