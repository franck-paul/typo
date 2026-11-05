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
use Dotclear\Database\Cursor;
use Dotclear\Interface\Core\BlogInterface;

class FrontendBehaviors
{
    /**
     * @param      BlogInterface    $blog   The blog
     * @param      Cursor           $cursor    The current
     *
     * @deprecated since 2.34
     */
    public static function updateTypoComments(BlogInterface $blog, Cursor $cursor): string
    {
        App::deprecated()->set('', '2.34');

        $settings = My::settings();
        if ($settings->getBool('active', false)
            && $settings->getBool('comments', false)
            && !$cursor->boolField('comment_trackback', false)
            && $cursor->strField('comment_content') !== ''
        ) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;
            $cursor->setStrField('comment_content', SmartyPants::transform($cursor->strField('comment_content'), (string) $dashes_mode));
        }

        return '';
    }

    /**
     * @param      array<string, string>|ArrayObject<string, string>  $prv    The preview data
     *
     * @deprecated since 2.34
     */
    public static function previewTypoComments(array|ArrayObject $prv): string
    {
        App::deprecated()->set('', '2.34');

        $settings = My::settings();
        if ($settings->getBool('active', false)
            && $settings->getBool('comments', false)
            && $prv['content'] !== null
        ) {
            /* Transform typo for comment content (HTML) */
            $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

            $prv['content'] = SmartyPants::transform($prv['content'], (string) $dashes_mode);
        }

        return '';
    }
}
