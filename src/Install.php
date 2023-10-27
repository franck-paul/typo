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

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Interface\Core\BlogWorkspaceInterface;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $old_version = App::version()->getVersion(My::id());
            if (version_compare((string) $old_version, '3.1', '<')) {
                // Change settings names (remove wc_ prefix in them)
                $rename = static function (string $name, BlogWorkspaceInterface $settings) : void {
                    if ($settings->settingExists('typo_' . $name, true)) {
                        $settings->rename('typo_' . $name, $name);
                    }
                };

                $settings = My::settings();

                $rename('active', $settings);
                $rename('entries', $settings);
                $rename('comments', $settings);
                $rename('dashes_mode', $settings);
            }

            // Default state is active for entries content and inactive for comments
            $settings = My::settings();
            $settings->put('active', true, 'boolean', 'Active', false, true);
            $settings->put('entries', true, 'boolean', 'Apply on entries', false, true);
            $settings->put('comments', false, 'boolean', 'Apply on comments', false, true);
            $settings->put('dashes_mode', (int) SmartyPants::SMARTYPANTS_ATTR, 'integer', 'Dashes replacement mode', false, true);
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        return true;
    }
}
