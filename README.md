# Typo plugin for Dotclear 2

This plugin use the [PHP SmartyPants & Typographer library](https://michelf.ca/projets/php-smartypants/) from Michel Fortin (PHP port of the [Original SmartyPants Perl library](https://daringfireball.net/projects/smartypants/) from John Gruber) that easily translates plain ASCII punctuation characters into “smart” typographic punctuation HTML entities.

## Example

The following text:

> Un jour \-- ou plutôt une nuit \--, je crois cela se déroulait pendant ma lecture de << À la recherche du temps perdu >>, il me semble\... Mais je m\'égare alors reprenons le fil de notre conversation : \"¿ Cómo estás ?\" me demandait le garçon de café auquel j\'avais répondu sans faire attention d\'un >>Ich weiß es nicht<< maladroit.

Will be transformed to this:

> Un jour — ou plutôt une nuit —, je crois que cela se déroulait pendant ma lecture de « À la recherche du temps perdu », il me semble… Mais je m’égare alors reprenons le fil de notre conversation : “¿ Cómo estás ?” me demandait le garçon de café auquel j’avais répondu sans faire attention d’un »Ich weiß es nicht« maladroit.
