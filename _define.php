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
$this->registerModule(
    'Typo',
    'Brings smart typographic replacements for your blog entries and comments',
    'Franck Paul and contributors',
    '7.0',
    [
        'date'        => '2025-05-20T11:02:55+0200',
        'requires'    => [['core', '2.34']],
        'permissions' => 'My',
        'type'        => 'plugin',

        'details'    => 'https://open-time.net/docs/plugins/typo',
        'support'    => 'https://github.com/franck-paul/typo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/typo/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
