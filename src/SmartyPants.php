<?php

/**
 * This class implements Smarty Pants.
 *
 * SmartyPants Typographer  -  Smart typography for web sites
 *
 * PHP SmartyPants & Typographer
 * Copyright (c) 2004-2013 Michel Fortin
 * <http://michelf.ca/>
 *
 * Original SmartyPants
 * Copyright (c) 2003-2004 John Gruber
 * <http://daringfireball.net/>
 */
declare(strict_types=1);

namespace Dotclear\Plugin\typo;

class SmartyPants
{
    public static bool $init = false;

    // SMARTYPANTS_VERSION              = '1.5.1f-php7' - Sun 23 Jan 2013
    // SMARTYPANTSTYPOGRAPHER_VERSION   = '1.0.1-php7'  - Sun 23 Jan 2013

    #
    # Default configuration:
    #
    #  1  ->  "--" for em-dashes; no en-dash support
    #  2  ->  "---" for em-dashes; "--" for en-dashes
    #  3  ->  "--" for em-dashes; "---" for en-dashes
    #
    public const SMARTYPANTS_ATTR_EM0_EN0 = '0';
    public const SMARTYPANTS_ATTR_EM2_EN0 = '1';
    public const SMARTYPANTS_ATTR_EM3_EN2 = '2';
    public const SMARTYPANTS_ATTR_EM2_EN3 = '3';

    public const SMARTYPANTS_ATTR = self::SMARTYPANTS_ATTR_EM2_EN0;

    public static function transform(string $text, string $attr = self::SMARTYPANTS_ATTR): string
    {
        // Transform text using parser.
        return (new SmartyPantsTypographerParser($attr))->transform($text);
    }

    public static function transformQuotes(string $text, string $attr = self::SMARTYPANTS_ATTR): string
    {
        switch ($attr) {
            case self::SMARTYPANTS_ATTR_EM0_EN0:  return $text;
            case self::SMARTYPANTS_ATTR_EM3_EN2:  $attr = 'qb';

                break;
            default: $attr = 'q';

                break;
        }

        return self::transform($text, $attr);
    }

    public static function transformDashes(string $text, string $attr = self::SMARTYPANTS_ATTR): string
    {
        switch ($attr) {
            case self::SMARTYPANTS_ATTR_EM0_EN0:  return $text;
            case self::SMARTYPANTS_ATTR_EM3_EN2:  $attr = 'D';

                break;
            case self::SMARTYPANTS_ATTR_EM2_EN3:  $attr = 'i';

                break;
            default: $attr = 'd';

                break;
        }

        return self::transform($text, $attr);
    }

    public static function transformEllipsis(string $text, string $attr = self::SMARTYPANTS_ATTR): string
    {
        switch ($attr) {
            case self::SMARTYPANTS_ATTR_EM0_EN0:  return $text;
            default: $attr = 'e';

                break;
        }

        return self::transform($text, $attr);
    }
}
