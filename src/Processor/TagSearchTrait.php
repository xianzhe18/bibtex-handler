<?php

namespace Xianzhe18\BibTexParser\Processor;

trait TagSearchTrait
{
    /**
     * Searchs for the actual name of a tag.
     *
     * The search performed is case-insensitive.
     *
     * @param string $needle
     * @param array  $haystack
     *
     * @return string|null
     */
    protected function tagSearch($needle, array $haystack)
    {
        foreach ($haystack as $actual) {
            if (0 === strcasecmp($needle, $actual)) {
                return $actual;
            }
        }

        return null;
    }
}
