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

namespace Pawwwloff\Bundle\SuluArticleBundle\Controller\Admin;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Admin\DoctrineListRepresentationFactory;
use Pawwwloff\Bundle\SuluArticleBundle\Api\PawwwloffArticle as ArticleApi;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;
use Pawwwloff\Bundle\SuluArticleBundle\Service\ArticleService;

class ParticleController extends AbstractRestController implements ClassResourceInterface
{
    // serialization groups for contact
    protected static $oneArticleSerializationGroups = [
        'partialMedia',
        'fullPawwwloffArticle',
    ];

    /**
     * ParticleController constructor.
     */
    public function __construct(
        ViewHandlerInterface                               $viewHandler,
        TokenStorageInterface                              $tokenStorage,
        private readonly PawwwloffArticleRepository        $repository,
        private readonly ArticleService                    $articleService,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
    ) {
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            PawwwloffArticle::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        if (($entity = $this->repository->findById($id)) === null) {
            throw new NotFoundHttpException();
        }

        $apiEntity = $this->generateApiArticleEntity($entity, $this->getLocale($request));

        $view = $this->generateViewContent($apiEntity);

        return $this->handleView($view);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function postAction(Request $request): Response
    {
        $article = $this->articleService->saveNewArticle($request->request->all(), $this->getLocale($request));

        $apiEntity = $this->generateApiArticleEntity($article, $this->getLocale($request));

        $view = $this->generateViewContent($apiEntity);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/particles/{id}")
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $article = $this->repository->findById($id);
        if (!$article instanceof PawwwloffArticle) {
            throw new NotFoundHttpException();
        }

        $article = $this->articleService->updateArticlePublish($article, $request->query->all());

        $apiEntity = $this->generateApiArticleEntity($article, $this->getLocale($request));
        $view = $this->generateViewContent($apiEntity);

        return $this->handleView($view);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function putAction(int $id, Request $request): Response
    {
        $entity = $this->repository->findById($id);
        if (!$entity instanceof PawwwloffArticle) {
            throw new NotFoundHttpException();
        }

        $updatedEntity = $this->articleService->updateArticle($request->request->all(), $entity, $this->getLocale($request));
        $apiEntity = $this->generateApiArticleEntity($updatedEntity, $this->getLocale($request));
        $view = $this->generateViewContent($apiEntity);

        return $this->handleView($view);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(int $id): Response
    {
        try {
            $this->articleService->removeArticle($id);
        } catch (\Exception) {
            throw new EntityNotFoundException(self::$entityName, $id);
        }

        return $this->handleView($this->view());
    }

    public static function getPriority(): int
    {
        return 0;
    }

    protected function generateApiArticleEntity(PawwwloffArticle $entity, string $locale): ArticleApi
    {
        return new ArticleApi($entity, $locale);
    }

    protected function generateViewContent(ArticleApi $entity): View
    {
        $view = $this->view($entity);
        $context = new Context();
        $context->setGroups(static::$oneArticleSerializationGroups);

        return $view->setContext($context);
    }
}
