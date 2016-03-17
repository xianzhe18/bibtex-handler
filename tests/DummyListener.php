<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public $calls = [];

    public function typeFound(string $text, array $context)
    {
        $this->calls[] = ['type', $text];
    }

    public function keyFound(string $text, array $context)
    {
        $this->calls[] = ['key', $text];
    }

    public function valueFound(string $text, array $context)
    {
        $this->calls[] = ['value', $text, $context['state']];
    }
}
