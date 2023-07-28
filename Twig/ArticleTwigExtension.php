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

use Doctrine\ORM\EntityManagerInterface;
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
        private readonly EntityManagerInterface $em,
        private readonly PawwwloffArticleRepository $articleRepository
    ) {
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sulu_resolve_pawwwloff_article', [$this, 'resolveArticleFunction']),
            new TwigFunction('get_pawwwloff_articles_tags', [$this, 'getActiveArticlesTags']),
            new TwigFunction('get_pawwwloff_articles_categories', [$this, 'getActiveArticlesCategories']),
        ];
    }

    public function resolveArticleFunction(int $id): ?PawwwloffArticle
    {
        $article = $this->articleRepository->find($id);

        return $article ?? null;
    }

    public function getActiveArticlesTags()
    {
        $sql = <<<SQL
            SELECT t.id, t.name, count(*) as count
            FROM ta_tags t
            INNER JOIN pawwwloff_articles_tags nt ON nt.idTags = t.id
            INNER JOIN su_pawwwloff_article n ON n.id = nt.article_id
            WHERE n.enabled = true
            AND n.publishedAt <= CURRENT_DATE()
            GROUP BY t.id, t.name;
        SQL;

        $tags = $this->em->getConnection()->executeQuery($sql)->fetchAllAssociative();

        return $tags;
    }

    public function getActiveArticlesCategories()
    {
        $sql = <<<SQL
            SELECT t.idCategories as id, t.translation as name, count(*) as count
            FROM ca_category_translations t
            INNER JOIN pawwwloff_article_categories nt ON nt.idCategories = t.idCategories
            INNER JOIN su_pawwwloff_article n ON n.id = nt.article_id
            WHERE n.enabled = true
            AND n.publishedAt <= CURRENT_DATE()
            AND t.locale = 'en'
            GROUP BY t.id, t.translation;
        SQL;

        $categories = $this->em->getConnection()->executeQuery($sql)->fetchAllAssociative();

        return $categories;
    }
}
