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
use dcAuth;
use dcBlog;
use dcCore;
use Dotclear\Core\Backend\Action\ActionsComments;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Page;
use Dotclear\Database\Cursor;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    public static function adminDashboardFavorites(Favorites $favs): string
    {
        $favs->register('Typo', [
            'title'       => __('Typographic replacements'),
            'url'         => My::manageUrl(),
            'small-icon'  => My::icons(),
            'large-icon'  => My::icons(),
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_ADMIN,
            ]),
        ]);

        return '';
    }

    public static function adminPostsActions(ActionsPosts $ap): string
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                self::adminPostsDoReplacements(...)
            );
        }

        return '';
    }

    public static function adminPagesActions(PagesBackendActions $ap): string
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                self::adminPagesDoReplacements(...)
            );
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
                    if (($posts->post_excerpt_xhtml) || ($posts->post_content_xhtml)) {
                        # Apply typo features to entry
                        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::POST_TABLE_NAME);

                        if ($posts->post_excerpt_xhtml) {
                            $cur->post_excerpt_xhtml = SmartyPants::transform($posts->post_excerpt_xhtml, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                        }

                        if ($posts->post_content_xhtml) {
                            $cur->post_content_xhtml = SmartyPants::transform($posts->post_content_xhtml, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                        }

                        $cur->update('WHERE post_id = ' . (int) $posts->post_id);
                    }
                }
                $ap->redirect(true, ['upd' => 1]);
            } else {
                $ap->redirect();
            }
        } else {
            // Ask confirmation for replacements
            if ($type == 'page') {
                $ap->beginPage(
                    Page::breadcrumb(
                        [
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Pages')                                 => dcCore::app()->admin->url->get('admin.plugin.pages'),
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    Page::breadcrumb(
                        [
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => dcCore::app()->admin->url->get('admin.posts'),
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            }

            Page::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-entries-typo'))
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    ...$ap->hiddenFields(),
                    (new Hidden(['full_content'], 'true')),
                    (new Hidden(['action'], 'typo')),
                    (new Hidden(['process'], ($type === 'post' ? 'Posts' : 'Plugin'))),
                    dcCore::app()->formNonce(false),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    public static function adminCommentsActions(ActionsComments $ap): void
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                self::adminCommentsDoReplacements(...)
            );
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
                        $cur                  = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::COMMENT_TABLE_NAME);
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
                Page::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Comments')                              => dcCore::app()->admin->url->get('admin.comments'),
                        __('Typographic replacements')              => '',
                    ]
                )
            );

            Page::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-comments-typo'))
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    ...$ap->hiddenFields(),
                    ... My::hiddenFields([
                        'full_content' => 'true',
                        'action'       => 'typo',
                    ]),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    /**
     * @param      array<string, string>|ArrayObject<string, string>  $ref    The preview data
     *
     * @return     string
     */
    public static function updateTypoEntries(array|ArrayObject $ref): string
    {
        $settings = My::settings();
        if ($settings->active && $settings->entries && @is_array($ref)) {
            $dashes_mode = $settings->dashes_mode;
            /* Transform typo for excerpt (HTML) */
            if (isset($ref['excerpt_xhtml'])) {
                $excerpt = &$ref['excerpt_xhtml'];
                if ($excerpt) {
                    $excerpt = SmartyPants::transform($excerpt, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                }
            }
            /* Transform typo for content (HTML) */
            if (isset($ref['content_xhtml'])) {
                $content = &$ref['content_xhtml'];
                if ($content) {
                    $content = SmartyPants::transform($content, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                }
            }
        }

        return '';
    }

    public static function updateTypoComments(dcBlog $blog, Cursor $cur): string
    {
        $settings = My::settings();
        if ($settings->active && $settings->comments && !(bool) $cur->comment_trackback && $cur->comment_content != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode          = $settings->dashes_mode;
            $cur->comment_content = SmartyPants::transform($cur->comment_content, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }

        return '';
    }
}
