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
use Exception;

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && dcCore::app()->newVersion(My::id(), dcCore::app()->plugins->moduleInfo(My::id(), 'version'));

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        try {
            // Default state is active for entries content and inactive for comments
            $settings = dcCore::app()->blog->settings->typo;
            $settings->put('typo_active', true, 'boolean', 'Active', false, true);
            $settings->put('typo_entries', true, 'boolean', 'Apply on entries', false, true);
            $settings->put('typo_comments', false, 'boolean', 'Apply on comments', false, true);
            $settings->put('typo_dashes_mode', (int) SmartyPants::SMARTYPANTS_ATTR, 'integer', 'Dashes replacement mode', false, true);

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
