<?php

/**
 * @brief typo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\typo;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsComments;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
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
        if (!$settings->active) {
            return '';
        }

        $items = [];

        if ($settings->entries_title) {
            array_push(
                $items,
                // Entries (posts, pages)
                [
                    'url'       => App::backend()->url()->get('admin.post', separator:'&'),
                    'id'        => '',
                    'selectors' => [
                        '#post_title',  // Post title
                    ],
                ],
                [
                    'url'       => App::backend()->url()->get('admin.plugin.pages', ['act' => 'page'], separator:'&'),
                    'id'        => '',
                    'selectors' => [
                        '#post_title',  // Page title
                    ],
                ]
            );
        }

        if ($settings->categories_title) {
            array_push(
                $items,
                // Categories
                [
                    'url'       => App::backend()->url()->get('admin.category', separator:'&'),
                    'id'        => '',
                    'selectors' => [
                        '#cat_title',   // Category title
                    ],
                ],
            );
        }

        if ($settings->medias) {
            array_push(
                $items,
                // Medias
                [
                    'url'       => App::backend()->url()->get('admin.media.item', separator:'&'),
                    'id'        => '',
                    'selectors' => [
                        '#media_title', // Media title
                        '#media_alt',   // Media alternate text
                        '#media_desc',  // Media description
                    ],
                ],
            );
        }

        if ($settings->widgets_titles) {
            array_push(
                $items,
                // Widgets
                [
                    'url'       => App::backend()->url()->get('admin.plugin.widgets', separator:'&'),
                    'id'        => 'sidebarsWidgets',
                    'selectors' => [
                        '[name$="[title]"]',    // Widgets' title
                    ],
                ],
            );
        }

        if ($settings->simplemenu) {
            array_push(
                $items,
                // menuSimple

                [
                    'url'       => App::backend()->url()->get('admin.plugin.simpleMenu', separator:'&'),
                    'id'        => 'menuitems',
                    'selectors' => [
                        '[name="items_label[]"]',   // Menu items' label
                        '[name="items_descr[]"]',   // Menu items' description
                    ],
                ],
                [
                    'url'       => App::backend()->url()->get('admin.plugin.simpleMenu', separator:'&'),
                    'id'        => 'additem',
                    'selectors' => [
                        '#item_label',  // Menu item label
                        '#item_descr',  // Menu item descripion
                    ],
                ],
            );
        }

        if ($settings->blogroll) {
            array_push(
                $items,
                // Blogroll
                [
                    'url'       => App::backend()->url()->get('admin.plugin.blogroll', separator:'&'),
                    'id'        => 'add-link-form',
                    'selectors' => [
                        '#link_title',  // Link title
                        '#link_desc',   // Link description
                        '#cat_title',   // Category title
                    ],
                ]
            );
        }

        echo
        Html::jsJson('typo', [
            'items' => $items,
        ]) .
        My::jsLoad('typo.js');

        return '';
    }

    public static function adminDashboardFavorites(Favorites $favs): string
    {
        $favs->register('Typo', [
            'title'       => __('Typographic replacements'),
            'url'         => My::manageUrl(),
            'small-icon'  => My::icons(),
            'large-icon'  => My::icons(),
            'permissions' => App::auth()->makePermissions([
                App::auth()::PERMISSION_ADMIN,
            ]),
        ]);

        return '';
    }

    public static function adminPostsActions(ActionsPosts $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->active && ($settings->entries || $settings->entries_titles)) {
                $ap->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminPostsDoReplacements(...)
                );
            }
        }

        return '';
    }

    public static function adminPagesActions(PagesBackendActions $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->active && ($settings->entries || $settings->entries_titles)) {
                $ap->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminPagesDoReplacements(...)
                );
            }
        }

        return '';
    }

    /**
     * @param      ActionsPosts                 $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminPostsDoReplacements(ActionsPosts $ap, ArrayObject $post): void
    {
        self::adminEntriesDoReplacements($ap, $post, 'post');
    }

    /**
     * @param      PagesBackendActions          $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminPagesDoReplacements(PagesBackendActions $ap, ArrayObject $post): void
    {
        self::adminEntriesDoReplacements($ap, $post, 'page');
    }

    /**
     * @param      ActionsPosts|PagesBackendActions     $ap     Actions
     * @param      ArrayObject<string, mixed>           $post   The post
     * @param      string                               $type   The type
     */
    public static function adminEntriesDoReplacements(ActionsPosts|PagesBackendActions $ap, ArrayObject $post, string $type = 'post'): void
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                $settings    = My::settings();
                $dashes_mode = $settings->dashes_mode;
                while ($posts->fetch()) {
                    // Apply typo features to entry
                    $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME);

                    if ($settings->entries && (($posts->post_excerpt_xhtml) || ($posts->post_content_xhtml))) {
                        if ($posts->post_excerpt_xhtml) {
                            $cur->post_excerpt_xhtml = SmartyPants::transform($posts->post_excerpt_xhtml, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                        }

                        if ($posts->post_content_xhtml) {
                            $cur->post_content_xhtml = SmartyPants::transform($posts->post_content_xhtml, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                        }
                    }
                    if ($settings->entries_titles && $posts->post_title) {
                        $cur->post_title = SmartyPants::transform($posts->post_title, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                    }

                    $cur->update('WHERE post_id = ' . (int) $posts->post_id);
                }

                $ap->redirect(true, ['upd' => 1]);
            } else {
                $ap->redirect();
            }
        } else {
            // Ask confirmation for replacements
            if ($type === 'page') {
                $ap->beginPage(
                    App::backend()->page()->breadcrumb(
                        [
                            Html::escapeHTML(App::blog()->name()) => '',
                            __('Pages')                           => App::backend()->url()->get('admin.plugin.pages'),
                            __('Typographic replacements')        => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
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
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    App::nonce()->formNonce(),
                    ... $ap->hiddenFields(),
                    (new Hidden('full_content', 'true')),
                    (new Hidden('action', 'typo')),
                    (new Hidden(['process'], ($type === 'post' ? 'Posts' : 'Plugin'))),
                    App::nonce()->formNonce(),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    public static function adminCommentsActions(ActionsComments $ap): void
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_CONTENT_ADMIN,
        ]), App::blog()->id())) {
            $settings = My::settings();
            if ($settings->active && $settings->comments) {
                $ap->addAction(
                    [__('Typo') => [__('Typographic replacements') => 'typo']],
                    self::adminCommentsDoReplacements(...)
                );
            }
        }
    }

    /**
     * @param      ActionsComments              $ap     Actions
     * @param      ArrayObject<string, mixed>   $post   The post
     */
    public static function adminCommentsDoReplacements(ActionsComments $ap, ArrayObject $post): void
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $co = $ap->getRS();
            if ($co->rows()) {
                $settings    = My::settings();
                $dashes_mode = $settings->dashes_mode;
                while ($co->fetch()) {
                    if ($co->comment_content) {
                        # Apply typo features to comment
                        $cur                  = App::db()->con()->openCursor(App::db()->con()->prefix() . App::blog()::COMMENT_TABLE_NAME);
                        $cur->comment_content = SmartyPants::transform($co->comment_content, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                        $cur->update('WHERE comment_id = ' . (int) $co->comment_id);
                    }
                }

                $ap->redirect(true, ['upd' => 1]);
            } else {
                $ap->redirect();
            }
        } else {
            // Ask confirmation for replacements
            $ap->beginPage(
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
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    App::nonce()->formNonce(),
                    ...$ap->hiddenFields(),
                    (new Hidden('full_content', 'true')),
                    (new Hidden('action', 'typo')),
                    (new Hidden(['process'], 'Comments')),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    /**
     * @param      array<int, list{0:mixed, 1:string}>|ArrayObject<int, list{0:mixed, 1:string}>  $contents The content data
     *
     * Each item of $contents should be as:
     *  $content[0]: current HTML content
     *  $content[1]: content format (html, text, …)
     *
     * @since 2.34
     */
    public static function coreContentFilter(string $type, array|ArrayObject $contents): string
    {
        $settings = My::settings();
        if ($settings->active && $settings->entries) {
            $dashes_mode        = $settings->dashes_mode;
            $supported_syntaxes = ['html', 'xhtml'];

            foreach ($contents as $content) {
                if (!is_array($content) || count($content) < 2) {   // @phpstan-ignore-line PHPDoc should be certain but maybe…
                    continue;
                }
                if ($content[0] !== '' && in_array($content[1], $supported_syntaxes)) {
                    $pointer    = (string) $content[0];
                    $pointer    = SmartyPants::transform($pointer, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                    $content[0] = $pointer;
                }
            }
        }

        return '';
    }
}
