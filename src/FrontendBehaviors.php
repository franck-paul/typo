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
use Dotclear\Database\Cursor;
use Dotclear\Interface\Core\BlogInterface;

class FrontendBehaviors
{
    /**
     * @param      BlogInterface    $blog   The blog
     * @param      Cursor           $cur    The current
     */
    public static function updateTypoComments(BlogInterface $blog, Cursor $cur): string
    {
        $settings = My::settings();
        if ($settings->active && $settings->comments && !(bool) $cur->comment_trackback && $cur->comment_content != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode          = (int) $settings->dashes_mode;
            $cur->comment_content = SmartyPants::transform($cur->comment_content, ($dashes_mode !== 0 ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }

        return '';
    }

    /**
     * @param      array<string, string>|ArrayObject<string, string>  $prv    The preview data
     */
    public static function previewTypoComments(array|ArrayObject $prv): string
    {
        $settings = My::settings();
        if ($settings->active && $settings->comments && $prv['content'] != null) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode    = (int) $settings->dashes_mode;
            $prv['content'] = SmartyPants::transform($prv['content'], ($dashes_mode !== 0 ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        }

        return '';
    }
}
