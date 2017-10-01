<?php

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser\Processor;

/**
 * @deprecated since 0.6.0, to be removed removed in 1.0. Use NamesProcessor instead.
 */
class AuthorProcessor extends NamesProcessor
{
    public function __construct()
    {
        @trigger_error('RenanBr\BibTexParser\Processor\AuthorProcessor is deprecated since version 0.6 and will be removed in 1.0. Use RenanBr\BibTexParser\Processor\NamesProcessor instead.', \E_USER_DEPRECATED);
        $this->setTagCoverage(['author']);
    }
}
