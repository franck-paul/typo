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

use dcCore;

class FrontendBehaviors
{
    public static function updateTypoComments($blog, $cur)
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->active && $settings->comments && !(bool) $cur->comment_trackback && $cur->comment_content != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode          = (int) $settings->dashes_mode;
            $cur->comment_content = SmartyPants::transform($cur->comment_content, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }
    }
    public static function previewTypoComments($prv)
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->active && $settings->comments && $prv['content'] != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode    = (int) $settings->dashes_mode;
            $prv['content'] = SmartyPants::transform($prv['content'], ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }
    }
}
