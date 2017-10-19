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

/**
 * Detects and appends citation key.
 */
class CitationKeyProcessor
{
    /** @var string */
    private const TAG_NAME = 'citation-key';

    public function __invoke(array $entry): array
    {
        // Does nothing if tag already exists
        if (array_key_exists(self::TAG_NAME, $entry)) {
            return $entry;
        }

        // Makes sure tag will exist
        $entry[self::TAG_NAME] = null;

        // Skips if candidate tag doesn't exists: the first tag is always the
        // "type" tag, the second tag is the candidate, and the third tag is at
        // least the "citation-key" created in last block
        if (count($entry) < 3) {
            return $entry;
        }

        // Skips if the second tag content isn't NULL, because otherwise its tag
        // name can't become citation key
        $secondTag = array_slice($entry, 1, 1, true);
        $tagName = key($secondTag);
        $tagContent = current($secondTag);
        if (null !== $tagContent) {
            return $entry;
        }

        // Transforms the tag name into tag content
        $entry[self::TAG_NAME] = $tagName;
        unset($entry[$tagName]);

        return $entry;
    }
}
