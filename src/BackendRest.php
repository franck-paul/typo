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
        $dashes_mode = $settings->dashes_mode;
        $str         = SmartyPants::transform($buffer, ($dashes_mode ? (string) $dashes_mode : SmartyPants::SMARTYPANTS_ATTR));
        $str         = html_entity_decode($str, ENT_QUOTES, 'UTF-8');

        return [
            'ret'    => ($buffer !== $str),
            'buffer' => $str,
        ];
    }
}
