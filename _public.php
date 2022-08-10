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

/* Add behavior callback for typo replacement in comments */
dcCore::app()->addBehavior('coreBeforeCommentCreate', ['dcTypo', 'updateTypoComments']);
dcCore::app()->addBehavior('publicBeforeCommentPreview', ['dcTypo', 'previewTypoComments']);

class dcTypo
{
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
    public static function previewTypoComments($prv)
    {
        if (dcCore::app()->blog->settings->typo->typo_active && dcCore::app()->blog->settings->typo->typo_comments) {
            /* Transform typo for comment content (XHTML) */
            if ($prv['content'] != null) {
                $dashes_mode    = (int) dcCore::app()->blog->settings->typo->typo_dashes_mode;
                $prv['content'] = SmartyPants($prv['content'], ($dashes_mode ?: SMARTYPANTS_ATTR));
            }
        }
    }
}
