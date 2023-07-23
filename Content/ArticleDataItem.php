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

namespace Pawwwloff\Bundle\SuluArticleBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\SmartContent\ItemInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class ArticleDataItem implements ItemInterface
{
    /**
     * ArticleDataItem constructor.
     */
    public function __construct(
        /**
         * @Serializer\Exclude
         */
        private readonly PawwwloffArticle $entity
    ) {
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle()
    {
        return $this->entity->getTitle();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage()
    {
        return $this->entity->getHeader();
    }

    /**
     * @return mixed|PawwwloffArticle
     */
    public function getResource()
    {
        return $this->entity;
    }
}
