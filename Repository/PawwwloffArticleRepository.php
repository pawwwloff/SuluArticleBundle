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

    public function findByFilters(
        $filters,
        $page,
        $pageSize,
        $limit,
        $locale,
        $options = [],
        UserInterface $user = null,
        $permission = null
    )
    {
        if(!isset($filters['tagOperator'])) {
            $filters['tagOperator'] = 'OR';
        }
        return $this->parentFindByFilters(
            $filters,
            $page,
            $pageSize,
            $limit,
            $locale,
            $options,
            $user,
            Collection::class,
            'collection',
            $permission
        );
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

    public function getPublishedArticles(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('a')
            ->from('PawwwloffArticleBundle:PawwwloffArticle', 'a')
            ->where('a.enabled = 1')
            ->andWhere('a.publishedAt <= :created')
            ->setParameter('created', \date('Y-m-d H:i:s'))
            ->orderBy('a.publishedAt', 'DESC');

        $articles = $qb->getQuery()->getResult();

        if (!$articles) {
            return [];
        }

        return $articles;
    }

    public function findById(int $id): ?PawwwloffArticle
    {
        $article = $this->find($id);
        if (!$article) {
            return null;
        }

        return $article;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(int $id): void
    {
        $this->getEntityManager()->remove(
            $this->getEntityManager()->getReference(
                $this->getClassName(),
                $id
            )
        );
        $this->getEntityManager()->flush();
    }

    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale)
    {
        $queryBuilder->addSelect('tags')
            ->addSelect('route')
            ->leftJoin($alias . '.tags', 'tags')
            ->leftJoin($alias . '.route', 'route');
    }

    /**
     * Resolves filter and returns id array for second query.
     *
     * @param array $filters array of filters: tags, tagOperator
     * @param int $page
     * @param int $pageSize
     * @param int $limit
     * @param string $locale
     * @param array $options
     *
     * @return array
     */
    private function findByFiltersIds(
        $filters,
        $page,
        $pageSize,
        $limit,
        $locale,
        $options = [],
        ?UserInterface $user = null,
        $entityClass = null,
        $entityAlias = null,
        $permission = null
    ) {
        $parameter = [];

        $alias = 'entity';
        $queryBuilder = $this->createQueryBuilder($alias)
            ->select($alias . '.id')
            ->distinct()
            ->orderBy($alias . '.id', 'ASC');

        $tagRelation = $this->appendTagsRelation($queryBuilder, $alias);
        $categoryRelation = $this->appendCategoriesRelation($queryBuilder, $alias);

        if (isset($filters['sortBy'])) {
            $sortMethod = $filters['sortMethod'] ?? 'asc';
            $sortBy = false !== \strpos($filters['sortBy'], '.') ? $filters['sortBy'] : $alias . '.' . $filters['sortBy'];

            $this->appendSortBy($sortBy, $sortMethod, $queryBuilder, $alias, $locale);
            $queryBuilder->addSelect($sortBy);
        }

        $parameter = $this->append($queryBuilder, $alias, $locale, $options);

        if (isset($filters['dataSource'])) {
            $includeSubFolders = $this->getBoolean($filters['includeSubFolders'] ?? false);
            $parameter = \array_merge(
                $parameter,
                $this->appendDatasource($filters['dataSource'], $includeSubFolders, $queryBuilder, $alias)
            );
        }

        if (isset($filters['tags']) && !empty($filters['tags'])) {
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $tagRelation,
                    $filters['tags'],
                    \strtolower($filters['tagOperator']),
                    'adminTags'
                )
            );
        }

        if (isset($filters['types']) && !empty($filters['types'])) {
            $typeRelation = $this->appendTypeRelation($queryBuilder, $alias);
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $typeRelation,
                    $filters['types'],
                    'or',
                    'typeId'
                )
            );
        }

        if (isset($filters['categories']) && !empty($filters['categories'])) {
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $categoryRelation,
                    $filters['categories'],
                    \strtolower($filters['categoryOperator']),
                    'adminCategories'
                )
            );
        }

        if (isset($filters['targetGroupId']) && $filters['targetGroupId']) {
            $targetGroupRelation = $this->appendTargetGroupRelation($queryBuilder, $alias);
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $targetGroupRelation,
                    [$filters['targetGroupId']],
                    'and',
                    'targetGroupId'
                )
            );
        }

        if (isset($filters['websiteTags']) && !empty($filters['websiteTags'])) {
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $tagRelation,
                    $filters['websiteTags'],
                    \strtolower($filters['websiteTagsOperator']),
                    'websiteTags'
                )
            );
        }

        if (isset($filters['websiteCategories']) && !empty($filters['websiteCategories'])) {
            $parameter = \array_merge(
                $parameter,
                $this->appendRelation(
                    $queryBuilder,
                    $categoryRelation,
                    $filters['websiteCategories'],
                    \strtolower($filters['websiteCategoriesOperator']),
                    'websiteCategories'
                )
            );
        }

        if ($this->accessControlQueryEnhancer && $entityClass && $entityAlias && $permission) {
            $this->accessControlQueryEnhancer->enhance(
                $queryBuilder,
                $user,
                $permission,
                $entityClass,
                $entityAlias
            );
        }

        $query = $queryBuilder->getQuery();
        foreach ($parameter as $name => $value) {
            $query->setParameter($name, $value);
        }

        if (null !== $page && $pageSize > 0) {
            $pageOffset = ($page - 1) * $pageSize;
            $restLimit = $limit - $pageOffset;

            // if limitation is smaller than the page size then use the rest limit else use page size plus 1 to
            // determine has next page
            $maxResults = (null !== $limit && $pageSize > $restLimit ? $restLimit : $pageSize);

            if ($maxResults <= 0) {
                return [];
            }

            $query->setMaxResults($maxResults);
            $query->setFirstResult($pageOffset);
        } elseif (null !== $limit) {
            $query->setMaxResults($limit);
        }

        return \array_map(
            function($item) {
                return $item['id'];
            },
            $query->getScalarResult()
        );
    }
}
