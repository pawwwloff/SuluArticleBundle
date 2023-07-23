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

namespace Pawwwloff\Bundle\SuluArticleBundle\Routing;

use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;

class PawwwloffArticleRouteDefaultProvider implements RouteDefaultsProviderInterface
{
    public function __construct(private readonly PawwwloffArticleRepository $articleRepository)
    {
    }

    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => 'sulu_pawwwloff_article.controller::indexAction',
            'article' => $object ?: $this->articleRepository->findById((int) $id),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        /** @var PawwwloffArticle $article */
        $article = $this->articleRepository->findById((int) $id);
        if (!$this->supports($entityClass) || !$article instanceof PawwwloffArticle) {
            return false;
        }

        return $article->isEnabled();
    }

    public function supports($entityClass)
    {
        return PawwwloffArticle::class === $entityClass;
    }
}
