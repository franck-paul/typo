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
    'Typography',
    'Brings smart typographic replacements for your blog entries and comments',
    'Franck Paul and contributors',
    '8.4',
    [
        'date'        => '2025-12-15T08:56:33+0100',
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',

        'details'    => 'https://open-time.net/docs/plugins/typo',
        'support'    => 'https://github.com/franck-paul/typo',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/typo/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
