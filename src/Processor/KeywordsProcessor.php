<?php

namespace Xianzhe18\BibTexParser\Processor;

/**
 * Splits tags contents as array.
 */
class KeywordsProcessor
{
    use TagCoverageTrait;

    public function __construct()
    {
        $this->setTagCoverage(['keywords']);
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        $covered = $this->getCoveredTags(array_keys($entry));
        foreach ($covered as $tag) {
            $entry[$tag] = preg_split('/, |; /', $entry[$tag]);
        }

        return $entry;
    }
}
