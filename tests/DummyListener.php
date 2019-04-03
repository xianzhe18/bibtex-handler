<?php

namespace Xianzhe18\BibTexParser\Test;

use Xianzhe18\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public $calls = [];

    public function bibTexUnitFound($text, $type, array $context)
    {
        $this->calls[] = [
            $text,
            $type,
            $context,
        ];
    }
}
