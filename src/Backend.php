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

use dcAdmin;
use dcCore;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        // dead but useful code, in order to have translations
        __('Typo') . __('Brings smart typographic replacements for your blog entries and comments');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        /* Add menu item in extension list */
        dcCore::app()->menu[dcAdmin::MENU_BLOG]->addItem(
            __('Typographic replacements'),
            My::makeUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        if (My::checkContext(My::MENU)) {
            dcCore::app()->addBehaviors([
                // Register favorite
                'adminDashboardFavoritesV2' => [BackendBehaviors::class, 'adminDashboardFavorites'],
            ]);
        }

        dcCore::app()->addBehaviors([
            // Add behavior callback, will be used for all types of posts (standard, page, galery item, ...)
            'coreAfterPostContentFormat' => [BackendBehaviors::class, 'updateTypoEntries'],

            // Add behavior callbacks, will be used for all comments (not trackbacks)
            'coreBeforeCommentCreate' => [BackendBehaviors::class, 'updateTypoComments'],
            'coreBeforeCommentUpdate' => [BackendBehaviors::class, 'updateTypoComments'],

            // Add behavior callbacks for posts actions
            'adminPostsActions' => [BackendBehaviors::class, 'adminPostsActions'],
            'adminPagesActions' => [BackendBehaviors::class, 'adminPagesActions'],

            // Add behavior callbacks for comments actions
            'adminCommentsActions' => [BackendBehaviors::class, 'adminCommentsActions'],
        ]);

        return true;
    }
}
