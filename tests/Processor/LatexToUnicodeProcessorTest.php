<?php

namespace Xianzhe18\BibTexParser\Test\Processor;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Processor\LatexToUnicodeProcessor;

/**
 * @covers \RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor
 */
class LatexToUnicodeProcessorTest extends TestCase
{
    public function testTextAsInput()
    {
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => 'tr\\`{e}s bien',
        ]);

        $this->assertSame('très bien', $entry['text']);
    }

    public function testArrayAsInput()
    {
        $processor = new LatexToUnicodeProcessor();
        $entry = $processor([
            'text' => [
                'foo' => "f\\'{u}",
                'bar' => 'b{\\aa}r',
            ],
        ]);

        $this->assertSame([
            'foo' => 'fú',
            'bar' => 'bår',
        ], $entry['text']);
    }

    public function testThroughListener()
    {
        $listener = new Listener();
        $listener->addProcessor(new LatexToUnicodeProcessor());

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/tag-contents-latex.bib');

        $entries = $listener->export();

        // Some sanity checks to make sure it didn't screw the rest of the entry
        $this->assertCount(3, $entries[0]);
        $this->assertSame('tagContentLatex', $entries[0]['type']);
        $this->assertInternalType('string', $entries[0]['_original']);

        $this->assertSame('cafés', $entries[0]['consensus']);
    }
}
