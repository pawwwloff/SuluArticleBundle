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

use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Component\Persistence\RelationTrait;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class CategoryFactory extends AbstractFactory implements CategoryFactoryInterface
{
    use RelationTrait;

    /**
     * TagFactory constructor.
     */
    public function __construct(private readonly CategoryManagerInterface $categoryManager)
    {
    }

    /**
     * @param mixed $categories
     *
     * @return bool
     */
    public function processCategories(PawwwloffArticle $article, $categories)
    {
        $entities = $article->getCategories();

        if($entities) {
            foreach ($entities as $category) {
                if (!in_array($category->getId(), $categories)) {
                    $article->removeCategory($category);
                }
            }
        }

        foreach ($categories as $category){
            $this->addCategory($article, $category);
        }
    }

    /**
     * Returns the tag manager.
     *
     * @return CategoryManagerInterface
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    /**
     * Adds a new tag to the given contact and persist it with the given object manager.
     *
     * @return bool True if there was no error, otherwise false
     */
    protected function addCategory(PawwwloffArticle $article, mixed $data): bool
    {
        $resolvedCategory = $this->getCategoryManager()->findById($data);
        $article->addCategory($resolvedCategory);

        return true;
    }
}
