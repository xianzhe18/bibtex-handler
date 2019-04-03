<?php

namespace Xianzhe18\BibTexParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Test\DummyListener;

/**
 * @covers \RenanBr\BibTexParser\Parser
 */
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
        $dir = __DIR__.'/../resources/valid';

        return [
            'abbreviation' => [$dir.'/abbreviation.bib'],
            'basic' => [$dir.'/basic.bib'],
            'citation key' => [$dir.'/citation-key.bib'],
            'multiples entries' => [$dir.'/multiples-entries.bib'],
            'no value' => [$dir.'/no-tag-content.bib'],
            'uppercased tag' => [$dir.'/tag-name-uppercased.bib'],
            'tag with underscore' => [$dir.'/tag-name-with-underscore.bib'],
            'trailing comma' => [$dir.'/trailing-comma.bib'],
            'type overriding' => [$dir.'/type-overriding.bib'],
            'basic values' => [$dir.'/tag-contents-basic.bib'],
            'escaped values' => [$dir.'/tag-contents-escaped.bib'],
            'multiple values' => [$dir.'/tag-contents-multiple.bib'],
            'values with nested braces' => [$dir.'/tag-contents-nested-braces.bib'],
            'values with slashs' => [$dir.'/tag-contents-slashes.bib'],
        ];
    }
}
