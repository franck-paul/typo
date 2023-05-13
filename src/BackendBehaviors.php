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
use dcCommentsActions;
use dcCore;
use dcPage;
use dcPostsActions;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    public static function adminDashboardFavorites($favs)
    {
        $favs->register('Typo', [
            'title'       => __('Typographic replacements'),
            'url'         => My::makeUrl(),
            'small-icon'  => My::icons(),
            'large-icon'  => My::icons(),
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_ADMIN,
            ]),
        ]);
    }

    public static function adminPostsActions(dcPostsActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                [self::class, 'adminPostsDoReplacements']
            );
        }
    }

    public static function adminPagesActions(PagesBackendActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                [self::class, 'adminPagesDoReplacements']
            );
        }
    }

    public static function adminPostsDoReplacements(dcPostsActions $ap, ArrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'post');
    }

    public static function adminPagesDoReplacements(PagesBackendActions $ap, ArrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'page');
    }

    public static function adminEntriesDoReplacements($ap, ArrayObject $post, $type = 'post')
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                $settings    = dcCore::app()->blog->settings->get(My::id());
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
                    dcPage::breadcrumb(
                        [
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Pages')                                 => dcCore::app()->adminurl->get('admin.plugin.pages'),
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => dcCore::app()->adminurl->get('admin.posts'),
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            }

            dcPage::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-entries-typo'))
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    dcCore::app()->formNonce(false),
                    ...$ap->hiddenFields(),
                    (new Hidden(['full_content'], 'true')),
                    (new Hidden(['action'], 'typo')),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    public static function adminCommentsActions(dcCommentsActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Typo') => [__('Typographic replacements') => 'typo']],
                [self::class, 'adminCommentsDoReplacements']
            );
        }
    }

    public static function adminCommentsDoReplacements(dcCommentsActions $ap, ArrayObject $post)
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $co = $ap->getRS();
            if ($co->rows()) {
                $settings    = dcCore::app()->blog->settings->get(My::id());
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
                dcPage::breadcrumb(
                    [
                        Html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Comments')                              => 'comments.php',
                        __('Typographic replacements')              => '',
                    ]
                )
            );

            dcPage::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            (new Form('ap-comments-typo'))
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-typo-do', __('Save'))),
                    dcCore::app()->formNonce(false),
                    ...$ap->hiddenFields(),
                    (new Hidden(['full_content'], 'true')),
                    (new Hidden(['action'], 'typo')),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }

    public static function updateTypoEntries($ref)
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
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
    }

    public static function updateTypoComments($blog, $cur)
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->active && $settings->comments && !(bool) $cur->comment_trackback && $cur->comment_content != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode          = $settings->dashes_mode;
            $cur->comment_content = SmartyPants::transform($cur->comment_content, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }
    }
}
