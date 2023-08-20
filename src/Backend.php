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
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Typo') . __('Brings smart typographic replacements for your blog entries and comments');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        /* Add menu item in extension list */
        dcCore::app()->admin->menus[Menus::MENU_BLOG]->addItem(
            __('Typographic replacements'),
            My::manageUrl(),
            My::icons(),
            preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
            My::checkContext(My::MENU)
        );

        if (My::checkContext(My::MENU)) {
            dcCore::app()->addBehaviors([
                // Register favorite
                'adminDashboardFavoritesV2' => BackendBehaviors::adminDashboardFavorites(...),
            ]);
        }

        dcCore::app()->addBehaviors([
            // Add behavior callback, will be used for all types of posts (standard, page, galery item, ...)
            'coreAfterPostContentFormat' => BackendBehaviors::updateTypoEntries(...),

            // Add behavior callbacks, will be used for all comments (not trackbacks)
            'coreBeforeCommentCreate' => BackendBehaviors::updateTypoComments(...),
            'coreBeforeCommentUpdate' => BackendBehaviors::updateTypoComments(...),

            // Add behavior callbacks for posts actions
            'adminPostsActions' => BackendBehaviors::adminPostsActions(...),
            'adminPagesActions' => BackendBehaviors::adminPagesActions(...),

            // Add behavior callbacks for comments actions
            'adminCommentsActions' => BackendBehaviors::adminCommentsActions(...),
        ]);

        return true;
    }
}
