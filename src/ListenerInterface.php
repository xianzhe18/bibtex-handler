<?php

namespace Xianzhe18\BibTexParser;

interface ListenerInterface
{
    /**
     * Called when an unit is found.
     *
     * @param string $text    The original content of the unit found.
     *                        Escape character will not be sent.
     * @param string $type    The type of unit found.
     *                        It can assume one of Parser's constant value.
     * @param array  $context contains details of the unit found
     */
    public function bibTexUnitFound($text, $type, array $context);
}
