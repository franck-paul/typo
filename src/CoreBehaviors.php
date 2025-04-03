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
            'post'     => $settings->entries,
            'comment'  => $settings->comments,
            'category' => true, // $settings->categories,
            ''         => true,
            default    => true,
        };
        if ($do) {
            $dashes_mode = $settings->dashes_mode;
            foreach ($ref as $content) {
                if (isset($content[1]) && $content[1] === 'html') {
                    /* Transform typo for HTML content */
                    $buffer = &$content[0];
                    if ($buffer !== '') {
                        $buffer = SmartyPants::transform($buffer, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
                    }
                }
            }
        }

        return '';
    }
}
