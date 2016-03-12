<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser;

interface ListenerInterface
{
    public function typeFound(string $type);
    public function keyFound(string $key);
    public function valueFound(string $value, string $state);
}
