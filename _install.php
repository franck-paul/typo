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

$new_version = $core->plugins->moduleInfo('typo', 'version');
$old_version = $core->getVersion('typo');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try {
    if (version_compare(DC_VERSION, '2.6', '<')) {
        throw new Exception('Typo requires Dotclear 2.6+');
    }

    $core->blog->settings->addNamespace('typo');

    // Default state is active for entries content and inactive for comments
    $core->blog->settings->typo->put('typo_active', true, 'boolean', 'Active', false, true);
    $core->blog->settings->typo->put('typo_entries', true, 'boolean', 'Apply on entries', false, true);
    $core->blog->settings->typo->put('typo_comments', false, 'boolean', 'Apply on comments', false, true);
    $core->blog->settings->typo->put('typo_dashes_mode', 1, 'integer', 'Dashes replacement mode', false, true);

    $core->setVersion('typo', $new_version);

    return true;
} catch (Exception $e) {
    $core->error->add($e->getMessage());
}

return false;
