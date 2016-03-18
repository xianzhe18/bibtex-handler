<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public $calls = [];
    public $contexts = [];

    public function typeFound(string $text, array $context)
    {
        $this->calls[] = ['type', $text];
        $this->contexts[] = $context;
    }

    public function keyFound(string $text, array $context)
    {
        $this->calls[] = ['key', $text];
        $this->contexts[] = $context;
    }

    public function valueFound(string $text, array $context)
    {
        $this->calls[] = ['value', $text, $context['state']];
        $this->contexts[] = $context;
    }

    public function filterContexts(array $keys)
    {
        $contexts = $this->contexts;
        foreach ($contexts as $key => $context) {
            $contexts[$key] = array_filter($context, function ($key) use ($keys) {
                return in_array($key, $keys);
            }, \ARRAY_FILTER_USE_KEY);
        }
        return $contexts;
    }
}
