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

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Interface\Core\BlogWorkspaceInterface;
use Exception;

class Install
{
    use TraitProcess;

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
                $rename = static function (string $name, BlogWorkspaceInterface $settings): void {
                    if ($settings->settingExists('typo_' . $name, true)) {
                        $settings->rename('typo_' . $name, $name);
                    }
                };

                $settings = My::settings();

                $rename('active', $settings);
                $rename('entries', $settings);
                $rename('comments', $settings);
                $rename('categories', $settings);
                $rename('dashes_mode', $settings);
            }

            // Default state is active for entries content and inactive for comments
            $settings = My::settings();
            $settings->put('active', true, App::blogWorkspace()::NS_BOOL, 'Active', false, true);
            $settings->put('entries', true, App::blogWorkspace()::NS_BOOL, 'Apply on entry contents', false, true);
            $settings->put('entries_titles', true, App::blogWorkspace()::NS_BOOL, 'Apply on entry titles', false, true);
            $settings->put('comments', false, App::blogWorkspace()::NS_BOOL, 'Apply on comments', false, true);
            $settings->put('categories', false, App::blogWorkspace()::NS_BOOL, 'Apply on category descriptions', false, true);
            $settings->put('categories_titles', false, App::blogWorkspace()::NS_BOOL, 'Apply on category titles', false, true);
            $settings->put('medias', false, App::blogWorkspace()::NS_BOOL, 'Apply on media titles, alternate texts and descriptions', false, true);
            $settings->put('simplemenu', false, App::blogWorkspace()::NS_BOOL, 'Apply on simpleMenu labels and descriptions', false, true);
            $settings->put('blogroll', false, App::blogWorkspace()::NS_BOOL, 'Apply on blogroll titles, descriptions and categories', false, true);
            $settings->put('dashes_mode', (int) SmartyPants::SMARTYPANTS_ATTR, App::blogWorkspace()::NS_INT, 'Dashes replacement mode', false, true);
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        return true;
    }
}
