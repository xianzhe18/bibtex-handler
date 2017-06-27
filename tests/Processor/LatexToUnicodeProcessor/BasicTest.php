<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Processor\LatexToUnicodeProcessor;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Processor\LatexToUnicodeProcessor;

class BasicTest extends TestCase
{
    public function testTextAsInput()
    {
        $latex = "tr\\`{e}s bien";
        $processor = new LatexToUnicodeProcessor();
        $processor($latex, 'no-matter');

        $this->assertSame('très bien', $latex);
    }

    public function testArrayAsInput()
    {
        $latex = [
            'foo' => "f\\'{u}",
            'bar' => "b{\\aa}r",
        ];
        $processor = new LatexToUnicodeProcessor();
        $processor($latex, 'no-matter');

        $this->assertCount(2, $latex);
        $this->assertSame('fú', $latex['foo']);
        $this->assertSame('bår', $latex['bar']);
    }
}
