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

namespace Pawwwloff\Bundle\SuluArticleBundle\Service;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\Factory\ArticleFactory;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\Factory\ArticleRouteFactory;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Event\ArticleCreatedActivityEvent;
use Pawwwloff\Bundle\SuluArticleBundle\Event\ArticleModifiedActivityEvent;
use Pawwwloff\Bundle\SuluArticleBundle\Event\ArticleRemovedActivityEvent;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;

class ArticleService implements ArticleServiceInterface
{
    /**
     * @var object|string
     */
    private ?UserInterface $loginUser = null;

    /**
     * ArticleService constructor.
     */
    public function __construct(
        private readonly PawwwloffArticleRepository    $articleRepository,
        private readonly ArticleFactory                $articleFactory,
        private readonly ArticleRouteFactory           $routeFactory,
        TokenStorageInterface                          $tokenStorage,
        private readonly DomainEventCollectorInterface $domainEventCollector
    ) {
        if (null !== $tokenStorage->getToken()) {
            $this->loginUser = $tokenStorage->getToken()->getUser();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveNewArticle(array $data, string $locale): PawwwloffArticle
    {
        $article = null;
        try {
            $article = $this->articleFactory->generateArticleFromRequest(new PawwwloffArticle(), $data, $locale);
        } catch (\Exception) {
        }

        /** @var PawwwloffArticle $article */
        if (!$article->getCreator()) {
            $article->setCreator($this->loginUser->getContact());
        }
        $article->setchanger($this->loginUser->getContact());

        $this->articleRepository->save($article);

        $this->routeFactory->generateArticleRoute($article);

        $this->domainEventCollector->collect(new ArticleCreatedActivityEvent($article, ['name' => $article->getTitle()]));
        $this->articleRepository->save($article);

        return $article;
    }

    /**
     * @param mixed $data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateArticle($data, PawwwloffArticle $article, string $locale): PawwwloffArticle
    {
        try {
            $article = $this->articleFactory->generateArticleFromRequest($article, $data, $locale);
        } catch (\Exception) {
        }

        $article->setchanger($this->loginUser->getContact());

        if ($article->getRoute()->getPath() !== $data['route']) {
            $route = $this->routeFactory->updateArticleRoute($article, $data['route']);
            $article->setRoute($route);
        }

        $this->domainEventCollector->collect(new ArticleModifiedActivityEvent($article, ['name' => $article->getTitle()]));
        $this->articleRepository->save($article);

        return $article;
    }

    public function updateArticlePublish(PawwwloffArticle $article, array $data): PawwwloffArticle
    {
        switch ($data['action']) {
            case 'enable':
                $article = $this->articleFactory->generateArticleFromRequest($article, [], null, true);

                break;

            case 'disable':
                $article = $this->articleFactory->generateArticleFromRequest($article, [], null, false);

                break;
        }
        $this->domainEventCollector->collect(new ArticleModifiedActivityEvent($article, ['name' => $article->getTitle()]));
        $this->articleRepository->save($article);

        return $article;
    }

    public function removeArticle(int $id): void
    {
        $article = $this->articleRepository->findById($id);
        if (!$article instanceof PawwwloffArticle) {
            throw new \Exception($id);
        }

        $this->domainEventCollector->collect(new ArticleRemovedActivityEvent($article, ['name' => $article->getTitle()]));
        $this->articleRepository->remove($id);
    }
}
