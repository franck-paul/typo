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
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Radio;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
use Exception;

class Manage extends dcNsProcess
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        // Manageable only by super-admin
        static::$init = My::checkContext(My::MANAGE);

        return static::$init;
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // Saving new configuration
        if (!empty($_POST['saveconfig'])) {
            try {
                $typo_active      = (empty($_POST['active'])) ? false : true;
                $typo_entries     = (empty($_POST['entries'])) ? false : true;
                $typo_comments    = (empty($_POST['comments'])) ? false : true;
                $typo_dashes_mode = (int) $_POST['dashes_mode'];

                $settings = dcCore::app()->blog->settings->get(My::id());
                $settings->put('active', $typo_active, 'boolean');
                $settings->put('entries', $typo_entries, 'boolean');
                $settings->put('comments', $typo_comments, 'boolean');
                $settings->put('dashes_mode', $typo_dashes_mode, 'integer');
                dcCore::app()->blog->triggerBlog();
                dcPage::addSuccessNotice(__('Configuration successfully updated.'));
                dcCore::app()->adminurl->redirect('admin.plugin.' . My::id());
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        // Getting current parameters
        $settings    = dcCore::app()->blog->settings->get(My::id());
        $active      = (bool) $settings->active;
        $entries     = (bool) $settings->entries;
        $comments    = (bool) $settings->comments;
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
            $i++;
        }

        dcPage::openModule(__('Typo'));

        echo dcPage::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Typographic replacements')              => '',
            ]
        );
        echo dcPage::notices();

        echo
        (new Form('typo'))
        ->action(dcCore::app()->admin->getPageURL())
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
            ]),
            (new Fieldset())
            ->legend(new Legend(__('Dashes replacement mode')))
            ->fields($modes),
            (new Para())->items([
                (new Submit(['saveconfig'], __('Save configuration')))
                    ->accesskey('s'),
                dcCore::app()->formNonce(false),
            ]),

        ])
        ->render();

        dcPage::closeModule();
    }
}
