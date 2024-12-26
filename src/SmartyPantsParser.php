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

class SmartyPantsParser
{
    # Options to specify which transformations to make:
    public int $do_nothing   = 0;
    public int $do_quotes    = 0;
    public int $do_backticks = 0;
    public int $do_dashes    = 0;
    public int $do_ellipses  = 0;
    public int $do_stupefy   = 0;
    public int $convert_quot = 0; # should we translate &quot; entities into normal quotes?

    # SmartyPants will not alter the content of these tags:
    private const SMARTYPANTS_TAGS_TO_SKIP = 'pre|code|kbd|script|style|math';

    public function __construct(string $attr = SmartyPants::SMARTYPANTS_ATTR)
    {
        #
        # Initialize a SmartyPants_Parser with certain attributes.
        #
        # Parser attributes:
        # 0 : do nothing
        # 1 : set all
        # 2 : set all, using old school en- and em- dash shortcuts
        # 3 : set all, using inverted old school en and em- dash shortcuts
        #
        # q : quotes
        # b : backtick quotes (``double'' only)
        # B : backtick quotes (``double'' and `single')
        # d : dashes
        # D : old school dashes
        # i : inverted old school dashes
        # e : ellipses
        # w : convert &quot; entities to " for Dreamweaver users
        #
        if ($attr === SmartyPants::SMARTYPANTS_ATTR_EM0_EN0) {
            $this->do_nothing = 1;
        } elseif ($attr === SmartyPants::SMARTYPANTS_ATTR_EM2_EN0) {
            # Do everything, turn all options on.
            $this->do_quotes    = 1;
            $this->do_backticks = 1;
            $this->do_dashes    = 1;
            $this->do_ellipses  = 1;
        } elseif ($attr === SmartyPants::SMARTYPANTS_ATTR_EM3_EN2) {
            # Do everything, turn all options on, use old school dash shorthand.
            $this->do_quotes    = 1;
            $this->do_backticks = 1;
            $this->do_dashes    = 2;
            $this->do_ellipses  = 1;
        } elseif ($attr === SmartyPants::SMARTYPANTS_ATTR_EM2_EN3) {
            # Do everything, turn all options on, use inverted old school dash shorthand.
            $this->do_quotes    = 1;
            $this->do_backticks = 1;
            $this->do_dashes    = 3;
            $this->do_ellipses  = 1;
        } elseif ($attr === '-1') {
            # Special "stupefy" mode.
            $this->do_stupefy = 1;
        } else {
            $chars = preg_split('//', $attr);
            if ($chars !== false) {
                foreach ($chars as $c) {
                    if ($c === 'q') {
                        $this->do_quotes = 1;
                    } elseif ($c === 'b') {
                        $this->do_backticks = 1;
                    } elseif ($c === 'B') {
                        $this->do_backticks = 2;
                    } elseif ($c === 'd') {
                        $this->do_dashes = 1;
                    } elseif ($c === 'D') {
                        $this->do_dashes = 2;
                    } elseif ($c === 'i') {
                        $this->do_dashes = 3;
                    } elseif ($c === 'e') {
                        $this->do_ellipses = 1;
                    } elseif ($c === 'w') {
                        $this->convert_quot = 1;
                    }

                    # Unknown attribute option, ignore.
                }
            }
        }
    }

    public function transform(string $text): string
    {
        if ($this->do_nothing !== 0) {
            return $text;
        }

        $tokens = $this->tokenizeHTML($text);
        $result = '';
        $in_pre = 0;  # Keep track of when we're inside <pre> or <code> tags.

        $prev_token_last_char = ''; # This is a cheat, used to get some context
        # for one-character tokens that consist of
        # just a quote char. What we do is remember
        # the last character of the previous text
        # token, to use as context to curl single-
        # character quote tokens correctly.

        foreach ($tokens as $cur_token) {
            if ($cur_token[0] == 'tag') {
                # Don't mess with quotes inside tags.
                $result .= $cur_token[1];
                if (preg_match('@<(/?)(?:' . self::SMARTYPANTS_TAGS_TO_SKIP . ')[\s>]@', $cur_token[1], $matches)) {
                    $in_pre = $matches[1] === '/' ? 0 : 1;
                }
            } else {
                $t         = $cur_token[1];
                $last_char = substr($t, -1); # Remember last char of this token before processing.
                if ($in_pre === 0) {
                    $t = $this->educate($t, $prev_token_last_char);
                }
                $prev_token_last_char = $last_char;
                $result .= $t;
            }
        }

        return $result;
    }

