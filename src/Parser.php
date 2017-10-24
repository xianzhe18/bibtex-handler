<?php declare(strict_types=1);

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser;

use RenanBr\BibTexParser\Exception\ParserException;

class Parser
{
    public const TYPE = 'type';
    public const TAG_NAME = 'tag_name';
    public const RAW_TAG_CONTENT = 'raw_tag_content';
    public const BRACED_TAG_CONTENT = 'braced_tag_content';
    public const QUOTED_TAG_CONTENT = 'quoted_tag_content';
    public const ENTRY = 'entry';

    private const NONE = 'none';
    private const COMMENT = 'comment';
    private const POST_TYPE = 'post_type';
    private const POST_TAG_NAME = 'post_tag_name';
    private const PRE_TAG_CONTENT = 'pre_tag_content';

    /** @var string */
    private $state;

    /** @var string */
    private $buffer;

    /** @var string */
    private $originalEntry;

    /** @var int */
    private $originalEntryOffset;

    /** @var int */
    private $line;

    /** @var int */
    private $column;

    /** @var int */
    private $offset;

    /** @var bool */
    private $isTagContentEscaped;

    /** @var bool */
    private $mayConcatenateTagContent;

    /** @var string */
    private $tagContentDelimiter;

    /** @var int */
    private $braceLevel = 0;

    /** @var ListenerInterface[] */
    private $listeners = [];

