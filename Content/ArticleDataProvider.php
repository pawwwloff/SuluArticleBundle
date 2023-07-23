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

use JMS\Serializer\Context;
use JMS\Serializer\SerializationContext;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;

class ArticleDataProvider extends BaseDataProvider
{
    public function getConfiguration()
    {
        if (null === $this->configuration) {
            $this->configuration = self::createConfigurationBuilder()
                ->enableLimit()
                ->enablePagination()
                ->enableSorting(
                    [
                        ['column' => 'title', 'title' => 'sulu_admin.title'],
                    ]
                )
                ->getConfiguration();
        }

        return parent::getConfiguration();
    }

    protected function decorateDataItems(array $data)
    {
        return \array_map(
            fn ($item) => new ArticleDataItem($item),
            $data
        );
    }

    protected function getSerializationContext(): Context|SerializationContext
    {
        return parent::getSerializationContext()->setGroups(['fullPawwwloffArticle']);
    }
}
