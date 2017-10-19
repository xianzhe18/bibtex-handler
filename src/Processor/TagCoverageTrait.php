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

trait TagCoverageTrait
{
    use TagSearchTrait;

    /** @var array */
    private $tagCoverageList = [];

    /** @var string */
    private $tagCoverageStrategy = 'blacklist';

    /**
     * @param array  $tags     List of tags to be covered
     * @param string $strategy Can assume "whitelist" (default) or "blacklist"
     */
    public function setTagCoverage(array $tags, string $strategy = null): void
    {
        $this->tagCoverageList = $tags;
        $this->tagCoverageStrategy = $strategy ?: 'whitelist';
    }

    /**
     * Calculates which tags are covered.
     *
     * It return always a list of tags names, with some specificities:
     *   - When running under "whitelist" strategy, it uses the sended names to
     *     setTagCoverage() as keys, and the actual name as contents, because
     *     the search performed internally is case-insensitive. It means the
     *     array return may contain NULL as value.
     *   - When running under "blacklist" strategy, it returns a single list of
     *     actual tags' names, with numeric keys.
     */
    protected function getCoveredTags(array $entryTags): array
    {
        // Creates a map between coverage tag and its actual respective tag,
        // because tags are case-insensitive
        $matchedTags = [];
        $list = $this->tagCoverageList ?? [];
        foreach ($list as $original) {
            $matchedTags[$original] = $this->tagSearch($original, $entryTags);
        }

        // When running under a "whitelist" strategy it returns an map where the
        // key is the original configured tag name, and the content is the actual
        // tag name found
        $strategy = $this->tagCoverageStrategy ?? null;
        if ('whitelist' === $strategy) {
            return $matchedTags;
        }

        // Returns an simple tag names list when running under "blacklist" strategy
        return array_values(array_diff($entryTags, $matchedTags));
    }
}
