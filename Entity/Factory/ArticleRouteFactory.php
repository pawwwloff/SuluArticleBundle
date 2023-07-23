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

use Sulu\Bundle\RouteBundle\Manager\RouteManager;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class ArticleRouteFactory implements ArticleRouteFactoryInterface
{
    /**
     * ArticleFactory constructor.
     */
    public function __construct(private readonly RouteManager $routeManager)
    {
    }

    public function generateArticleRoute(PawwwloffArticle $article): RouteInterface
    {
        return $this->routeManager->create($article);
    }

    public function updateArticleRoute(PawwwloffArticle $article, string $routePath): RouteInterface
    {
        return $this->routeManager->update($article, $routePath);
    }
}
