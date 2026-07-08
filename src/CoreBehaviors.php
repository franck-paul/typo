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

class CoreBehaviors
{
    /**
     * @param      array<int, array<int, string>>  $ref    The content to filter
     *
     * @since   2.34
     */
    public static function coreContentFilter(string $context, array $ref): string
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

            foreach ($ref as $content) {
                if (isset($content[1]) && $content[1] === 'html') {
                    /* Transform typo for HTML content */
                    $buffer = &$content[0];
                    if ($buffer !== '') {
                        $buffer = SmartyPants::transform($buffer, (string) $dashes_mode);
                    }
                }
            }
        }

        return '';
    }
}
