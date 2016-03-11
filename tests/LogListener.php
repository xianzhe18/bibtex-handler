<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class LogListener implements ListenerInterface
{
    public $log = [];

    public function typeFound(string $text)
    {
        $this->log[] = ['type', $text];
    }

    public function keyFound(string $text)
    {
        $this->log[] = ['key', $text];
    }

    public function valueFound(string $text, bool $isRaw)
    {
        $this->log[] = ['value', $text, $isRaw];
    }
}
