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
    /**
     * @var array
     */
    private $entries = [];

    /**
     * Current key name.
     * Indicates where to save values.
     *
     * @var string
     */
    private $currentKey;

    /**
     * @var int|null
     */
    private $tagNameCase = null;

    /**
     * @var bool
     */
    private $processed = false;

    public function export()
    {
        if (!$this->processed) {
            foreach ($this->entries as &$entry) {
                $this->processCitationKey($entry);
                $this->processTagNameCase($entry);
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

    private function processCitationKey(array &$entry)
    {
        // the first key is always the "type"
        // the second key MAY be actually a "citation-key" value, but only if its value is null
        if (count($entry) > 1) {
            $second = array_slice($entry, 1, 1, true);
            list($key, $value) = each($second);
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
}
