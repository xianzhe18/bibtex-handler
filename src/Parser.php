<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser;

class Parser
{
    const NONE = 'none';
    const COMMENT = 'comment';
    const TYPE = 'type';
    const POST_TYPE = 'post_type';
    const KEY = 'key';
    const POST_KEY = 'post_key';
    const VALUE = 'value';
    const RAW_VALUE = 'raw_value';
    const DELIMITED_VALUE = 'delimited_value';

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $stateAfterCommentIsGone;

    /**
     * @var string
     */
    private $buffer;

    /**
     * @var int
     */
    private $line;

    /**
     * @var int
     */
    private $column;

    /**
     * @var bool
     */
    private $isValueEscaped;

    /**
     * @var bool
     */
    private $mayConcatenateValue;

    /**
     * @var string
     */
    private $valueDelimiter;

    /**
     * @var int
     */
    private $braceLevel = 0;

    /**
     * @var ListenerInterface[]
     */
    private $listeners = [];

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    public function parseFile(string $file)
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

    public function parseString(string $string)
    {
        $this->reset();
        $this->parse($string);
        $this->checkFinalStatus();
    }

    private function parse(string $text)
    {
        $length = strlen($text);
        for ($position = 0; $position < $length; $position++) {
            $char = substr($text, $position, 1);
            $this->read($char);
            if ("\n" == $char) {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
        }
    }

    /**
     * It's called when parsing has been done, so it checks whether the status
     * is ok or not.
     */
    private function checkFinalStatus()
    {
        $current = $this->state;
        $previous = $this->stateAfterCommentIsGone;
        if (self::NONE != $current || (self::COMMENT == $current && self::NONE != $previous)) {
            $this->throwException("\0");
        }
    }

    private function reset()
    {
        $this->state = self::NONE;
        $this->stateAfterCommentIsGone = null;
        $this->buffer = '';
        $this->line = 1;
        $this->column = 1;
        $this->mayConcatenateValue = false;
        $this->isValueEscaped = false;
        $this->valueDelimiter = null;
        $this->braceLevel = 0;
    }

    private function read(string $char)
    {
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
            case self::KEY:
                $this->readKey($char);
                break;
            case self::POST_KEY:
                $this->readPostKey($char);
                break;
            case self::VALUE:
                $this->readValue($char);
                break;
            case self::RAW_VALUE:
                $this->readRawValue($char);
                break;
            case self::DELIMITED_VALUE:
                $this->readDelimitedValue($char);
                break;
        }
    }

    private function readNone(string $char)
    {
        if ('%' == $char) {
            $this->stateAfterCommentIsGone = self::NONE;
            $this->state = self::COMMENT;
        } elseif ('@' == $char) {
            $this->state = self::TYPE;
        } elseif (!$this->isWhitespace($char)) {
            $this->throwException($char);
        }
    }

    private function readComment(string $char)
    {
        if ("\n" == $char) {
            $this->state = $this->stateAfterCommentIsGone;
        }
    }

    private function readType(string $char)
    {
        if (preg_match('/^[a-zA-Z]$/', $char)) {
            $this->buffer .= $char;
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListeners('typeFound');

            // once $char isn't a valid character
            // it must be interpreted as POST_TYPE
            $this->state = self::POST_TYPE;
            $this->readPostType($char);
        }
    }

    private function readPostType(string $char)
    {
        if ('%' == $char) {
            $this->stateAfterCommentIsGone = self::POST_TYPE;
            $this->state = self::COMMENT;
        } elseif ('{' == $char) {
            $this->state = self::KEY;
        } elseif (!$this->isWhitespace($char)) {
            $this->throwException($char);
        }
    }

    private function readKey(string $char)
    {
        if (preg_match('/^[a-zA-Z0-9\+:\-]$/', $char)) {
            $this->buffer .= $char;
        } elseif ($this->isWhitespace($char) && empty($this->buffer)) {
            // skip
        } elseif ('%' == $char && empty($this->buffer)) {
            // we can't move to POST_KEY, because buffer buffer is empty
            // so, after comment is gone, we are still looking for a key
            $this->stateAfterCommentIsGone = self::KEY;
            $this->state = self::COMMENT;
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListeners('keyFound');

            // once $char isn't a valid character
            // it must be interpreted as POST_TYPE
            $this->state = self::POST_KEY;
            $this->readPostKey($char);
        }
    }

