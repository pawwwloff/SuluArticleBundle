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

namespace Pawwwloff\Bundle\SuluArticleBundle\Service;

use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

interface ArticleServiceInterface
{
    public function saveNewArticle(array $data, string $locale): PawwwloffArticle;

    public function updateArticle($data, PawwwloffArticle $article, string $locale): PawwwloffArticle;
}
