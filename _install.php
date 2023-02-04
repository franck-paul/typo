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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

try {
    // Default state is active for entries content and inactive for comments
    dcCore::app()->blog->settings->typo->put('typo_active', true, 'boolean', 'Active', false, true);
    dcCore::app()->blog->settings->typo->put('typo_entries', true, 'boolean', 'Apply on entries', false, true);
    dcCore::app()->blog->settings->typo->put('typo_comments', false, 'boolean', 'Apply on comments', false, true);
    dcCore::app()->blog->settings->typo->put('typo_dashes_mode', 1, 'integer', 'Dashes replacement mode', false, true);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
