<?php

namespace Xianzhe18\BibTexParser\Processor;

use Pandoc\Pandoc;
use Pandoc\PandocException;
use Xianzhe18\BibTexParser\Exception\ProcessorException;

/**
 * Translates LaTeX texts to unicode.
 */
class LatexToUnicodeProcessor
{
    use TagCoverageTrait;

    /** @var Pandoc|null */
    private $pandoc;

    /**
     * @param array $entry
     *
     * @return array
     */
    public function __invoke(array $entry)
    {
        $covered = $this->getCoveredTags(array_keys($entry));
        foreach ($covered as $tag) {
            // Translate string
            if (\is_string($entry[$tag])) {
                $entry[$tag] = $this->decode($entry[$tag]);
                continue;
            }

            // Translate array
            if (\is_array($entry[$tag])) {
                array_walk_recursive($entry[$tag], function (&$text) {
                    if (\is_string($text)) {
                        $text = $this->decode($text);
                    }
                });
            }
        }

        return $entry;
    }

    /**
     * @param mixed $text
     *
     * @return string
     */
    private function decode($text)
    {
        try {
            if (!$this->pandoc) {
                $this->pandoc = new Pandoc();
            }

            return $this->pandoc->runWith($text, [
                'from' => 'latex',
                'to' => 'plain',
            ]);
        } catch (PandocException $exception) {
            throw new ProcessorException(
                sprintf(
                    'Error while processing LaTeX to Unicode: %s',
                    $exception->getMessage()
                ),
                0,
                $exception
            );
        }
    }
}
