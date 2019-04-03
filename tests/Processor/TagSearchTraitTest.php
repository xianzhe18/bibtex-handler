<?php

namespace Xianzhe18\BibTexParser\Test\Processor;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Xianzhe18\BibTexParser\Processor\TagSearchTrait;

/**
 * @covers \RenanBr\BibTexParser\Processor\TagSearchTrait
 */
class TagSearchTraitTest extends TestCase
{
    public function testFound()
    {
        $trait = $this->getMockForTrait(TagSearchTrait::class);
        $found = $this->invokeTagSearch($trait, 'foo', ['foo', 'bar']);

        $this->assertSame('foo', $found);
    }

    public function testNotFound()
    {
        $trait = $this->getMockForTrait(TagSearchTrait::class);
        $found = $this->invokeTagSearch($trait, 'missing', ['foo', 'bar']);

        $this->assertNull($found);
    }

    public function testCaseInsensitiveMatch()
    {
        $trait = $this->getMockForTrait(TagSearchTrait::class);
        $found = $this->invokeTagSearch($trait, 'BAR', ['foo', 'bar']);

        $this->assertSame('bar', $found);
    }

    private function invokeTagSearch($trait, $needle, $haystack)
    {
        $reflection = new ReflectionClass($trait);
        $tagSearch = $reflection->getMethod('tagSearch');
        $tagSearch->setAccessible(true);

        return $tagSearch->invoke($trait, $needle, $haystack);
    }
}
