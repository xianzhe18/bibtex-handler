<?php

namespace Xianzhe18\BibTexParser\Test\Processor;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Processor\KeywordsProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\KeywordsProcessor
 */
class KeywordsProcessorTest extends TestCase
{
    public function testCommaAsSeparator()
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => 'foo, bar',
        ]);

        $this->assertSame(['foo', 'bar'], $entry['keywords']);
    }

    public function testSemicolonAsSeparator()
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => 'foo; bar',
        ]);

        $this->assertSame(['foo', 'bar'], $entry['keywords']);
    }

    /** @see https://github.com/retorquere/zotero-better-bibtex/issues/361 */
    public function testCommaAsTagContent()
    {
        $processor = new KeywordsProcessor();
        $entry = $processor([
            'keywords' => '1,2-diol, propargyl alcohol, reaction of, triphosgene',
        ]);

        $this->assertSame([
            '1,2-diol',
            'propargyl alcohol',
            'reaction of',
            'triphosgene',
        ], $entry['keywords']);
    }

    public function testThroughListener()
    {
        $listener = new Listener();
        $listener->addProcessor(new KeywordsProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/keywords-simple.bib');

        $entries = $listener->export();

        // Some sanity checks to make sure it didn't screw the rest of the entry
        $this->assertCount(3, $entries[0]);
        $this->assertSame('keywordsSimple', $entries[0]['type']);
        $this->assertInternalType('string', $entries[0]['_original']);

        $this->assertSame(['foo', 'bar'], $entries[0]['keywords']);
    }
}
