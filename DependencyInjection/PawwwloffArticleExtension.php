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

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Pawwwloff\Bundle\SuluArticleBundle\Admin\PawwwloffArticleAdmin;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PawwwloffArticleExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sulu_search')) {
            $container->prependExtensionConfig(
                'sulu_search',
                [
                    'indexes' => [
                        'pawwwloff_article' => [
                            'name' => 'PawwwloffArticle',
                            'icon' => 'su-pen',
                            'view' => [
                                'name' => PawwwloffArticleAdmin::BUNDLE_EDIT_FORM_VIEW,
                                'result_to_view' => [
                                    'id' => 'id',
                                    'locale' => 'locale',
                                ],
                            ],
                            'security_context' => PawwwloffArticleAdmin::SECURITY_CONTEXT,
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_route')) {
            $container->prependExtensionConfig(
                'sulu_route',
                [
                    'mappings' => [
                        PawwwloffArticle::class => [
                            'generator' => 'schema',
                            'options' => ['route_schema' => '/pawwwloff_article/{object.getId()}'],
                            'resource_key' => PawwwloffArticle::RESOURCE_KEY,
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'lists' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/lists',
                        ],
                    ],
                    'forms' => [
                        'directories' => [
                            __DIR__ . '/../Resources/config/forms',
                        ],
                    ],
                    'resources' => [
                        'pawwwloff_article' => [
                            'routes' => [
                                'list' => 'app.get_particle',
                                'detail' => 'app.get_particle',
                            ],
                        ],
                    ],
                    'field_type_options' => [
                        'selection' => [
                            'pawwwloff_article_selection' => [
                                'default_type' => 'list_overlay',
                                'resource_key' => PawwwloffArticle::RESOURCE_KEY,
                                'view' => [
                                    'name' => 'app.article_edit_form',
                                    'result_to_view' => [
                                        'id' => 'id',
                                    ],
                                ],
                                'types' => [
                                    'auto_complete' => [
                                        'display_property' => 'title',
                                        'search_properties' => ['title'],
                                    ],
                                    'list_overlay' => [
                                        'adapter' => 'table',
                                        'list_key' => 'article',
                                        'display_properties' => ['title'],
                                        'label' => 'sulu_pawwwloff_article.article_select',
                                        'icon' => 'su-pen',
                                        'overlay_title' => 'sulu_pawwwloff_article.single_pawwwloff_article_selection_overlay_title',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }

        $container->prependExtensionConfig(
            'sulu_pawwwloff_article',
            ['templates' => ['view' => 'pawwwloff/article/index.html.twig']]
        );

//        $container->loadFromExtension('framework', [
//            'default_locale' => 'en',
//            'translator' => ['paths' => [__DIR__ . '/../Resources/config/translations/']],
//            // ...
//        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('controller.xml');

        $this->configurePersistence($config['objects'], $container);
    }
}