    public function addListener(ListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param  string          $file
     * @throws ParserException If $file given is not a valid BibTeX.
     * @throws \ErrorException If $file given is not readable.
     */
    public function parseFile(string $file): void
    {
        $handle = fopen($file, 'r');
        try {
            $this->reset();
            while (!feof($handle)) {
                $buffer = fread($handle, 128);
                $this->parse($buffer);
            }
            $this->checkFinalStatus();
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param  string          $string
     * @throws ParserException If $string given is not a valid BibTeX.
     */
    public function parseString(string $string): void
    {
        $this->reset();
        $this->parse($string);
        $this->checkFinalStatus();
    }

    private function parse(string $text): void
    {
        $length = mb_strlen($text);
        for ($position = 0; $position < $length; $position++) {
            $char = mb_substr($text, $position, 1);
            $this->read($char);
            if ("\n" === $char) {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
            $this->offset++;
        }
    }

    private function checkFinalStatus(): void
    {
        // it's called when parsing has been done
        // so it checks whether the status is ok or not
        if (self::NONE !== $this->state && self::COMMENT !== $this->state) {
            throw ParserException::unexpectedCharacter("\0", $this->line, $this->column);
        }
    }

    private function reset(): void
    {
        $this->state = self::NONE;
        $this->buffer = '';
        $this->originalEntry = '';
        $this->originalEntryOffset = null;
        $this->line = 1;
        $this->column = 1;
        $this->offset = 0;
        $this->mayConcatenateTagContent = false;
        $this->isTagContentEscaped = false;
        $this->valueDelimiter = null;
        $this->braceLevel = 0;
    }

    private function read(string $char): void
    {
        $previousState = $this->state;

        switch ($this->state) {
            case self::NONE:
                $this->readNone($char);
                break;
            case self::COMMENT:
                $this->readComment($char);
                break;
            case self::TYPE:
                $this->readType($char);
                break;
            case self::POST_TYPE:
                $this->readPostType($char);
                break;
            case self::TAG_NAME:
                $this->readTagName($char);
                break;
            case self::POST_TAG_NAME:
                $this->readPostTagName($char);
                break;
            case self::PRE_TAG_CONTENT:
                $this->readPreTagContent($char);
                break;
            case self::RAW_TAG_CONTENT:
                $this->readRawTagContent($char);
                break;
            case self::QUOTED_TAG_CONTENT:
            case self::BRACED_TAG_CONTENT:
                $this->readDelimitedTagContent($char);
                break;
        }

        $this->readOriginalEntry($char, $previousState);
    }

    private function readNone(string $char): void
    {
        if ('@' === $char) {
            $this->state = self::TYPE;
        } elseif (!$this->isWhitespace($char)) {
            $this->state = self::COMMENT;
        }
    }

    private function readComment(string $char): void
    {
        if ($this->isWhitespace($char)) {
            $this->state = self::NONE;
        }
    }

    private function readType(string $char): void
    {
        if (preg_match('/^[a-zA-Z]$/', $char)) {
            $this->appendToBuffer($char);
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListenersWithCurrentBuffer();

            // once $char isn't a valid character
            // it must be interpreted as POST_TYPE
            $this->state = self::POST_TYPE;
            $this->readPostType($char);
        }
    }

    private function readPostType(string $char): void
    {
        if ('{' === $char) {
            $this->state = self::TAG_NAME;
        } elseif (!$this->isWhitespace($char)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function readTagName(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9_\+:\-]$/', $char)) {
            $this->appendToBuffer($char);
        } elseif ($this->isWhitespace($char) && empty($this->buffer)) {
            // skip
        } elseif ('}' === $char) {
            $this->state = self::NONE;
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListenersWithCurrentBuffer();

            // once $char isn't a valid character
            // it must be interpreted as POST_TYPE
            $this->state = self::POST_TAG_NAME;
            $this->readPostTagName($char);
        }
    }

    private function readPostTagName(string $char): void
    {
        if ('=' === $char) {
            $this->state = self::PRE_TAG_CONTENT;
        } elseif ('}' === $char) {
            $this->state = self::NONE;
        } elseif (',' === $char) {
            $this->state = self::TAG_NAME;
        } elseif (!$this->isWhitespace($char)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function readPreTagContent(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9]$/', $char)) {
            // when $mayConcatenateTagContent is true it means there is another
            // value defined before it, so a concatenator char is expected (or
            // a comment as well)
            if ($this->mayConcatenateTagContent) {
                throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
            }
            $this->state = self::RAW_TAG_CONTENT;
            $this->readRawTagContent($char);
        } elseif ('"' === $char) {
            // this verification is here for the same reason of the first case
            if ($this->mayConcatenateTagContent) {
                throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
            }
            $this->valueDelimiter = '"';
            $this->state = self::QUOTED_TAG_CONTENT;
        } elseif ('{' === $char) {
            // this verification is here for the same reason of the first case
            if ($this->mayConcatenateTagContent) {
                throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
            }
            $this->valueDelimiter = '}';
            $this->state = self::BRACED_TAG_CONTENT;
        } elseif ('#' === $char || ',' === $char || '}' === $char) {
            if (!$this->mayConcatenateTagContent) {
                // it expects some value
                throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
            }
            $this->mayConcatenateTagContent = false;
            if (',' === $char) {
                $this->state = self::TAG_NAME;
            } elseif ('}' === $char) {
                $this->state = self::NONE;
            }
        }
    }

    private function readRawTagContent(string $char): void
    {
        if (preg_match('/^[a-zA-Z0-9]$/', $char)) {
            $this->appendToBuffer($char);
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListenersWithCurrentBuffer();

            // once $char isn't a valid character
            // it must be interpreted as TAG_CONTENT
            $this->mayConcatenateTagContent = true;
            $this->state = self::PRE_TAG_CONTENT;
            $this->readPreTagContent($char);
        }
    }

    private function readDelimitedTagContent(string $char): void
    {
        if ($this->isTagContentEscaped) {
            $this->isTagContentEscaped = false;
            if ($this->valueDelimiter !== $char && '\\' !== $char && '%' !== $char) {
                $this->appendToBuffer('\\');
            }
            $this->appendToBuffer($char);
        } elseif ('}' === $this->valueDelimiter && '{' === $char) {
            $this->braceLevel++;
            $this->appendToBuffer($char);
        } elseif ($this->valueDelimiter === $char) {
            if (0 === $this->braceLevel) {
                $this->triggerListenersWithCurrentBuffer();
                $this->mayConcatenateTagContent = true;
                $this->state = self::PRE_TAG_CONTENT;
            } else {
                $this->braceLevel--;
                $this->appendToBuffer($char);
            }
        } elseif ('\\' === $char) {
            $this->isTagContentEscaped = true;
        } else {
            $this->appendToBuffer($char);
        }
    }

    private function readOriginalEntry(string $char, string $previousState): void
    {
        // check whether we are reading an entry character or not
        // $isEntryChar is TRUE when previous or current state indicates it
        $isEntryChar =
            ($previousState !== self::NONE && $previousState !== self::COMMENT) ||
            ($this->state !== self::NONE && $this->state !== self::COMMENT)
        ;

        if ($isEntryChar) {
            // append to the buffer
            if (empty($this->originalEntry)) {
                $this->originalEntryOffset = $this->offset;
            }
            $this->originalEntry .= $char;
        } elseif (!empty($this->originalEntry)) {
            // send original value to the listeners
            $context = [
                'offset' => $this->originalEntryOffset,
                'length' => $this->offset - $this->originalEntryOffset,
            ];
            $this->triggerListeners($this->originalEntry, self::ENTRY, $context);
            $this->originalEntryOffset = null;
            $this->originalEntry = '';
        }
    }

    private function throwExceptionIfBufferIsEmpty(string $char): void
    {
        if (empty($this->buffer)) {
            throw ParserException::unexpectedCharacter($char, $this->line, $this->column);
        }
    }

    private function appendToBuffer(string $char): void
    {
        if (empty($this->buffer)) {
            $this->bufferOffset = $this->offset;
        }
        $this->buffer .= $char;
    }

    private function triggerListenersWithCurrentBuffer(): void
    {
        $context = [
            'offset' => $this->bufferOffset,
            'length' => $this->offset - $this->bufferOffset,
        ];
        $this->triggerListeners($this->buffer, $this->state, $context);
        $this->bufferOffset = null;
        $this->buffer = '';
    }

    private function triggerListeners(string $text, string $type, array $context): void
    {
        foreach ($this->listeners as $listener) {
            $listener->bibTexUnitFound($text, $type, $context);
        }
    }

    private function isWhitespace(string $char): bool
    {
        return ' ' === $char || "\t" === $char || "\n" === $char || "\r" === $char;
    }
}
