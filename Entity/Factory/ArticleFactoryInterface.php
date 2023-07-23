<?php

declare(strict_types=1);

/*
 * This file is part of Pawwwloff/SuluArticleBundle.
 *
 * by Evgeny Pavlov.
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Pawwwloff\Bundle\SuluArticleBundle\Entity\Factory;

use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

interface ArticleFactoryInterface
{
    public function generateArticleFromRequest(PawwwloffArticle $article, array $data, string $locale): PawwwloffArticle;
}
