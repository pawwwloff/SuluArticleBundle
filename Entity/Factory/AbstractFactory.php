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

abstract class AbstractFactory
{
    /**
     * Return property for key or given default value.
     *
     * @param array $data
     * @param string $key
     * @param string $default
     */
    protected function getProperty($data, $key, $default = null): mixed
    {
        if (\array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;
    }
}
