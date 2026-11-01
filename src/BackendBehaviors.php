<?php

/**
 * @brief typo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\typo;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsComments;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Content;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    public static function adminPageHTMLHead(bool $main = false): string
    {
        $settings = My::settings();
        if (!$settings->getBool('active', false)) {
            return '';
        }

        $items = [];

        if ($settings->getBool('entries_titles', false)) {
            // Entries (posts, pages)
            $items[] = [
                'url'       => App::backend()->url()->get('admin.post', separator:'&'),
                'id'        => '',
                'selectors' => [
                    '#post_title',  // Post title
                ],
            ];
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.pages', ['act' => 'page'], separator:'&'),
                'id'        => '',
                'selectors' => [
                    '#post_title',  // Page title
                ],
            ];
        }

        if ($settings->getBool('categories_titles', false)) {
            // Categories
            $items[] = [
                'url'       => App::backend()->url()->get('admin.category', separator:'&'),
                'id'        => '',
                'selectors' => [
                    '#cat_title',   // Category title
                ],
            ];
        }

        if ($settings->getBool('medias', false)) {
            // Medias
            $items[] = [
                'url'       => App::backend()->url()->get('admin.media.item', separator:'&'),
                'id'        => '',
                'selectors' => [
                    '#media_title', // Media title
                    '#media_alt',   // Media alternate text
                    '#media_desc',  // Media description
                ],
            ];
        }

        if ($settings->getBool('widgets_titles', false)) {
            // Widgets
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.widgets', separator:'&'),
                'id'        => 'sidebarsWidgets',
                'selectors' => [
                    '[name$="[title]"]',    // Widgets' title
                ],
            ];
        }

        if ($settings->getBool('simplemenu', false)) {
            // menuSimple
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.simpleMenu', separator:'&'),
                'id'        => 'menuitems',
                'selectors' => [
                    '[name="items_label[]"]',   // Menu items' label
                    '[name="items_descr[]"]',   // Menu items' description
                ],
            ];
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.simpleMenu', separator:'&'),
                'id'        => 'additem',
                'selectors' => [
                    '#item_label',  // Menu item label
                    '#item_descr',  // Menu item descripion
                ],
            ];
        }

        if ($settings->getBool('blogroll', false)) {
            // Blogroll
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.blogroll', separator:'&'),
                'id'        => 'add-link-form',
                'selectors' => [
                    '#link_title',  // Link title
                    '#link_desc',   // Link description
                    '#cat_title',   // Category title
                ],
            ];
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.blogroll', separator:'&'),
                'id'        => 'blogroll_link',
                'selectors' => [
                    '#link_title',  // Link title
                    '#link_desc',   // Link description
                ],
            ];
            $items[] = [
                'url'       => App::backend()->url()->get('admin.plugin.blogroll', separator:'&'),
                'id'        => 'blogroll_cat',
                'selectors' => [
                    '#link_desc',   // Category title
                ],
            ];
        }

        echo
        Html::jsJson('typo', [
            'items' => $items,
        ]) .
        My::jsLoad('typo.js');

        return '';
    }

    public static function adminDashboardFavorites(Favorites $favorites): string
    {
        $favorites->register('Typo', [
            'title'          => __('Typographic replacements'),
            'url'            => My::manageUrl(),
            'menu-icon'      => My::icon(),
            'dashboard-icon' => My::icon(),
            'permissions'    => App::auth()->makePermissions([
                App::auth()::PERMISSION_ADMIN,
            ]),
        ]);

        return '';
    }

    public static function adminPostsActions(ActionsPosts $actionsPosts): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->getBool('active', false) && ($settings->getBool('entries', false) || $settings->getBool('entries_titles', false))) {
                $actionsPosts->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminPostsDoReplacements(...)
                );
            }
        }

        return '';
    }

    public static function adminPagesActions(PagesBackendActions $pagesBackendActions): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->getBool('active', false) && ($settings->getBool('entries', false) || $settings->getBool('entries_titles', false))) {
                $pagesBackendActions->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminPagesDoReplacements(...)
                );
            }
        }

        return '';
    }

    /**
     * @param      ActionsPosts                 $actionsPosts     Actions
     * @param      ArrayObject<string, mixed>   $arrayObject      The post
     */
    public static function adminPostsDoReplacements(ActionsPosts $actionsPosts, ArrayObject $arrayObject): void
    {
        self::adminEntriesDoReplacements($actionsPosts, $arrayObject, 'post');
    }

    /**
     * @param      PagesBackendActions          $pagesBackendActions    Actions
     * @param      ArrayObject<string, mixed>   $arrayObject            The post
     */
    public static function adminPagesDoReplacements(PagesBackendActions $pagesBackendActions, ArrayObject $arrayObject): void
    {
        self::adminEntriesDoReplacements($pagesBackendActions, $arrayObject, 'page');
    }

    /**
     * @param      ActionsPosts|PagesBackendActions     $actionsPostsPages      Actions
     * @param      ArrayObject<string, mixed>           $arrayObject            The post
     * @param      string                               $type                   The type
     */
    public static function adminEntriesDoReplacements(ActionsPosts|PagesBackendActions $actionsPostsPages, ArrayObject $arrayObject, string $type = 'post'): void
    {
        if (!empty($arrayObject['full_content'])) {
            // Do replacements
            $posts = $actionsPostsPages->getRS();
            if ($posts->rows() !== []) {
                $settings    = My::settings();
                $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

                while ($posts->fetch()) {
                    // Apply typo features to entry
                    $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME);

                    if ($settings->getBool('entries', false)) {
                        $excerpt = $posts->strField('post_excerpt_xhtml');
                        if ($excerpt !== '') {
                            $cur->setStrField('post_excerpt_xhtml', SmartyPants::transform($excerpt, (string) $dashes_mode));
                        }

                        $content = $posts->strField('post_content_xhtml');
                        if ($content !== '') {
                            $cur->setStrField('post_content_xhtml', SmartyPants::transform($content, (string) $dashes_mode));
                        }
                    }

                    if ($settings->getBool('entries_titles', false)) {
                        $title = $posts->strField('post_title');
                        if ($title !== '') {
                            $cur->setStrField('post_title', SmartyPants::transform($title, (string) $dashes_mode));
                        }
                    }

                    $post_id = $posts->intField('post_id');
                    $cur->update('WHERE post_id = ' . $post_id);
                }

                $actionsPostsPages->redirect(true, ['upd' => 1]);
            } else {
                $actionsPostsPages->redirect();
            }
        } else {
            // Ask confirmation for replacements
            if ($type === 'page') {
                $actionsPostsPages->beginPage(
                    App::backend()->page()->breadcrumb(
                        [
                            Html::escapeHTML(App::blog()->name()) => '',
                            __('Pages')                           => App::backend()->url()->get('admin.plugin.pages'),
                            __('Typographic replacements')        => '',
                        ]
                    )
                );
            } else {
                $actionsPostsPages->beginPage(
                    App::backend()->page()->breadcrumb(
                        [
                            Html::escapeHTML(App::blog()->name()) => '',
                            __('Entries')                         => App::backend()->url()->get('admin.posts'),
                            __('Typographic replacements')        => '',
                        ]
                    )
                );
            }

            App::backend()->notices()->warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-entries-typo'))
            ->action($actionsPostsPages->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $actionsPostsPages->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    App::nonce()->formNonce(),
                    ... $actionsPostsPages->hiddenFields(),
                    (new Hidden('full_content', 'true')),
                    (new Hidden('action', 'typo')),
                    (new Hidden(['process'], ($type === 'post' ? 'Posts' : 'Plugin'))),
                    App::nonce()->formNonce(),
                ]),
            ])
            ->render();

            $actionsPostsPages->endPage();
        }
    }

    public static function adminCommentsActions(ActionsComments $actionsComments): void
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->getBool('active', false) && $settings->getBool('comments', false)) {
                $actionsComments->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminCommentsDoReplacements(...)
                );
            }
        }
    }

    /**
     * @param      ActionsComments              $actionsComments    Actions
     * @param      ArrayObject<string, mixed>   $arrayObject        The post
     */
    public static function adminCommentsDoReplacements(ActionsComments $actionsComments, ArrayObject $arrayObject): void
    {
        if (!empty($arrayObject['full_content'])) {
            // Do replacements
            $co = $actionsComments->getRS();
            if ($co->rows() !== []) {
                $settings    = My::settings();
                $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

                while ($co->fetch()) {
                    if ($co->strField('comment_content') !== '') {
                        # Apply typo features to comment
                        $cur     = App::db()->con()->openCursor(App::db()->con()->prefix() . App::blog()::COMMENT_TABLE_NAME);
                        $content = $co->strField('comment_content');

                        $cur->setStrField('comment_content', SmartyPants::transform($content, (string) $dashes_mode));

                        $comment_id = $co->intField('comment_id');
                        $cur->update('WHERE comment_id = ' . $comment_id);
                    }
                }

                $actionsComments->redirect(true, ['upd' => 1]);
            } else {
                $actionsComments->redirect();
            }
        } else {
            // Ask confirmation for replacements
            $actionsComments->beginPage(
                App::backend()->page()->breadcrumb(
                    [
                        Html::escapeHTML(App::blog()->name()) => '',
                        __('Comments')                        => App::backend()->url()->get('admin.comments'),
                        __('Typographic replacements')        => '',
                    ]
                )
            );

            App::backend()->notices()->warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-comments-typo'))
            ->action($actionsComments->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $actionsComments->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    App::nonce()->formNonce(),
                    ...$actionsComments->hiddenFields(),
                    (new Hidden('full_content', 'true')),
                    (new Hidden('action', 'typo')),
                    (new Hidden(['process'], 'Comments')),
                ]),
            ])
            ->render();

            $actionsComments->endPage();
        }
    }

    /**
     * @param      Content[]  $contents The content data
     *
     * @since 2.34 then 2.40 for new behavior V2
     */
    public static function coreContentFilter(string $type, array $contents): string
    {
        $settings = My::settings();
        if ($settings->getBool('active', false) && $settings->getBool('entries', false)) {
            $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

            $supported_syntaxes = ['html', 'xhtml'];

            foreach ($contents as $content) {
                $buffer = $content->getContent();
                if ($buffer !== '' && in_array($content->getFormat(), $supported_syntaxes, true)) {
                    $buffer = SmartyPants::transform($buffer, (string) $dashes_mode);
                    $content->setContent($buffer);
                }
            }
        }

        return '';
    }
}
