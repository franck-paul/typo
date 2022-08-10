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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Typo',                                                                     // Name
    'Brings smart typographic replacements for your blog entries and comments', // Description
    'Franck Paul and contributors',                                             // Author
    '1.12',                                                                     // Version
    [
        'requires'    => [['core', '2.23']],                        // Dependencies
        'permissions' => 'usage,contentadmin',                      // Permissions
        'type'        => 'plugin',                                  // Type

        'details'    => 'https://open-time.net/docs/plugins/typo', // Details
        'support'    => 'https://github.com/franck-paul/typo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/typo/master/dcstore.xml',
    ]
);
