<?php declare(strict_types=1);

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\AbstractProcessor;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RenanBr\BibTexParser\Processor\TagCoverageTrait;

/**
 * @covers \RenanBr\BibTexParser\Processor\TagCoverageTrait
 */
class TagCoverageTraitTest extends TestCase
{
    public function testZeroConfigurationMustCoverAllTags(): void
    {
        $trait = $this->getMockForTrait(TagCoverageTrait::class);
        $coverage = $this->invokeGetCoveredTags($trait, ['bbb', 'ccc']);

        $this->assertSame(['bbb', 'ccc'], $coverage);
    }

    public function testWhitelistStrategy(): void
    {
        $trait = $this->getMockForTrait(TagCoverageTrait::class);
        $trait->setTagCoverage(['aaa', 'bbb'], 'whitelist');
        $coverage = $this->invokeGetCoveredTags($trait, ['bbb', 'ccc']);

        $this->assertSame(['bbb'], $coverage);
    }

    public function testDefaultStrategyMustActAsWhitelist(): void
    {
        $trait = $this->getMockForTrait(TagCoverageTrait::class);
        $trait->setTagCoverage(['aaa', 'bbb']);
        $coverage = $this->invokeGetCoveredTags($trait, ['bbb', 'ccc']);

        $this->assertSame(['bbb'], $coverage);
    }

    public function testBlacklist(): void
    {
        $trait = $this->getMockForTrait(TagCoverageTrait::class);
        $trait->setTagCoverage(['aaa', 'bbb'], 'blacklist');
        $coverage = $this->invokeGetCoveredTags($trait, ['bbb', 'ccc']);

        $this->assertSame(['ccc'], $coverage);
    }

    public function testCaseInsensitiveMatch(): void
    {
        $trait = $this->getMockForTrait(TagCoverageTrait::class);
        $trait->setTagCoverage(['aaa', 'bbb']);
        $coverage = $this->invokeGetCoveredTags($trait, ['BBB', 'ccc']);

        $this->assertSame(['BBB'], $coverage);
    }

    private function invokeGetCoveredTags($trait, $tags)
    {
        $reflection = new ReflectionClass($trait);
        $getCoveredTags = $reflection->getMethod('getCoveredTags');
        $getCoveredTags->setAccessible(true);

        return $getCoveredTags->invoke($trait, $tags);
    }
}
