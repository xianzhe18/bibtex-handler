<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\KeywordsProcessor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\KeywordsProcessor;

class BasicTest extends TestCase
{
    public function testCommaAsSeparator()
    {
        $keywords = 'foo, bar';
        $processor = new KeywordsProcessor();
        $processor($keywords, 'keywords');

        $this->assertCount(2, $keywords);
        $this->assertSame('foo', $keywords[0]);
        $this->assertSame('bar', $keywords[1]);
    }

    public function testSemicolonAsSeparator()
    {
        $keywords = 'foo; bar';
        $processor = new KeywordsProcessor();
        $processor($keywords, 'keywords');

        $this->assertCount(2, $keywords);
        $this->assertSame('foo', $keywords[0]);
        $this->assertSame('bar', $keywords[1]);
    }

    /** @see https://github.com/retorquere/zotero-better-bibtex/issues/361 */
    public function testCommaAsTagContent()
    {
        $keywords = '1,2-diol, propargyl alcohol, reaction of, triphosgene';
        $processor = new KeywordsProcessor();
        $processor($keywords, 'keywords');

        $this->assertCount(4, $keywords);
        $this->assertSame('1,2-diol', $keywords[0]);
        $this->assertSame('propargyl alcohol', $keywords[1]);
        $this->assertSame('reaction of', $keywords[2]);
        $this->assertSame('triphosgene', $keywords[3]);
    }
}
