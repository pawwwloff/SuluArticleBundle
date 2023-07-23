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

namespace Pawwwloff\Bundle\SuluArticleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;
use Pawwwloff\Bundle\SuluArticleBundle\Repository\PawwwloffArticleRepository;

/**
 * This is the class that validates and merges configuration from your app/config files.
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sulu_pawwwloff_article');
        $root = $treeBuilder->getRootNode();

        $root->children()
            ->arrayNode('objects')
            ->addDefaultsIfNotSet()
            ->children()
            ->arrayNode('pawwwloff_article')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('model')->defaultValue(PawwwloffArticle::class)->end()
            ->scalarNode('repository')->defaultValue(PawwwloffArticleRepository::class)->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
