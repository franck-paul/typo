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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Radio;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends Process
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        // Manageable only by super-admin
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Saving new configuration
        if (!empty($_POST['saveconfig'])) {
            try {
                $typo_active      = !empty($_POST['active']);
                $typo_entries     = !empty($_POST['entries']);
                $typo_comments    = !empty($_POST['comments']);
                $typo_categories  = !empty($_POST['categories']);
                $typo_dashes_mode = (int) $_POST['dashes_mode'];

                $settings = My::settings();
                $settings->put('active', $typo_active, 'boolean');
                $settings->put('entries', $typo_entries, 'boolean');
                $settings->put('comments', $typo_comments, 'boolean');
                $settings->put('categories', $typo_categories, 'boolean');
                $settings->put('dashes_mode', $typo_dashes_mode, 'integer');
                App::blog()->triggerBlog();
                Notices::addSuccessNotice(__('Configuration successfully updated.'));
                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        // Getting current parameters
        $settings    = My::settings();
        $active      = (bool) $settings->active;
        $entries     = (bool) $settings->entries;
        $comments    = (bool) $settings->comments;
        $categories  = (bool) $settings->categories;
        $dashes_mode = (int) $settings->dashes_mode;

        $dashes_mode_options = [
            (int) SmartyPants::SMARTYPANTS_ATTR_EM2_EN0 => __('"--" for em-dashes; no en-dash support (default)'),
            (int) SmartyPants::SMARTYPANTS_ATTR_EM3_EN2 => __('"---" for em-dashes; "--" for en-dashes'),
            (int) SmartyPants::SMARTYPANTS_ATTR_EM2_EN3 => __('"--" for em-dashes; "---" for en-dashes'),
        ];
        $modes = [];
        $i     = 0;
        foreach ($dashes_mode_options as $k => $v) {
            $modes[] = (new Para())->items([
                (new Radio(['dashes_mode', 'dashes_mode-' . $i], $dashes_mode == $k))
                    ->value($k)
                    ->label((new Label($v, Label::INSIDE_TEXT_AFTER))),
            ]);
            ++$i;
        }

        Page::openModule(My::name());

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('Typographic replacements')        => '',
            ]
        );
        echo Notices::getNotices();

        echo
        (new Form('typo'))
        ->action(App::backend()->getPageURL())
        ->method('post')
        ->fields([
            (new Para())->items([
                (new Checkbox('active', $active))
                    ->value(1)
                    ->label((new Label(__('Enable typographic replacements for this blog'), Label::INSIDE_TEXT_AFTER))),
            ]),
            (new Fieldset())
            ->legend((new Legend(__('Options'))))
            ->fields([
                (new Para())->items([
                    (new Checkbox('entries', $entries))
                        ->value(1)
                        ->label((new Label(__('Enable typographic replacements for entries'), Label::INSIDE_TEXT_AFTER))),
                ]),
                (new Para())->items([
                    (new Checkbox('comments', $comments))
                        ->value(1)
                        ->label((new Label(__('Enable typographic replacements for comments'), Label::INSIDE_TEXT_AFTER))),
                ]),
                (new Para('trackbacks'))->class('form-note')->items([
                    (new Text(null, __('Excluding trackbacks'))),
                ]),
                (new Para())->items([
                    (new Checkbox('categories', $categories))
                        ->value(1)
                        ->label((new Label(__('Enable typographic replacements for categories'), Label::INSIDE_TEXT_AFTER))),
                ]),
                (new Note())
                    ->class('form-note')
                    ->text(__('Dotclear 2.34+ only')),
            ]),
            (new Fieldset())
            ->legend(new Legend(__('Dashes replacement mode')))
            ->fields($modes),
            (new Para())->items([
                (new Submit(['saveconfig'], __('Save configuration')))
                    ->accesskey('s'),
                ... My::hiddenFields(),
            ]),

        ])
        ->render();

        Page::closeModule();
    }
}
