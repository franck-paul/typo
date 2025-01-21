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
    '5.2',
    [
        'date'        => '2003-08-13T13:42:00+0100',
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',

        'details'    => 'https://open-time.net/docs/plugins/typo',
        'support'    => 'https://github.com/franck-paul/typo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/typo/main/dcstore.xml',
    ]
);
