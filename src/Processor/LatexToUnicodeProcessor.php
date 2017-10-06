<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Processor;

use Pandoc\Pandoc;

class LatexToUnicodeProcessor extends AbstractProcessor
{
    /** @var Pandoc */
    private $pandoc;

    /**
     * @param string|array &$value The current tag value, will be modified in-place
     * @param string       $tag    The current tag name, by default this method will process all tags
     */
    public function __invoke(&$value, $tag)
    {
        if (!$this->isTagCovered($tag)) {
            return;
        }

        if (!$this->pandoc) {
            $this->pandoc = new Pandoc();
        }
        $decoder = function (&$text) {
            $text = $this->pandoc->runWith($text, [
                'from' => 'latex',
                'to' => 'plain',
            ]);
        };

        if (is_array($value)) {
            array_walk($value, $decoder);

            return;
        }

        $decoder($value);
    }
}