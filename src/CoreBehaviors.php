<?php

/**
 * @brief typo, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\typo;

use Dotclear\Core\Content;

class CoreBehaviors
{
    /**
     * @param      Content[]  $contents The content data
     *
     * @since 2.34 then 2.40 for new behavior V2
     */
    public static function coreContentFilter(string $context, array $contents): string
    {
        $settings = My::settings();
        $do       = match ($context) {
            'post'     => $settings->getBool('entries'),
            'comment'  => $settings->getBool('comments'),
            'category' => $settings->getBool('categories'),
            default    => true,
        };
        if ($do) {
            $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

            foreach ($contents as $content) {
                if ($content->getFormat() === 'html') {
                    /* Transform typo for HTML content */
                    $buffer = $content->getContent();
                    if ($buffer !== '') {
                        $buffer = SmartyPants::transform($buffer, (string) $dashes_mode);
                        $content->setContent($buffer);
                    }
                }
            }
        }

        return '';
    }
}
