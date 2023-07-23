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

namespace Pawwwloff\Bundle\SuluArticleBundle\Twig;

use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension to handle article in frontend.
 */
class ArticleTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly PawwwloffArticleRepository $articleRepository
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_resolve_pawwwloff_article', [$this, 'resolveArticleFunction']),
        ];
    }

    public function resolveArticleFunction(int $id): ?PawwwloffArticle
    {
        $article = $this->articleRepository->find($id);

        return $article ?? null;
    }

    public function resolveArticlesByFiltersFunction(array $filters, int $page, int $pageSize): ?PawwwloffArticle
    {
        $article = $this->articleRepository->findByFilters($filters, $page, $pageSize);

        return $article ?? null;
    }

    public function resolveArticlePaginationInfoFunction(int $id): ?PawwwloffArticle
    {
        $article = $this->articleRepository->find($id);

        return $article ?? null;
    }
}
