<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public $calls = [];

    public function typeFound(string $text)
    {
        $this->calls[] = ['type', $text];
    }

    public function keyFound(string $text)
    {
        $this->calls[] = ['key', $text];
    }

    public function valueFound(string $text, string $status)
    {
        $this->calls[] = ['value', $text, $status];
    }
}
