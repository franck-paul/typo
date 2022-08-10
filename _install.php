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

$new_version = dcCore::app()->plugins->moduleInfo('typo', 'version');
$old_version = dcCore::app()->getVersion('typo');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try {
    dcCore::app()->blog->settings->addNamespace('typo');

    // Default state is active for entries content and inactive for comments
    dcCore::app()->blog->settings->typo->put('typo_active', true, 'boolean', 'Active', false, true);
    dcCore::app()->blog->settings->typo->put('typo_entries', true, 'boolean', 'Apply on entries', false, true);
    dcCore::app()->blog->settings->typo->put('typo_comments', false, 'boolean', 'Apply on comments', false, true);
    dcCore::app()->blog->settings->typo->put('typo_dashes_mode', 1, 'integer', 'Dashes replacement mode', false, true);

    dcCore::app()->setVersion('typo', $new_version);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
