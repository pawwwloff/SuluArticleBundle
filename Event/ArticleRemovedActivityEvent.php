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

namespace Pawwwloff\Bundle\SuluArticleBundle\Event;

use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;
use Pawwwloff\Bundle\SuluArticleBundle\Admin\PawwwloffArticleAdmin;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class ArticleRemovedActivityEvent extends DomainEvent
{
    public function __construct(
        private readonly PawwwloffArticle $article,
        private readonly array            $payload
    ) {
        parent::__construct();
    }

    public function getEventType(): string
    {
        return 'removed';
    }

    public function getResourceKey(): string
    {
        return PawwwloffArticle::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string) $this->article->getId();
    }

    public function getEventPayload(): ?array
    {
        return $this->payload;
    }

    public function getResourceTitle(): ?string
    {
        return $this->article->getTitle();
    }

    public function getResourceSecurityContext(): ?string
    {
        return PawwwloffArticleAdmin::SECURITY_CONTEXT;
    }
}
