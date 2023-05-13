# Typo plugin for Dotclear 2

[![Release](https://img.shields.io/github/v/release/franck-paul/typo)](https://github.com/franck-paul/typo/releases)
[![Date](https://img.shields.io/github/release-date/franck-paul/typo)](https://github.com/franck-paul/typo/releases)
[![Issues](https://img.shields.io/github/issues/franck-paul/typo)](https://github.com/franck-paul/typo/issues)
[![Dotaddict](https://img.shields.io/badge/dotaddict-official-green.svg)](https://plugins.dotaddict.org/dc2/details/typo)
[![License](https://img.shields.io/github/license/franck-paul/typo)](https://github.com/franck-paul/typo/blob/master/LICENSE)

This plugin use the [PHP SmartyPants & Typographer library](https://michelf.ca/projets/php-smartypants/) from Michel Fortin (PHP port of the [Original SmartyPants Perl library](https://daringfireball.net/projects/smartypants/) from John Gruber) that easily translates plain ASCII punctuation characters into “smart” typographic punctuation HTML entities.

## Example

The following text:

> Un jour \-- ou plutôt une nuit \--, je crois cela se déroulait pendant ma lecture de << À la recherche du temps perdu >>, il me semble\... Mais je m\'égare alors reprenons le fil de notre conversation : \"¿ Cómo estás ?\" me demandait le garçon de café auquel j\'avais répondu sans faire attention d\'un >>Ich weiß es nicht<< maladroit.

Will be transformed to this:

> Un jour — ou plutôt une nuit —, je crois que cela se déroulait pendant ma lecture de « À la recherche du temps perdu », il me semble… Mais je m’égare alors reprenons le fil de notre conversation : “¿ Cómo estás ?” me demandait le garçon de café auquel j’avais répondu sans faire attention d’un »Ich weiß es nicht« maladroit.
