<?php

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
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor\AbstractProcessor;

class TagCoverageTest extends TestCase
{
    private function invokeIsTagCovered($processor, $tag)
    {
        $reflection = new ReflectionClass($processor);
        $isTagCovered = $reflection->getMethod('isTagCovered');
        $isTagCovered->setAccessible(true);

        return $isTagCovered->invokeArgs($processor, [$tag]);
    }

    public function testWhitelistStrategy()
    {
        $processor = $this->getMockForAbstractClass(AbstractProcessor::class);
        $processor->setTagCoverage(['foo'], 'whitelist');

        $this->assertTrue($this->invokeIsTagCovered($processor, 'foo'));
        $this->assertFalse($this->invokeIsTagCovered($processor, 'bar'));
    }

    public function testBlacklistStrategy()
    {
        $processor = $this->getMockForAbstractClass(AbstractProcessor::class);
        $processor->setTagCoverage(['foo'], 'blacklist');

        $this->assertFalse($this->invokeIsTagCovered($processor, 'foo'));
        $this->assertTrue($this->invokeIsTagCovered($processor, 'bar'));
    }

    public function testZeroConfigurationMustCoverageAll()
    {
        $processor = $this->getMockForAbstractClass(AbstractProcessor::class);

        $this->assertTrue($this->invokeIsTagCovered($processor, 'foo'));
        $this->assertTrue($this->invokeIsTagCovered($processor, 'bar'));
    }

    public function testDefaultStrategyMustUseWhitelistStrategy()
    {
        $processor = $this->getMockForAbstractClass(AbstractProcessor::class);
        $processor->setTagCoverage(['foo']);

        $this->assertTrue($this->invokeIsTagCovered($processor, 'foo'));
        $this->assertFalse($this->invokeIsTagCovered($processor, 'bar'));
    }
}
