<?php

namespace Xianzhe18\BibTexParser\Processor;

/**
 * Change the case of all tag names.
 */
class TagNameCaseProcessor
{
    /** @var int */
    private $case;

    /**
     * @param int $case
     */
    public function __construct($case)
    {
        $this->case = $case;
    }

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        return array_change_key_case($entry, $this->case);
    }
}
