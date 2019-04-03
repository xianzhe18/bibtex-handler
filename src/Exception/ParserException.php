<?php

namespace Xianzhe18\BibTexParser\Exception;

use Exception;

class ParserException extends Exception implements ExceptionInterface
{
    /**
     * @param string $character
     * @param int    $line
     * @param int    $column
     */
    public static function unexpectedCharacter($character, $line, $column)
    {
        // Avoid var_export() weird treatment for \0
        $character = "\0" === $character ? "'\\0'" : var_export($character, true);

        return new self(sprintf(
            'Unexpected character %s at line %d column %d',
            $character,
            $line,
            $column
        ));
    }
}