    public function educate(string $t, string $prev_token_last_char): string
    {
        $t = $this->processEscapes($t);

        if ($this->convert_quot !== 0) {
            $t = (string) preg_replace('/&quot;/', '"', $t);
        }

        if ($this->do_dashes !== 0) {
            if ($this->do_dashes == 1) {
                $t = $this->educateDashes($t);
            }
            if ($this->do_dashes == 2) {
                $t = $this->educateDashesOldSchool($t);
            }
            if ($this->do_dashes == 3) {
                $t = $this->educateDashesOldSchoolInverted($t);
            }
        }

        if ($this->do_ellipses !== 0) {
            $t = $this->educateEllipses($t);
        }

        # Note: backticks need to be processed before quotes.
        if ($this->do_backticks !== 0) {
            $t = $this->educateBackticks($t);
            if ($this->do_backticks == 2) {
                $t = $this->educateSingleBackticks($t);
            }
        }

        if ($this->do_quotes !== 0) {
            if ($t == "'") {
                # Special case: single-character ' token
                $t = preg_match('/\S/', $prev_token_last_char) ? '&#8217;' : '&#8216;';
            } elseif ($t == '"') {
                # Special case: single-character " token
                $t = preg_match('/\S/', $prev_token_last_char) ? '&#8221;' : '&#8220;';
            } else {
                # Normal case:
                $t = $this->educateQuotes($t);
            }
        }

        if ($this->do_stupefy !== 0) {
            $t = $this->stupefyEntities($t);
        }

        return $t;
    }

    public function educateQuotes(string $_): string
    {
        #
        #   Parameter:  String.
        #
        #   Returns:    The string, with "educated" curly quote HTML entities.
        #
        #   Example input:  "Isn't this fun?"
        #   Example output: &#8220;Isn&#8217;t this fun?&#8221;
        #
        # Make our own "punctuation" character class, because the POSIX-style
        # [:PUNCT:] is only available in Perl 5.6 or later:
        $punct_class = "[!\"#\\$\\%'()*+,-.\\/:;<=>?\\@\\[\\\\\]\\^_`{|}~]";

        # Special case if the very first character is a quote
        # followed by punctuation at a non-word-break. Close the quotes by brute force:
        $_ = (string) preg_replace(
            ["/^'(?=$punct_class\\B)/", "/^\"(?=$punct_class\\B)/"],
            ['&#8217;',                 '&#8221;'],
            $_
        );

        # Special case for double sets of quotes, e.g.:
        #   <p>He said, "'Quoted' words in a larger quote."</p>
        $_ = (string) preg_replace(
            ["/\"'(?=\w)/",    "/'\"(?=\w)/"],
            ['&#8220;&#8216;', '&#8216;&#8220;'],
            $_
        );

        # Special case for decade abbreviations (the '80s):
        $_ = (string) preg_replace("/'(?=\\d{2}s)/", '&#8217;', $_);

        $close_class = '[^\ \t\r\n\[\{\(\-]';
        $dec_dashes  = '&\#8211;|&\#8212;';

        # Get most opening single quotes:
        $_ = (string) preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			'                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1&#8216;', $_);
        # Single closing quotes:
        $_ = (string) preg_replace("{
			($close_class)?
			'
			(?(1)|          # If $1 captured, then do nothing;
			  (?=\\s | s\\b)  # otherwise, positive lookahead for a whitespace
			)               # char or an 's' at a word ending position. This
							# is a special case to handle something like:
							# \"<i>Custer</i>'s Last Stand.\"
			}xi", '\1&#8217;', $_);

        # Any remaining single quotes should be opening ones:
        $_ = str_replace("'", '&#8216;', $_);

        # Get most opening double quotes:
        $_ = (string) preg_replace("{
			(
				\\s          |   # a whitespace char, or
				&nbsp;      |   # a non-breaking space entity, or
				--          |   # dashes, or
				&[mn]dash;  |   # named dash entities
				$dec_dashes |   # or decimal entities
				&\\#x201[34];    # or hex
			)
			\"                   # the quote
			(?=\\w)              # followed by a word character
			}x", '\1&#8220;', $_);

        # Double closing quotes:
        $_ = (string) preg_replace("{
			($close_class)?
			\"
			(?(1)|(?=\\s))   # If $1 captured, then do nothing;
							   # if not, then make sure the next char is whitespace.
			}x", '\1&#8221;', $_);

        # Any remaining quotes should be opening ones.
        $_ = str_replace('"', '&#8220;', $_);

