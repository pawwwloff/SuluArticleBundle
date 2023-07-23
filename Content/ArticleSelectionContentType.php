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

namespace Pawwwloff\Bundle\SuluArticleBundle\Content;

use Sulu\Component\Content\Compat\PropertyInterface;
use Sulu\Component\Content\SimpleContentType;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;

class ArticleSelectionContentType extends SimpleContentType
{
    public function __construct(private readonly PawwwloffArticleRepository $articleRepository)
    {
        parent::__construct('pawwwloff_article_selection', []);
    }

    /**
     * @return PawwwloffArticle[]
     */
    public function getContentData(PropertyInterface $property): array
    {
        $ids = $property->getValue();

        $articles = [];
        foreach ($ids ?: [] as $id) {
            $article = $this->articleRepository->findById((int) $id);
            if ($article && $article->isEnabled()) {
                $articles[] = $article;
            }
        }

        return $articles;
    }

    public function getViewData(PropertyInterface $property)
    {
        return [
            'ids' => $property->getValue(),
        ];
    }
}
