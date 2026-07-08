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

class BackendRest
{
    /**
     * @param      array<string, string>   $get    The cleaned $_GET
     *
     * @return     array<string, mixed>
     */
    public static function typoTransform(array $get): array
    {
        $buffer = $get['buffer'] ?? '';

        $settings    = My::settings();
        $dashes_mode = $settings->getInt('dashes_mode', false) ?: (int) SmartyPants::SMARTYPANTS_ATTR;

        $str = SmartyPants::transform($buffer, (string) $dashes_mode);
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

        return [
            'ret'    => ($buffer !== $str),
            'buffer' => $str,
        ];
    }
}
