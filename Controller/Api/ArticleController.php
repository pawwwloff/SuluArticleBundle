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

namespace Pawwwloff\Bundle\SuluArticleBundle\Controller\Api;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\ControllerTrait;
use Sulu\Bundle\MediaBundle\Api\Media as MediaApi;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Component\Rest\AbstractRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sulu\Component\Rest\RequestParametersTrait;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
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
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArticleController extends AbstractController
{
    use RequestParametersTrait;
    use ControllerTrait;

    protected static $oneArticleSerializationGroups = [
        'fullPawwwloffArticle',
    ];
    /**
     * ParticleController constructor.
     */
    public function __construct(
        private readonly PawwwloffArticleRepository        $repository,
        private readonly ArticleService                    $articleService,
        private readonly DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
    ) {}

    public function getarticlesAction(Request $request): Response
    {
        $locale = $request->get('locale') ?: 'en ';
        $articles = $this->articleService->getArticlesByRequest($request);

        return $this->json($articles);
    }

    public function gettemplateAction(Request $request): Response
    {
        $articles = $this->articleService->getArticlesByRequest($request);

        $content = $this->renderView(
            'pawwwloff/article/list.html.twig',
            ['articles' => $articles]
        );

        return new Response($content);
    }
}
