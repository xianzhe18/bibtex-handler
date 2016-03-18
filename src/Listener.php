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

    public function typeFound(string $type, array $context)
    {
        $this->entries[] = ['type' => $type];
    }

    public function keyFound(string $key, array $context)
    {
        // save key into last entry
        end($this->entries);
        $position = key($this->entries);
        $this->entries[$position][$key] = null;
    }

    public function valueFound(string $value, array $context)
    {
        if ($context['state'] == Parser::RAW_VALUE) {
            $value = $this->processRawValue($value);
        }
        if (null !== $value) {
            // save value into last key
            end($this->entries);
            $position = key($this->entries);
            end($this->entries[$position]);
            $key = key($this->entries[$position]);
            $this->entries[$position][$key] .= $value;
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
