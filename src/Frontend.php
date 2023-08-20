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
use Dotclear\Core\Process;

class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Don't do things in frontend if plugin disabled
        $settings = dcCore::app()->blog->settings->get(My::id());
        if (!(bool) $settings->active) {
            return false;
        }

        /* Add behavior callback for typo replacement in comments */
        dcCore::app()->addBehaviors([
            'coreBeforeCommentCreate'    => FrontendBehaviors::updateTypoComments(...),
            'publicBeforeCommentPreview' => FrontendBehaviors::previewTypoComments(...),
        ]);

        return true;
    }
}
