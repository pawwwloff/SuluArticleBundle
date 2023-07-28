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

namespace Pawwwloff\Bundle\SuluArticleBundle\Api;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\CategoryBundle\Api\Category;
use Sulu\Component\Rest\ApiWrapper;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle as ArticleEntity;

/**
 * The PawwwloffArticle class which will be exported to the API.
 *
 * @ExclusionPolicy("all")
 */
class PawwwloffArticle extends ApiWrapper
{
    public function __construct(ArticleEntity $article, $locale)
    {
        // @var ArticleEntity entity
        $this->entity = $article;
        $this->locale = $locale;
    }

    /**
     * Get id.
     *
     * @VirtualProperty
     *
     * @SerializedName("id")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("title")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getTitle(): ?string
    {
        return $this->entity?->getTitle();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("teaser")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getTeaser(): ?string
    {
        return $this->entity->getTeaser();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("content")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getContent(): array
    {
        if (!$this->entity->getContent()) {
            return [];
        }

        return $this->entity->getContent();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("enabled")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function isEnabled(): bool
    {
        return $this->entity?->isEnabled();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("publishedAt")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->entity?->getPublishedAt();
    }

    /**
     * @VirtualProperty
     *
     * @SerializedName("route")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getRoutePath(): ?string
    {
        if ($this->entity?->getRoute()) {
            return $this->entity->getRoute()?->getPath();
        }

        return '';
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("tags")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getTags(): array
    {
        return $this->entity->getTagNameArray();
    }

    /**
     * Get the contacts avatar and return the array of different formats.
     *
     * @VirtualProperty
     *
     * @SerializedName("header")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getHeader(): array
    {
        if ($this->entity->getHeader()) {
            return [
                'id' => $this->entity->getHeader()->getId(),
            ];
        }

        return [];
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("authored")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getAuthored(): \DateTime
    {
        return $this->entity->getCreated();
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("created")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getCreated(): \DateTime
    {
        return $this->entity->getCreated();
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("changed")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getChanged(): \DateTime
    {
        return $this->entity->getChanged();
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("author")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getAuthor(): ?int
    {
        return $this->entity?->getCreator()?->getId();
    }

    /**
     * Get tags.
     *
     * @VirtualProperty
     *
     * @SerializedName("ext")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getSeo(): array
    {
        $seo = ['seo'];
        $seo['seo'] = $this->getEntity()->getSeo();

        return $seo;
    }

    /**
     * Get categories.
     *
     * @return Category[]
     *
     * @VirtualProperty
     * @SerializedName("categories")
     * @Groups({"fullPawwwloffArticle"})
     */
    public function getCategories()
    {
        return \array_map(function($category) {
            return $category->getId();
        }, $this->entity?->getCategories()->toArray());
    }
}
