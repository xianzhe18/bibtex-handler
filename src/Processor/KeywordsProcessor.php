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

class KeywordsProcessor extends AbstractProcessor
{
    public function __construct()
    {
        $this->setTagCoverage(['keywords']);
    }

    /**
     * @param string $value The current tag value, will be modified in-place
     * @param string $tag   The current tag name, by default this method will only process "keywords" tags
     */
    public function __invoke(&$value, $tag)
    {
        if (!$this->isTagCovered($tag)) {
            return;
        }

        $value = preg_split('/, |; /', $value);
    }
}
