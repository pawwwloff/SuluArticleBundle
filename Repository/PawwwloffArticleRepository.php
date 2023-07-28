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

namespace Pawwwloff\Bundle\SuluArticleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\MediaBundle\Entity\Collection;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

/**
 * Class PawwwloffArticleRepository.
 */
class PawwwloffArticleRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait {
        findByFilters as parentFindByFilters;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(PawwwloffArticle $article): void
    {
        $this->getEntityManager()->persist($article);
        $this->getEntityManager()->flush();
    }

    public function createQueryBuilder($alias, $indexBy = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select($alias)
            ->from('PawwwloffArticleBundle:PawwwloffArticle', $alias, $indexBy)
            ->where("{$alias}.enabled = 1")
            ->andWhere("{$alias}.publishedAt <= CURRENT_DATE()")
            ->orderBy($alias.'.publishedAt', 'DESC');
        return $qb;
    }

    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = [])
    {
        if(!isset($filters['tagOperator'])) {
            $filters['tagOperator'] = 'OR';
        }
        $filters['sortBy'] = 'id';

        return $this->parentFindByFilters(
            $filters,
            $page,
            $pageSize,
            $limit,
            $locale,
            $options,
            null,
            Collection::class,
            'collection'
        );
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale)
    {
        $queryBuilder->addSelect('tags')
            ->addSelect('categories')
            ->addSelect('route')
            ->leftJoin($alias . '.tags', 'tags')
            ->leftJoin($alias . '.categories', 'categories')
            ->leftJoin($alias . '.route', 'route');
    }

    public function findById(int $id): ?PawwwloffArticle
    {
        $article = $this->find($id);
        if (!$article) {
            return null;
        }

        return $article;
    }
}
