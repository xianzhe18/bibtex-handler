<?php

namespace Xianzhe18\BibTexParser\Test\Listener;

use PHPUnit\Framework\TestCase;
use Xianzhe18\BibTexParser\Listener;
use Xianzhe18\BibTexParser\Parser;

/**
 * @covers \RenanBr\BibTexParser\Listener
 */
class ReusableTest extends TestCase
{
    public function testListenerKeepWorkAmongParseCalls()
    {
        $parser = new Parser();
        $listener = new Listener();
        $listener->addProcessor(function (array $entry) {
            $entry['title'] .= ' processed';

            return $entry;
        });
        $parser->addListener($listener);

        $parser->parseString('@misc{title="A"}');
        $entries = $listener->export(); // <--- first call

        $this->assertSame('A processed', $entries[0]['title']);

        $parser->parseString('@misc{title="B"}');
        $entries = $listener->export(); // <--- second call

        $this->assertSame('A processed', $entries[0]['title']);
        $this->assertSame('B processed', $entries[1]['title']);
    }
}
