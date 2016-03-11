<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser;

class Parser
{
    const NONE = 'none';
    const COMMENT = 'comment';
    const TYPE = 'type';
    const KEY = 'key';
    const RAW_VALUE = 'raw_value';
    const DELIMITED_VALUE = 'delimited_value';

    /**
     * @var string
     */
    private $state;

    /**
     * @var string[]
     */
    private $previousState = [];

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
     * @var ListenerInterface[]
     */
    private $listeners = [];

    /**
     * @var bool
     */
    private $isValueEscaped;

    /**
     * @var string
     */
    private $valueDelimiter;

    /**
     * @var bool
     */
    private $mayConcatValue;

    public function addListener(ListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }

    public function parse(string $file)
    {
        $handle = fopen($file, 'r');
        try {
            $this->line = 1;
            $this->column = 1;
            $this->state = self::NONE;
            $this->buffer = '';
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
            if (self::NONE != $this->state && self::COMMENT != $this->state) {
                $this->throwException("\0");
            }
        } finally {
            fclose($handle);
        }
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
            case self::KEY:
                $this->readKey($char);
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
            $this->previousState[] = self::NONE;
            $this->state = self::COMMENT;
            return;
        }

        if ('@' == $char) {
            $this->state = self::TYPE;
            return;
        }

        if (!$this->isWhitespace($char)) {
            $this->throwException($char);
        }
    }

    private function readComment(string $char)
    {
        if ("\n" == $char) {
            $this->state = array_shift($this->previousState);
        }
    }

    private function readType(string $char)
    {
        if (preg_match('/^[a-zA-Z]$/', $char)) {
            // when previous char is an whitespace it means it's expected a "{"
            // or multiples whitespaces before it
            $previous = substr($this->buffer, -1);
            if ($this->isWhitespace($previous)) {
                $this->throwException($char);
            }
            $this->buffer .= $char;
            return;
        }

        if ($this->isWhitespace($char)) {
            if (empty($this->buffer)) {
                // type reading must not start with an whitespace
                $this->throwException($char);
            }
            $this->buffer .= $char;
            return;
        }

        if ('{' == $char) {
            $type = rtrim($this->buffer);
            $this->buffer = '';
            if (empty($type)) {
                $this->throwException($char);
            }
            foreach ($this->listeners as $listener) {
                $listener->typeFound($type);
            }

            // start reading a key
            $this->state = self::KEY;
            return;
        }

        $this->throwException($char);
    }

    private function readKey(string $char)
    {
        if (preg_match('/^[a-zA-Z0-9\+:\-]$/', $char)) {
            // when previous char is an whitespace it means it's expected a "=",
            // "," or "}", or multiples whitespaces before them
            $previous = substr($this->buffer, -1);
            if ($this->isWhitespace($previous)) {
                $this->throwException($char);
            }
            $this->buffer .= $char;
            return;
        }

        if ($this->isWhitespace($char)) {
            if ('' != $this->buffer) {
                $this->buffer .= $char;
            }
            return;
        }

        if ('=' == $char || '}' == $char || ',' == $char) {
            $key = rtrim($this->buffer);
            $this->buffer = '';
            if (empty($key)) {
                $this->throwException($char);
            }
            foreach ($this->listeners as $listener) {
                $listener->keyFound($key);
            }

            if ('=' == $char) {
                $this->mayConcatValue = false;
                $this->state = self::RAW_VALUE;
            } if ('}' == $char) {
                $this->state = self::NONE;
            }
            return;
        }

        $this->throwException($char);
    }

    private function readRawValue(string $char)
    {
        if ('"' == $char || '{' == $char) {
            if (!empty($this->buffer)) {
                $this->throwException($char);
            }
            $this->isValueEscaped = false;
            $this->valueDelimiter = '"' == $char ? '"' : '}';
            $this->state = self::DELIMITED_VALUE;
            return;
        }

        if ('#' == $char) {
            if (!empty($this->buffer)) {
                foreach ($this->listeners as $listener) {
                    $listener->valueFound($this->buffer, true);
                }
                $this->buffer = '';
                $this->mayConcatValue = false;
            } elseif (!$this->mayConcatValue) {
                $this->throwException($char);
            }
            return;
        }

        if (',' == $char || '}' == $char) {
            if (!empty($this->buffer)) {
                foreach ($this->listeners as $listener) {
                    $listener->valueFound($this->buffer, true);
                }
                $this->buffer = '';
            } elseif (!$this->mayConcatValue) {
                // here, it means no value was given for the current key
                $this->throwException($char);
            }
            $this->state = ',' == $char ? self::KEY : self::NONE;
            return;
        }

        if (!$this->isWhitespace($char)) {
            $this->buffer .= $char;
        }
    }

    private function readDelimitedValue(string $char)
    {
        if ('\\' == $char) {
            $this->isValueEscaped = true;
            return;
        }

        if ($this->valueDelimiter == $char) {
            if ($this->isValueEscaped) {
                $this->isValueEscaped = false;
                $this->buffer .= $char;
                return;
            }

            foreach ($this->listeners as $listener) {
                $listener->valueFound($this->buffer, false);
            }
            $this->buffer = '';
            $this->mayConcatValue = true;
            $this->state = self::RAW_VALUE;
            return;
        }

        if ($this->isValueEscaped) {
            $this->isValueEscaped = false;
            $this->buffer .= '\\';
        }
        $this->buffer .= $char;
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

    private function isWhitespace(string $char): bool
    {
        return ' ' == $char || "\t" == $char || "\n" == $char || "\r" == $char;
    }
}
