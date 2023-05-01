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
use dcNsProcess;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_RC_PATH');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        /* Add behavior callback for typo replacement in comments */
        dcCore::app()->addBehaviors([
            'coreBeforeCommentCreate'    => [FrontendBehaviors::class, 'updateTypoComments'],
            'publicBeforeCommentPreview' => [FrontendBehaviors::class, 'previewTypoComments'],
        ]);

        return true;
    }
}