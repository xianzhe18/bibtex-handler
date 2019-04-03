<?php

namespace Xianzhe18\BibTexParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Parser;
use Xianzhe18\BibTexParser\Test\DummyListener;

/**
 * @covers \RenanBr\BibTexParser\Parser
 */
class CommentTest extends TestCase
{
    public function testCommentOnly()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/comment-only.bib');

        $this->assertCount(0, $listener->calls);
    }

    public function testCommenEntryMustBeIgnored()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/comment-entry.bib');

        $this->assertCount(0, $listener->calls);
    }

    public function testCommenEntryJabRefStyleMustBeIgnored()
    {
        $listener = new DummyListener();

        $parser = new Parser();
        $parser->addListener($listener);
        $parser->parseFile(__DIR__.'/../resources/valid/comment-jabref.bib');

        $this->assertCount(0, $listener->calls);
    }
}
