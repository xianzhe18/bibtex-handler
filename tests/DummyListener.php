<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Test;

use RenanBr\BibTexParser\ListenerInterface;

class DummyListener implements ListenerInterface
{
    public $calls = [];
    public $contexts = [];

    public function bibTexUnitFound($text, array $context)
    {
        $this->calls[] = [$context['state'], $text];
        $this->contexts[] = $context;
    }

    public function getContextsFiltered(array $keys)
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
