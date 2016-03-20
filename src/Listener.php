<?php declare (strict_types = 1);

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

    public function export(): array
    {
        return $this->entries;
    }

    public function bibTexUnitFound(string $text, array $context)
    {
        switch ($context['state']) {
            case Parser::TYPE:
                $this->entries[] = ['type' => $text];
                break;

            case PARSER::KEY:
                // save key into last entry
                end($this->entries);
                $latest = key($this->entries);
                $this->entries[$latest][$text] = null;
                break;

            case PARSER::RAW_VALUE:
                $text = $this->processRawValue($text);
                // break;

            case PARSER::BRACED_VALUE:
            case PARSER::QUOTED_VALUE:
                if (null !== $text) {
                    // save value into last key
                    end($this->entries);
                    $latest = key($this->entries);
                    end($this->entries[$latest]);
                    $key = key($this->entries[$latest]);
                    $this->entries[$latest][$key] .= $text;
                }
        }
    }

    private function processRawValue(string $value)
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
