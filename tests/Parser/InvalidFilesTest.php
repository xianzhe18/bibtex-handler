<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test\Parser;

use PHPUnit\Framework\TestCase;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\ParseException;

class InvalidFilesTest extends TestCase
{
    public function testInexistentFileMustTriggerWarning()
    {
        $parser = new Parser();

        // Keeps compatibility with phpunit 5 and 6
        $warningClass = class_exists(\PHPUnit_Framework_Error_Warning::class)
            ? \PHPUnit_Framework_Error_Warning::class
            : \PHPUnit\Framework\Error\Warning::class;
        $this->expectException($warningClass);

        $parser->parseFile(__DIR__ . '/../resources/valid/does-not-exist');
    }

    /** @dataProvider invalidFileProvider */
    public function testInvalidInputMustCauseException($file, $message)
    {
        $parser = new Parser();

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage($message);
        $parser->parseFile($file);
    }

    public function invalidFileProvider()
    {
        $dir = __DIR__ . '/../resources/invalid';

        return [
            'brace missing' => [
                $dir . '/brace-missing.bib',
                "'\\0' at line 3 column 1",
            ],
            'multiple braced values' => [
                $dir . '/multiple-braced-values.bib',
                "'{' at line 2 column 33",
            ],
            'multiple quoted values' => [
                $dir . '/multiple-quoted-values.bib',
                "'\"' at line 2 column 33",
            ],
            'multiple raw values' => [
                $dir . '/multiple-raw-values.bib',
                "'b' at line 2 column 31",
            ],
            'space after @' => [
                $dir . '/space-after-at-sign.bib',
                "' ' at line 1 column 2",
            ],
            'splitted tag name' => [
                $dir . '/splitted-tag-name.bib',
                "'t' at line 2 column 14",
            ],
            'splitted type' => [
                $dir . '/splitted-type.bib',
                "'T' at line 1 column 11",
            ],
            'double concat' => [
                $dir . '/double-concat.bib',
                "'#' at line 2 column 20",
            ],
        ];
    }
}
