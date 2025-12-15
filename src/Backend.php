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
use Dotclear\Helper\Process\TraitProcess;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Typo');
        __('Brings smart typographic replacements for your blog entries and comments');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        /* Add menu item in extension list */
        My::addBackendMenuItem(App::backend()->menus()::MENU_BLOG);

        if (My::checkContext(My::MENU)) {
            App::behavior()->addBehaviors([
                // Register favorite
                'adminDashboardFavoritesV2' => BackendBehaviors::adminDashboardFavorites(...),
            ]);
        }

        App::behavior()->addBehaviors([
            // Add behavior callback, will be used for all types of posts (standard, page, galery item, ...)
            // 'coreAfterPostContentFormat' => BackendBehaviors::updateTypoEntries(...),
            'coreContentFilter' => BackendBehaviors::coreContentFilter(...),

            // Add behavior callbacks for posts actions
            'adminPostsActions' => BackendBehaviors::adminPostsActions(...),
            'adminPagesActions' => BackendBehaviors::adminPagesActions(...),

            // Add behavior callbacks for comments actions
            'adminCommentsActions' => BackendBehaviors::adminCommentsActions(...),

            // Add behavoir callbacks for other direct fields
            'adminPageHTMLHead' => BackendBehaviors::adminPageHTMLHead(...),
        ]);

        App::rest()->addFunction('typoTransform', BackendRest::typoTransform(...));

        return true;
    }
}
