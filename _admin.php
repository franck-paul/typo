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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

// dead but useful code, in order to have translations
__('Typo') . __('Brings smart typographic replacements for your blog entries and comments');

require_once __DIR__ . '/inc/smartypants.php';

class adminTypo
{
    public static function adminDashboardFavorites($favs)
    {
        $favs->register('Typo', [
            'title'       => __('Typographic replacements'),
            'url'         => 'plugin.php?p=typo',
            'small-icon'  => [urldecode(dcPage::getPF('typo/icon.svg')), urldecode(dcPage::getPF('typo/icon-dark.svg'))],
            'large-icon'  => [urldecode(dcPage::getPF('typo/icon.svg')), urldecode(dcPage::getPF('typo/icon-dark.svg'))],
            'permissions' => dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_CONTENT_ADMIN,
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

    public static function adminPagesActions(dcPagesActions $ap)
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

    public static function adminPostsDoReplacements(dcPostsActions $ap, arrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'post');
    }

    public static function adminPagesDoReplacements(dcPagesActions $ap, arrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'page');
    }

    public static function adminEntriesDoReplacements($ap, arrayObject $post, $type = 'post')
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                $dashes_mode = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
                while ($posts->fetch()) {
                    if (($posts->post_excerpt_xhtml) || ($posts->post_content_xhtml)) {
                        # Apply typo features to entry
                        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::POST_TABLE_NAME);

                        if ($posts->post_excerpt_xhtml) {
                            $cur->post_excerpt_xhtml = SmartyPants($posts->post_excerpt_xhtml, ($dashes_mode ?: SMARTYPANTS_ATTR));
                        }

                        if ($posts->post_content_xhtml) {
                            $cur->post_content_xhtml = SmartyPants($posts->post_content_xhtml, ($dashes_mode ?: SMARTYPANTS_ATTR));
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
                            html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Pages')                                 => 'plugin.php?p=pages',
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => 'posts.php',
                            __('Typographic replacements')              => '',
                        ]
                    )
                );
            }

            dcPage::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<p><input type="submit" value="' . __('save') . '" /></p>' .

            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['full_content'], 'true') .
            form::hidden(['action'], 'typo') .
                '</form>';
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

    public static function adminCommentsDoReplacements(dcCommentsActions $ap, arrayObject $post)
    {
        if (!empty($post['full_content'])) {
            // Do replacements
            $co = $ap->getRS();
            if ($co->rows()) {
                $dashes_mode = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
                while ($co->fetch()) {
                    if ($co->comment_content) {
                        # Apply typo features to comment
                        $cur                  = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::COMMENT_TABLE_NAME);
                        $cur->comment_content = SmartyPants($co->comment_content, ($dashes_mode ?: SMARTYPANTS_ATTR));
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
                        html::escapeHTML(dcCore::app()->blog->name) => '',
                        __('Comments')                              => 'comments.php',
                        __('Typographic replacements')              => '',
                    ]
                )
            );

            dcPage::warning(__('Warning! These replacements will not be undoable.'), false, false);

            echo
            '<form action="' . $ap->getURI() . '" method="post">' .
            $ap->getCheckboxes() .
            '<p><input type="submit" value="' . __('save') . '" /></p>' .

            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['full_content'], 'true') .
            form::hidden(['action'], 'typo') .
                '</form>';
            $ap->endPage();
        }
    }

    public static function updateTypoEntries($ref)
    {
        if (dcCore::app()->blog->settings->typo->typo_active && dcCore::app()->blog->settings->typo->typo_entries && @is_array($ref)) {
            $dashes_mode = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
            /* Transform typo for excerpt (HTML) */
            if (isset($ref['excerpt_xhtml'])) {
                $excerpt = &$ref['excerpt_xhtml'];
                if ($excerpt) {
                    $excerpt = SmartyPants($excerpt, ($dashes_mode ?: SMARTYPANTS_ATTR));
                }
            }
            /* Transform typo for content (HTML) */
            if (isset($ref['content_xhtml'])) {
                $content = &$ref['content_xhtml'];
                if ($content) {
                    $content = SmartyPants($content, ($dashes_mode ?: SMARTYPANTS_ATTR));
                }
            }
        }
    }

    public static function updateTypoComments($blog, $cur)
    {
        if (dcCore::app()->blog->settings->typo->typo_active && dcCore::app()->blog->settings->typo->typo_comments && !(bool) $cur->comment_trackback && $cur->comment_content != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode          = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
            $cur->comment_content = SmartyPants($cur->comment_content, ($dashes_mode ?: SMARTYPANTS_ATTR));
        }
    }
}

/* Add behavior callback, will be used for all types of posts (standard, page, galery item, ...) */
dcCore::app()->addBehavior('coreAfterPostContentFormat', [adminTypo::class, 'updateTypoEntries']);

/* Add behavior callbacks, will be used for all comments (not trackbacks) */
dcCore::app()->addBehavior('coreBeforeCommentCreate', [adminTypo::class, 'updateTypoComments']);
dcCore::app()->addBehavior('coreBeforeCommentUpdate', [adminTypo::class, 'updateTypoComments']);

/* Add menu item in extension list */
dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
    __('Typographic replacements'),
    'plugin.php?p=typo',
    [urldecode(dcPage::getPF('typo/icon.svg')), urldecode(dcPage::getPF('typo/icon-dark.svg'))],
    preg_match('/plugin.php\?p=typo(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
        dcAuth::PERMISSION_CONTENT_ADMIN,
    ]), dcCore::app()->blog->id)
);

/* Register favorite */
dcCore::app()->addBehavior('adminDashboardFavoritesV2', [adminTypo::class, 'adminDashboardFavorites']);

/* Add behavior callbacks for posts actions */
dcCore::app()->addBehavior('adminPostsActions', [adminTypo::class, 'adminPostsActions']);
dcCore::app()->addBehavior('adminPagesActions', [adminTypo::class, 'adminPagesActions']);

/* Add behavior callbacks for comments actions */
dcCore::app()->addBehavior('adminCommentsActions', [adminTypo::class, 'adminCommentsActions']);
