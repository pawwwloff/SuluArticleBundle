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
use Pawwwloff\Bundle\SuluArticleBundle\Api\PawwwloffArticle as ArticleApi;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Sulu\Component\Serializer\ArraySerializerInterface;
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
        private readonly ArraySerializerInterface $serializer,
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

    /**
     * @param Request $request
     * @return PawwwloffArticle[]
     */
    public function getArticlesByRequest(Request $request): array
    {
        $filters = $request->query->all();
        //$filters['excluded'] = \array_filter(\explode(',', $this->getRequestParameter($request, 'excluded')));
        if (isset($filters['categories'])) {
            $filters['categories'] = explode(',', $this->getRequestParameter($request, 'categories'));
        }
        if (isset($filters['tags'])) {
            $filters['tags'] = explode(',', $this->getRequestParameter($request, 'tags'));
        }
        if (isset($filters['types'])) {
            $filters['types'] = explode(',', $this->getRequestParameter($request, 'types'));
        }
        if (isset($filters['sortBy'])) {
            $filters['sortBy'] = $this->getRequestParameter($request, 'sortBy');
        }

        $page = null;
        if (isset($filters['page'])) {
            $page = (int) $this->getRequestParameter($request, 'page');
            unset($filters['page']);
        }
        $pageSize = 1;
        if (isset($filters['pageSize'])) {
            $pageSize = (int) $this->getRequestParameter($request, 'pageSize');
            unset($filters['pageSize']);
        }
        $limit = 20;
        if (isset($filters['limit'])) {
            $limit = (int) $this->getRequestParameter($request, 'limit');
            unset($filters['limit']);
        }
        $locale = 'en';
        if (isset($filters['locale'])) {
            $locale = $this->getRequestParameter($request, 'locale');
            unset($filters['locale']);
        }
        $filters = \array_filter($filters);

        $articles = $this->articleRepository->findByFilters($filters, $page, $pageSize, $limit, $locale);

        $response = array_map(
            function(PawwwloffArticle $article) use ($locale) {
                return $this->serializer->serialize(new ArticleApi($article, $locale));
            },
            $articles
        );

        return $response;
    }
}
