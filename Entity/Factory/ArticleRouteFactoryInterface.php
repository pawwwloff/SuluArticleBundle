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

use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

interface ArticleRouteFactoryInterface
{
    public function generateArticleRoute(PawwwloffArticle $article): RouteInterface;

    public function updateArticleRoute(PawwwloffArticle $article, string $routePath): RouteInterface;
}
