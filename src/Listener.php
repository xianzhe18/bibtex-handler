<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser;

class Listener implements ListenerInterface
{
    /** @var array */
    private $entries = [];

    /**
     * Current tag name.
     *
     * Indicates where to save values.
     *
     * @var string
     */
    private $currentTagName;

    /** @var int|null */
    private $tagNameCase = null;

    /** @var array */
    private $tagContentProcessors = [];

    /** @var bool */
    private $processed = false;

    /**
     * @return array All entries found during a parsing process.
     */
    public function export()
    {
        if (!$this->processed) {
            foreach ($this->entries as &$entry) {
                $this->processCitationTagName($entry);
                $this->processTagNameCase($entry);
                $this->processTagContent($entry);
            }
            $this->processed = true;
        }

        return $this->entries;
    }

    /**
     * @param int|null $case CASE_LOWER, CASE_UPPER or null (no traitement)
     */
    public function setTagNameCase($case)
    {
        $this->tagNameCase = $case;
    }

    /**
     * @param  $processor Function or array of functions to be applied to every member
     *                    of an BibTeX entry. Uses array_walk() internally.
     *                    The suggested signature for each function argument is:
     *                        function (string &$tagContent, string $tag);
     *                    Note that functions will be applied in the same order
     *                    in which they were added.
     * @throws \InvalidArgumentException
     */
    public function addTagContentProcessor($processor)
    {
        // if $processor is a callable array, it will be processed here
        // (see http://php.net/manual/en/language.types.callable.php#example-75)
        if (is_callable($processor)) {
            $this->tagContentProcessors[] = $processor;

            return;
        }

        // if control reaches here: it might be a non-callable array
        if (is_array($processor)) {
            // check if each value is callable
            foreach ($processor as $testing) {
                if (!is_callable($testing)) {
                    throw new \InvalidArgumentException(
                        'The argument for addTagContentProcessor should be either callable or an array of callables.'
                    );
                }
            }
            $this->tagContentProcessors = array_merge($this->tagContentProcessors, $processor);

            return;
        }

        // if control reaches this point, raise exception
        throw new \InvalidArgumentException(
            'The argument for addTagContentProcessor should be either callable or an array of callables.'
        );
    }

    public function bibTexUnitFound($text, array $context)
    {
        switch ($context['state']) {
            case Parser::TYPE:
                $this->entries[] = ['type' => $text];
                break;

            case PARSER::TAG_NAME:
                // save tag name into last entry
                end($this->entries);
                $position = key($this->entries);
                $this->currentTagName = $text;
                $this->entries[$position][$this->currentTagName] = null;
                break;

            case PARSER::RAW_TAG_CONTENT:
                $text = $this->processRawTagContent($text);
                // break;

            case PARSER::BRACED_TAG_CONTENT:
            case PARSER::QUOTED_TAG_CONTENT:
                if (null !== $text) {
                    // append value into current tag name of last entry
                    end($this->entries);
                    $position = key($this->entries);
                    $this->entries[$position][$this->currentTagName] .= $text;
                }
                break;

            case Parser::ENTRY:
                end($this->entries);
                $position = key($this->entries);
                $this->entries[$position]['_original'] = $text;
                break;
        }
    }

    private function processRawTagContent($tagContent)
    {
        // find for an abbreviation
        foreach ($this->entries as $entry) {
            if ('string' == $entry['type'] && array_key_exists($tagContent, $entry)) {
                return $entry[$tagContent];
            }
        }

        return $tagContent;
    }

    private function processCitationTagName(array &$entry)
    {
        // the first tag name is always the "type"
        // the second tag name MAY be actually a "citation-key" value, but only if its value is null
        if (count($entry) > 1) {
            $second = array_slice($entry, 1, 1, true);
            $tagName = key($second);
            $tagContent = current($second);
            if (null === $tagContent) {
                // once the second tag name value is empty, it flips the tag name name
                // as value of "citation-key"
                $entry['citation-key'] = $tagName;
                unset($entry[$tagName]);
            }
        }
    }

    private function processTagNameCase(array &$entry)
    {
        if (null === $this->tagNameCase) {
            return;
        }
        $entry = array_change_key_case($entry, $this->tagNameCase);
    }

    private function processTagContent(array &$entry)
    {
        if (empty($this->tagContentProcessors)) {
            return;
        }
        foreach ($this->tagContentProcessors as $processor) {
            array_walk($entry, $processor);
        }
    }
}
