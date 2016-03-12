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
     * @var ListenerInterface[]
     */
    private $listeners = [];

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    public function parse(string $file)
    {
        $handle = fopen($file, 'r');
        try {
            $this->reset();
            while (!feof($handle)) {
                $buffer = fread($handle, 128);
                for ($key = 0, $length = strlen($buffer); $key < $length; $key++) {
                    $char = $buffer[$key];
                    $this->read($char);
                    if ("\n" == $char) {
                        $this->line++;
                        $this->column = 1;
                    } else {
                        $this->column++;
                    }
                }
            }
            if (self::NONE != $this->state &&
                (self::COMMENT == $this->state && self::NONE != $this->stateAfterCommentIsGone)) {
                $this->throwException("\0");
            }
        } finally {
            fclose($handle);
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
            $this->state = self::RAW_VALUE;
            $this->readRawValue($char);
        } elseif ('%' == $char) {
            $this->stateAfterCommentIsGone = self::VALUE;
            $this->state = self::COMMENT;
        } elseif ('"' == $char || '{' == $char) {
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
        if ('\\' == $char) {
            $this->isValueEscaped = true;
        } elseif ($this->valueDelimiter == $char) {
            if ($this->isValueEscaped) {
                $this->isValueEscaped = false;
                $this->buffer .= $char;
            } else {
                $this->triggerListeners('valueFound');
                $this->mayConcatenateValue = true;
                $this->state = self::VALUE;
            }
        } elseif ('%' == $char) {
            if ($this->isValueEscaped) {
                $this->isValueEscaped = false;
                $this->buffer .= $char;
            } else {
                $this->stateAfterCommentIsGone = self::DELIMITED_VALUE;
                $this->state = self::COMMENT;
            }
        } elseif ($this->isValueEscaped) {
            $this->isValueEscaped = false;
            $this->buffer .= '\\' . $char;
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
        throw new \RuntimeException(sprintf(
            "Unexpected character %s at line %d column %d",
            var_export($char, true),
            $this->line,
            $this->column
        ));
    }

    private function triggerListeners(string $method)
    {
        foreach ($this->listeners as $listener) {
            $listener->$method($this->buffer, $this->state);
        }
        $this->buffer = '';
    }

    private function isWhitespace(string $char): bool
    {
        return ' ' == $char || "\t" == $char || "\n" == $char || "\r" == $char;
    }
}