    private function readPostKey(string $char)
    {
        if ('%' == $char) {
            $this->stateAfterCommentIsGone = self::POST_KEY;
            $this->state = self::COMMENT;
        } elseif ('=' == $char) {
            $this->state = self::VALUE;
        } elseif ('}' == $char) {
            $this->state = self::NONE;
        } elseif (',' == $char) {
            $this->state = self::KEY;
        } elseif (!$this->isWhitespace($char)) {
            $this->throwException($char);
        }
    }

    private function readValue(string $char)
    {
        if (preg_match('/^[a-zA-Z0-9]$/', $char)) {
            // when $mayConcatenateValue is true it means there is another
            // value defined before it, so a concatenator char is expected (or
            // a comment as well)
            if ($this->mayConcatenateValue) {
                $this->throwException($char);
            }
            $this->state = self::RAW_VALUE;
            $this->readRawValue($char);
        } elseif ('%' == $char) {
            $this->stateAfterCommentIsGone = self::VALUE;
            $this->state = self::COMMENT;
        } elseif ('"' == $char || '{' == $char) {
            // when $mayConcatenateValue is true it means there is another
            // value defined before it, so a concatenator char is expected (or
            // a comment as well)
            if ($this->mayConcatenateValue) {
                $this->throwException($char);
            }
            $this->isValueEscaped = false;
            $this->valueDelimiter = '"' == $char ? '"' : '}';
            $this->state = self::DELIMITED_VALUE;
        } elseif ('#' == $char || ',' == $char || '}' == $char) {
            if (!$this->mayConcatenateValue) {
                // it expects some value
                $this->throwException($char);
            }
            $this->mayConcatenateValue = false;
            if (',' == $char) {
                $this->state = self::KEY;
            } elseif ('}' == $char) {
                $this->state = self::NONE;
            }
        }
    }

    private function readRawValue(string $char)
    {
        if (preg_match('/^[a-zA-Z0-9]$/', $char)) {
            $this->buffer .= $char;
        } elseif ('%' == $char) {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListeners('valueFound');

            $this->mayConcatenateValue = true;
            $this->stateAfterCommentIsGone = self::VALUE;
            $this->state = self::COMMENT;
        } else {
            $this->throwExceptionIfBufferIsEmpty($char);
            $this->triggerListeners('valueFound');

            // once $char isn't a valid character
            // it must be interpreted as VALUE
            $this->mayConcatenateValue = true;
            $this->state = self::VALUE;
            $this->readValue($char);
        }
    }

    private function readDelimitedValue(string $char)
    {
        if ($this->isValueEscaped) {
            $this->isValueEscaped = false;
            if ($this->valueDelimiter != $char && '\\' != $char && '%' != $char) {
                $this->buffer .= '\\';
            }
            $this->buffer .= $char;
        } elseif ('}' == $this->valueDelimiter && '{' == $char) {
            $this->braceLevel++;
            $this->buffer .= $char;
        } elseif ($this->valueDelimiter == $char) {
            if (0 == $this->braceLevel) {
                $this->triggerListeners('valueFound');
                $this->mayConcatenateValue = true;
                $this->state = self::VALUE;
            } else {
                $this->braceLevel--;
                $this->buffer .= $char;
            }
        } elseif ('\\' == $char) {
            $this->isValueEscaped = true;
        } elseif ('%' == $char) {
            $this->stateAfterCommentIsGone = self::DELIMITED_VALUE;
            $this->state = self::COMMENT;
        } else {
            $this->buffer .= $char;
        }
    }

    private function throwExceptionIfBufferIsEmpty(string $char)
    {
        if (empty($this->buffer)) {
            $this->throwException($char);
        }
    }

    private function throwException(string $char)
    {
        // avoid var_export() weird treatment for \0
        $char = "\0" == $char ? "'\\0'" : var_export($char, true);

        throw new ParseException(sprintf(
            "Unexpected character %s at line %d column %d",
            $char,
            $this->line,
            $this->column
        ));
    }

    private function triggerListeners(string $method)
    {
        $context = ['state' => $this->state];
        foreach ($this->listeners as $listener) {
            $listener->$method($this->buffer, $context);
        }
        $this->buffer = '';
    }

    private function isWhitespace(string $char): bool
    {
        return ' ' == $char || "\t" == $char || "\n" == $char || "\r" == $char;
    }
}
