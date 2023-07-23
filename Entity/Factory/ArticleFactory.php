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

use Sulu\Bundle\ContactBundle\Entity\ContactRepositoryInterface;
use Sulu\Component\Persistence\RelationTrait;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class ArticleFactory extends AbstractFactory implements ArticleFactoryInterface
{
    use RelationTrait;

    /**
     * ArticleFactory constructor.
     */
    public function __construct(private readonly MediaFactoryInterface $mediaFactory, private readonly TagFactoryInterface $tagFactory, private readonly ContactRepositoryInterface $contactRepository)
    {
    }

    /**
     * @param mixed|null $state
     *
     * @throws \Exception
     */
    public function generateArticleFromRequest(PawwwloffArticle $article, array $data, string $locale = null, $state = null): PawwwloffArticle
    {
        if ($this->getProperty($data, 'title')) {
            $article->setTitle($this->getProperty($data, 'title'));
        }

        if ($this->getProperty($data, 'teaser')) {
            $article->setTeaser($this->getProperty($data, 'teaser'));
        }

        if ($this->getProperty($data, 'header')) {
            $article->setHeader($this->mediaFactory->generateMedia($data['header']));
        }

        if ($this->getProperty($data, 'publishedAt')) {
            $article->setPublishedAt(new \DateTime($this->getProperty($data, 'publishedAt')));
        }

        if ($this->getProperty($data, 'content')) {
            $article->setContent($this->getProperty($data, 'content'));
        }

        if ($this->getProperty($data, 'ext')) {
            $article->setSeo($this->getProperty($data['ext'], 'seo'));
        }

        if ($tags = $this->getProperty($data, 'tags')) {
            $this->tagFactory->processTags($article, $tags);
        }

        if (!$article->getId()) {
            $article->setCreated(new \DateTime());
        }

        if ($locale) {
            $article->setLocale($locale);
        }

        if (null !== $state) {
            $article->setEnabled($state);
        }

        if ($authored = $this->getProperty($data, 'authored')) {
            $article->setCreated(new \DateTime($authored));
        }

        if ($author = $this->getProperty($data, 'author')) {
            // @var Contact $contact
            $article->setCreator($this->contactRepository->find($author));
        }

        return $article;
    }
}
