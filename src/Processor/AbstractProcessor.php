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

abstract class AbstractProcessor
{
    /** @var array */
    private $tagCoverageList = [];

    /** @var string */
    private $tagCoverageStrategy = 'blacklist';

    /**
     * @param array  $tags     List of tags to be covered
     * @param string $strategy Can assume "whitelist" (default) or "blacklist"
     */
    public function setTagCoverage(array $tags, $strategy = null)
    {
        $this->tagCoverageList = array_map('strtolower', $tags);
        $this->tagCoverageStrategy = $strategy ?: 'whitelist';
    }

    /**
     * @param  string $tag
     * @return bool
     */
    protected function isTagCovered($tag)
    {
        $isFound = in_array(strtolower($tag), $this->tagCoverageList);
        $isWhitelist = 'whitelist' == $this->tagCoverageStrategy;

        return ($isFound && $isWhitelist) || (!$isFound && !$isWhitelist);
    }
}
