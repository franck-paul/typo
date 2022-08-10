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
if (!defined('DC_RC_PATH')) {
    return;
}

require_once __DIR__ . '/inc/smartypants.php';

/* Add behavior callback, will be used for all types of posts (standard, page, galery item, ...) */
dcCore::app()->addBehavior('coreAfterPostContentFormat', ['xmlrpcTypo', 'updateTypoEntries']);

/* Add behavior callbacks, will be used for all comments (not trackbacks) */
dcCore::app()->addBehavior('coreBeforeCommentCreate', ['xmlrpcTypo', 'updateTypoComments']);
dcCore::app()->addBehavior('coreBeforeCommentUpdate', ['xmlrpcTypo', 'updateTypoComments']);

class xmlrpcTypo
{
    public static function updateTypoEntries($ref)
    {
        if (dcCore::app()->blog->settings->typo->typo_active && dcCore::app()->blog->settings->typo->typo_entries) {
            if (@is_array($ref)) {
                $dashes_mode = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
                /* Transform typo for excerpt (XHTML) */
                if (isset($ref['excerpt_xhtml'])) {
                    $excerpt = &$ref['excerpt_xhtml'];
                    if ($excerpt) {
                        $excerpt = SmartyPants($excerpt, ($dashes_mode ?: SMARTYPANTS_ATTR));
                    }
                }
                /* Transform typo for content (XHTML) */
                if (isset($ref['content_xhtml'])) {
                    $content = &$ref['content_xhtml'];
                    if ($content) {
                        $content = SmartyPants($content, ($dashes_mode ?: SMARTYPANTS_ATTR));
                    }
                }
            }
        }
    }

    public static function updateTypoComments($blog, $cur)
    {
        if (dcCore::app()->blog->settings->typo->typo_active && dcCore::app()->blog->settings->typo->typo_comments) {
            /* Transform typo for comment content (XHTML) */
            if (!(bool) $cur->comment_trackback) {
                if ($cur->comment_content != null) {
                    $dashes_mode          = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
                    $cur->comment_content = SmartyPants($cur->comment_content, ($dashes_mode ?: SMARTYPANTS_ATTR));
                }
            }
        }
    }
}
