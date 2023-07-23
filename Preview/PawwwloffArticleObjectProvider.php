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

namespace Pawwwloff\Bundle\SuluArticleBundle\Preview;

use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Admin\PawwwloffArticleAdmin;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;

class PawwwloffArticleObjectProvider implements PreviewObjectProviderInterface
{
    public function __construct(private readonly PawwwloffArticleRepository $articleRepository)
    {
    }

    public function getObject($id, $locale): ?PawwwloffArticle
    {
        return $this->articleRepository->findById((int) $id);
    }

    public function getId($object): string
    {
        return $object->getId();
    }

    public function setValues($object, $locale, array $data): void
    {
        // TODO: Implement setValues() method.
    }

    public function setContext($object, $locale, array $context): PawwwloffArticle
    {
        if (\array_key_exists('template', $context)) {
            $object->setStructureType($context['template']);
        }

        return $object;
    }

    public function serialize($object): string
    {
        return \serialize($object);
    }

    public function deserialize($serializedObject, $objectClass): PawwwloffArticle
    {
        return \unserialize($serializedObject);
    }

    public function getSecurityContext($id, $locale): ?string
    {
        return PawwwloffArticleAdmin::SECURITY_CONTEXT;
    }
}
