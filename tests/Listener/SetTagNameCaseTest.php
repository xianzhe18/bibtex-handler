<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Listener;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;

class SetTagNameCaseTest extends TestCase
{
    public function testSetTagNameCase()
    {
        $listenerStandard = new Listener();

        $listenerUpper = new Listener();
        $listenerUpper->setTagNameCase(\CASE_UPPER);

        $listenerLower = new Listener();
        $listenerLower->setTagNameCase(\CASE_LOWER);

        $parser = new Parser();
        $parser->addListener($listenerStandard);
        $parser->addListener($listenerUpper);
        $parser->addListener($listenerLower);
        $parser->parseFile(__DIR__ . '/../resources/valid/tag-name-uppercased.bib');

        $entries = $listenerStandard->export();
        $this->assertCount(1, $entries);
        $entry = $entries[0];
        $this->assertSame('tagNameUppercased', $entry['type']);
        $this->assertSame('bAr', $entry['FoO']);

        $entries = $listenerUpper->export();
        $this->assertCount(1, $entries);
        $entry = $entries[0];
        $this->assertSame('tagNameUppercased', $entry['TYPE']);
        $this->assertSame('bAr', $entry['FOO']);

        $entries = $listenerLower->export();
        $this->assertCount(1, $entries);
        $entry = $entries[0];
        $this->assertSame('tagNameUppercased', $entry['type']);
        $this->assertSame('bAr', $entry['foo']);
    }
}
