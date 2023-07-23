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
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

/**
 * Class PawwwloffArticleRepository.
 */
class PawwwloffArticleRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use \Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;
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
}
