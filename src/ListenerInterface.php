<?php declare (strict_types = 1);

namespace RenanBr\BibTexParser;

interface ListenerInterface
{
    public function typeFound(string $type, array $context);
    public function keyFound(string $key, array $context);
    public function valueFound(string $value, array $context);
}
