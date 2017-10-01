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
     * Current key name.
     *
     * Indicates where to save values.
     *
     * @var string
     */
    private $currentKey;

    /** @var int|null */
    private $tagNameCase = null;

    /** @var array */
    private $tagValueProcessors = [];

    /** @var bool */
    private $processed = false;

    /**
     * @return array All entries found during a parsing process.
     */
    public function export()
    {
        if (!$this->processed) {
            foreach ($this->entries as &$entry) {
                $this->processCitationKey($entry);
                $this->processTagNameCase($entry);
                $this->processTagValue($entry);
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
     * @param callable|null $processor Function to be applied to every member of an BibTeX entry.
     *                                 It uses array_walk() internally.
     *                                 The suggested signature for the argument is:
     *                                 function (string &$value, string $tag);
     *
     * @deprecated since 0.5.0, to be removed removed in 1.0. Use addTagValueProcessor instead.
     */
    public function setTagValueProcessor(callable $processor = null)
    {
        @trigger_error('setTagValueProcessor() is deprecated since version 0.5 and will be removed in 1.0. Use addTagValueProcessor() instead.', \E_USER_DEPRECATED);
        if (is_null($processor)) {
            $this->tagValueProcessors = [];

            return;
        }
        $this->tagValueProcessors = [$processor];
    }

    /**
     * @param  $processor Function or array of functions to be applied to every member
     *                    of an BibTeX entry. Uses array_walk() internally.
     *                    The suggested signature for each function argument is:
     *                        function (string &$value, string $tag);
     *                    Note that functions will be applied in the same order
     *                    in which they were added.
     * @throws \InvalidArgumentException
     */
    public function addTagValueProcessor($processor)
    {
        // if $processor is a callable array, it will be processed here
        // (see http://php.net/manual/en/language.types.callable.php#example-75)
        if (is_callable($processor)) {
            $this->tagValueProcessors[] = $processor;

            return;
        }

        // if control reaches here: it might be a non-callable array
        if (is_array($processor)) {
            // check if each value is callable
            foreach ($processor as $testing) {
                if (!is_callable($testing)) {
                    throw new \InvalidArgumentException(
                        'The argument for addTagValueProcessor should be either callable or an array of callables.'
                    );
                }
            }
            $this->tagValueProcessors = array_merge($this->tagValueProcessors, $processor);

            return;
        }

        // if control reaches this point, raise exception
        throw new \InvalidArgumentException(
            'The argument for addTagValueProcessor should be either callable or an array of callables.'
        );
    }

    public function bibTexUnitFound($text, array $context)
    {
        switch ($context['state']) {
            case Parser::TYPE:
                $this->entries[] = ['type' => $text];
                break;

            case PARSER::KEY:
                // save key into last entry
                end($this->entries);
                $position = key($this->entries);
                $this->currentKey = $text;
                $this->entries[$position][$this->currentKey] = null;
                break;

            case PARSER::RAW_VALUE:
                $text = $this->processRawValue($text);
                // break;

            case PARSER::BRACED_VALUE:
            case PARSER::QUOTED_VALUE:
                if (null !== $text) {
                    // append value into current key of last entry
                    end($this->entries);
                    $position = key($this->entries);
                    $this->entries[$position][$this->currentKey] .= $text;
                }
                break;

            case Parser::ORIGINAL_ENTRY:
                end($this->entries);
                $position = key($this->entries);
                $this->entries[$position]['_original'] = $text;
                break;
        }
    }

    private function processRawValue($value)
    {
        // find for an abbreviation
        foreach ($this->entries as $entry) {
            if ('string' == $entry['type'] && array_key_exists($value, $entry)) {
                return $entry[$value];
            }
        }

        return $value;
    }

    private function processCitationKey(array &$entry)
    {
        // the first key is always the "type"
        // the second key MAY be actually a "citation-key" value, but only if its value is null
        if (count($entry) > 1) {
            $second = array_slice($entry, 1, 1, true);
            $key = key($second);
            $value = current($second);
            if (null === $value) {
                // once the second key value is empty, it flips the key name
                // as value of "citation-key"
                $entry['citation-key'] = $key;
                unset($entry[$key]);
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

    private function processTagValue(array &$entry)
    {
        if (empty($this->tagValueProcessors)) {
            return;
        }
        foreach ($this->tagValueProcessors as $processor) {
            array_walk($entry, $processor);
        }
    }
}
