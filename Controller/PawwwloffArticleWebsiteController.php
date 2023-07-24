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

namespace Pawwwloff\Bundle\SuluArticleBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sulu\Bundle\PreviewBundle\Preview\Preview;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

/**
 * Class PawwwloffArticleWebsiteController.
 */
class PawwwloffArticleWebsiteController extends AbstractController
{
    public function indexAction(PawwwloffArticle $article, $attributes = [], $preview = false, $partial = false): Response
    {
        if (!$article) {
            throw new NotFoundHttpException();
        }

        if ($partial) {
            $content = $this->renderBlock(
                'pawwwloff/article/index.html.twig',
                'content',
                ['article' => $article]
            );
        } elseif ($preview) {
            $content = $this->renderPreview(
                'pawwwloff/article/index.html.twig',
                ['article' => $article]
            );
        } else {
            $content = $this->renderView(
                'pawwwloff/article/index.html.twig',
                ['article' => $article]
            );
        }

        return new Response($content);
    }

    protected function renderPreview(string $view, array $parameters = []): string
    {
        $parameters['previewParentTemplate'] = $view;
        $parameters['previewContentReplacer'] = Preview::CONTENT_REPLACER;

        return $this->renderView('@SuluWebsite/Preview/preview.html.twig', $parameters);
    }

    /**
     * Returns rendered part of template specified by block.
     */
    protected function renderBlock(mixed $template, mixed $block, mixed $attributes = [])
    {
        $twig = $this->container->get('twig');
        $attributes = $twig->mergeGlobals($attributes);

        $template = $twig->load($template);

        $level = \ob_get_level();
        \ob_start();

        try {
            $rendered = $template->renderBlock($block, $attributes);
            \ob_end_clean();

            return $rendered;
        } catch (\Exception $e) {
            while (\ob_get_level() > $level) {
                \ob_end_clean();
            }

            throw $e;
        }
    }
}
