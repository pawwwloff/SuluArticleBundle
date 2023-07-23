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

namespace Pawwwloff\Bundle\SuluArticleBundle\Entity;

interface PawwwloffArticleInterface
{
    public function getId(): ?int;

    public function isEnabled(): bool;

    public function getTitle(): ?string;

    public function getContent();

    public function getCreated(): ?\DateTime;

    public function getCreator();
}
