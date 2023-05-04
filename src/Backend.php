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
use dcAuth;
use dcCore;
use dcNsProcess;
use dcPage;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN');

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
            'plugin.php?p=typo',
            [urldecode(dcPage::getPF(My::id() . '/icon.svg')), urldecode(dcPage::getPF(My::id() . '/icon-dark.svg'))],
            preg_match('/plugin.php\?p=typo(&.*)?$/', $_SERVER['REQUEST_URI']),
            dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id)
        );

        dcCore::app()->addBehaviors([
            // Add behavior callback, will be used for all types of posts (standard, page, galery item, ...)
            'coreAfterPostContentFormat' => [BackendBehaviors::class, 'updateTypoEntries'],

            // Add behavior callbacks, will be used for all comments (not trackbacks)
            'coreBeforeCommentCreate' => [BackendBehaviors::class, 'updateTypoComments'],
            'coreBeforeCommentUpdate' => [BackendBehaviors::class, 'updateTypoComments'],

            // Register favorite
            'adminDashboardFavoritesV2' => [BackendBehaviors::class, 'adminDashboardFavorites'],

            // Add behavior callbacks for posts actions
            'adminPostsActions' => [BackendBehaviors::class, 'adminPostsActions'],
            'adminPagesActions' => [BackendBehaviors::class, 'adminPagesActions'],

            // Add behavior callbacks for comments actions
            'adminCommentsActions' => [BackendBehaviors::class, 'adminCommentsActions'],
        ]);

        return true;
    }
}
