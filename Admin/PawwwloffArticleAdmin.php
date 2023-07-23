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

namespace Pawwwloff\Bundle\SuluArticleBundle\Admin;

use Sulu\Bundle\ActivityBundle\Infrastructure\Sulu\Admin\View\ActivityViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\TogglerToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Pawwwloff\Bundle\SuluArticleBundle\Entity\PawwwloffArticle;

class PawwwloffArticleAdmin extends Admin
{
    final public const SECURITY_CONTEXT = 'sulu.pawwwloff.article';

    final public const BUNDLE_LIST_KEY = 'pawwwloff_article';

    final public const BUNDLE_FORM_KEY_ADD = 'pawwwloff_article_details_add';

    final public const BUNDLE_FORM_KEY_EDIT = 'pawwwloff_article_details_edit';

    final public const BUNDLE_LIST_VIEW = 'app.pawwwloff_article_list';

    final public const BUNDLE_ADD_FORM_VIEW = 'app.pawwwloff_article_add_form';

    final public const BUNDLE_EDIT_FORM_VIEW = 'app.pawwwloff_article_edit_form';

    final public const BUNDLE_FORM_KEY_SETTINGS = 'pawwwloff_article_settings';

    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly WebspaceManagerInterface $webspaceManager,
        private readonly SecurityCheckerInterface $securityChecker,
        private readonly ActivityViewBuilderFactoryInterface $activityViewBuilderFactory
    ) {
    }

    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if ($this->securityChecker->hasPermission(static::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            $module = new NavigationItem('sulu.pawwwloff.article');
            $module->setPosition(20);
            $module->setIcon('su-publish');
            $module->setView(static::BUNDLE_LIST_VIEW);

            $navigationItemCollection->add($module);
        }
    }

    public function configureViews(ViewCollection $viewCollection): void
    {
        $locales = $this->webspaceManager->getAllLocales();

        // Configure news List View
        $listToolbarActions = [new ToolbarAction('sulu_admin.add'), new ToolbarAction('sulu_admin.delete')];
        $listView = $this->viewBuilderFactory->createListViewBuilder(self::BUNDLE_LIST_VIEW, '/pawwwloff_article/:locale')
            ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
            ->setListKey(self::BUNDLE_LIST_KEY)
            ->setTitle('sulu.pawwwloff.article')
            ->addListAdapters(['table'])
            ->addLocales($locales)
            ->setDefaultLocale($locales[0])
            ->setAddView(static::BUNDLE_ADD_FORM_VIEW)
            ->setEditView(static::BUNDLE_EDIT_FORM_VIEW)
            ->addToolbarActions($listToolbarActions);
        $viewCollection->add($listView);

        $addFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(self::BUNDLE_ADD_FORM_VIEW, '/pawwwloff_article/:locale/add')
            ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
            ->setBackView(static::BUNDLE_LIST_VIEW)
            ->addLocales($locales);
        $viewCollection->add($addFormView);

        $addDetailsFormView = $this->viewBuilderFactory->createFormViewBuilder(self::BUNDLE_ADD_FORM_VIEW . '.details', '/details')
            ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
            ->setFormKey(self::BUNDLE_FORM_KEY_ADD)
            ->setTabTitle('sulu_admin.details')
            ->setEditView(static::BUNDLE_EDIT_FORM_VIEW)
            ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
            ->setParent(static::BUNDLE_ADD_FORM_VIEW);
        $viewCollection->add($addDetailsFormView);

        // Configure news Edit View
        $editFormView = $this->viewBuilderFactory->createResourceTabViewBuilder(static::BUNDLE_EDIT_FORM_VIEW, '/pawwwloff_article/:locale/:id')
            ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
            ->setBackView(static::BUNDLE_LIST_VIEW)
            ->setTitleProperty('title')
            ->addLocales($locales);
        $viewCollection->add($editFormView);

        $formToolbarActions = [
            new ToolbarAction('sulu_admin.save'),
            new ToolbarAction('sulu_admin.delete'),
            new TogglerToolbarAction(
                'sulu.pawwwloff.article.enable_article',
                'enabled',
                'enable',
                'disable'
            ),
        ];

        $viewCollection->add(
            $this->viewBuilderFactory->createPreviewFormViewBuilder(static::BUNDLE_EDIT_FORM_VIEW . '.details', '/details')
                ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
                ->setFormKey(self::BUNDLE_FORM_KEY_EDIT)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::BUNDLE_EDIT_FORM_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory->createPreviewFormViewBuilder(static::BUNDLE_EDIT_FORM_VIEW . '.details_settings', '/details-settings')
                ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
                ->setFormKey(self::BUNDLE_FORM_KEY_SETTINGS)
                ->setTabTitle('sulu_admin.settings')
                ->addToolbarActions($formToolbarActions)
                ->setParent(static::BUNDLE_EDIT_FORM_VIEW)
        );
        if ($this->activityViewBuilderFactory->hasActivityListPermission()) {
            $viewCollection->add(
                $this->activityViewBuilderFactory
                    ->createActivityListViewBuilder(
                        static::BUNDLE_EDIT_FORM_VIEW . '.activity',
                        '/activity',
                        PawwwloffArticle::RESOURCE_KEY
                    )
                    ->setParent(static::BUNDLE_EDIT_FORM_VIEW)
            );
        }

        /** @var PreviewFormViewBuilderInterface $test */
        $test = $this->viewBuilderFactory->createPreviewFormViewBuilder(static::BUNDLE_EDIT_FORM_VIEW . '.details_seo', '/seo');
        $viewCollection->add(
            $test->disablePreviewWebspaceChooser()
                ->setResourceKey(PawwwloffArticle::RESOURCE_KEY)
                ->setFormKey('article_seo')
                ->setTabTitle('sulu_page.seo')
                ->addToolbarActions($formToolbarActions)
                ->setTitleVisible(true)
                ->setTabOrder(2048)
                ->setParent(static::BUNDLE_EDIT_FORM_VIEW)
        );
    }

    public function getSecurityContexts()
    {
        return [
            'Sulu' => [
                'PawwwloffArticle' => [
                    static::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    public function getConfigKey(): ?string
    {
        return 'sulu_pawwwloff_articlle';
    }
}