        return $_;
    }

    public function educateBackticks(string $_): string
    {
        #
        #   Parameter:  String.
        #   Returns:    The string, with ``backticks'' -style double quotes
        #               translated into HTML curly quote entities.
        #
        #   Example input:  ``Isn't this fun?''
        #   Example output: &#8220;Isn't this fun?&#8221;
        #

        $_ = str_replace(
            ['``',       "''"],
            ['&#8220;', '&#8221;'],
            $_
        );

        return $_;
    }

    public function educateSingleBackticks(string $_): string
    {
        #
        #   Parameter:  String.
        #   Returns:    The string, with `backticks' -style single quotes
        #               translated into HTML curly quote entities.
        #
        #   Example input:  `Isn't this fun?'
        #   Example output: &#8216;Isn&#8217;t this fun?&#8217;
        #

        $_ = str_replace(
            ['`',       "'"],
            ['&#8216;', '&#8217;'],
            $_
        );

        return $_;
    }

    public function educateDashes(string $_): string
    {
        #
        #   Parameter:  String.
        #
        #   Returns:    The string, with each instance of "--" translated to
        #               an em-dash HTML entity.
        #

        $_ = str_replace('--', '&#8212;', $_);

        return $_;
    }

    public function educateDashesOldSchool(string $_): string
    {
        #
        #   Parameter:  String.
        #
        #   Returns:    The string, with each instance of "--" translated to
        #               an en-dash HTML entity, and each "---" translated to
        #               an em-dash HTML entity.
        #

        #                      em         en
        $_ = str_replace(
            ['---',     '--'],
            ['&#8212;', '&#8211;'],
            $_
        );

        return $_;
    }

    public function educateDashesOldSchoolInverted(string $_): string
    {
        #
        #   Parameter:  String.
        #
        #   Returns:    The string, with each instance of "--" translated to
        #               an em-dash HTML entity, and each "---" translated to
        #               an en-dash HTML entity. Two reasons why: First, unlike the
        #               en- and em-dash syntax supported by
        #               EducateDashesOldSchool(), it's compatible with existing
        #               entries written before SmartyPants 1.1, back when "--" was
        #               only used for em-dashes.  Second, em-dashes are more
        #               common than en-dashes, and so it sort of makes sense that
        #               the shortcut should be shorter to type. (Thanks to Aaron
        #               Swartz for the idea.)
        #

        #                      en         em
        $_ = str_replace(
            ['---',     '--'],
            ['&#8211;', '&#8212;'],
            $_
        );

        return $_;
    }

    public function educateEllipses(string $_): string
    {
        #
        #   Parameter:  String.
        #   Returns:    The string, with each instance of "..." translated to
        #               an ellipsis HTML entity. Also converts the case where
        #               there are spaces between the dots.
        #
        #   Example input:  Huh...?
        #   Example output: Huh&#8230;?
        #

        $_ = str_replace(['...',     '. . .'], '&#8230;', $_);

        return $_;
    }

    public function stupefyEntities(string $_): string
    {
        #
        #   Parameter:  String.
        #   Returns:    The string, with each SmartyPants HTML entity translated to
        #               its ASCII counterpart.
        #
        #   Example input:  &#8220;Hello &#8212; world.&#8221;
        #   Example output: "Hello -- world."
        #

        #  en-dash    em-dash
        $_ = str_replace(
            ['&#8211;', '&#8212;'],
            ['-',       '--'],
            $_
        );

        # single quote         open       close
        $_ = str_replace(['&#8216;', '&#8217;'], "'", $_);

        # double quote         open       close
        $_ = str_replace(['&#8220;', '&#8221;'], '"', $_);

        return str_replace('&#8230;', '...', $_); # ellipsis
    }

    public function processEscapes(string $_): string
    {
        #
        #   Parameter:  String.
        #   Returns:    The string, with after processing the following backslash
        #               escape sequences. This is useful if you want to force a "dumb"
        #               quote or other character to appear.
        #
        #               Escape  Value
        #               ------  -----
        #               \\      &#92;
        #               \"      &#34;
        #               \'      &#39;
        #               \.      &#46;
        #               \-      &#45;
        #               \`      &#96;
        #
        $_ = str_replace(
            ['\\\\',  '\"',    "\'",    '\.',    '\-',    '\`'],
            ['&#92;', '&#34;', '&#39;', '&#46;', '&#45;', '&#96;'],
            $_
        );

        return $_;
    }

    /**
     * @param      string  $str    The string
     *
     * @return     array<int<0, max>, array{'tag'|'text', string}>
     */
    public function tokenizeHTML(string $str): array
    {
        #
        #   Parameter:  String containing HTML markup.
        #   Returns:    An array of the tokens comprising the input
        #               string. Each token is either a tag (possibly with nested,
        #               tags contained therein, such as <a href="<MTFoo>">, or a
        #               run of text between tags. Each element of the array is a
        #               two-element array; the first is either 'tag' or 'text';
        #               the second is the actual value.
        #
        #
        #   Regular expression derived from the _tokenize() subroutine in
        #   Brad Choate's MTRegex plugin.
        #   <http://www.bradchoate.com/past/mtregex.php>
        #
        $index  = 0;
        $tokens = [];

        $match = '(?s:<!--.*?-->)|' .	# comment
                 '(?s:<\?.*?\?>)|' .				# processing instruction
                                                # regular tags
                 '(?:<[/!$]?[-a-zA-Z0-9:]+\b(?>[^"\'>]+|"[^"]*"|\'[^\']*\')*>)';

        $parts = preg_split("{($match)}", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts !== false) {
            foreach ($parts as $part) {
                $tokens[] = ++$index % 2 && $part !== '' ? ['text', $part] : ['tag', $part];
            }
        }

        return $tokens;
    }
}
