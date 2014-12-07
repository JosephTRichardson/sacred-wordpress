# Sacred WordPress: Regex Tests and Optimizations

This directory contains several revisions to the Scripture-tagging regex
and replacement routines, developed in the course of timing and 
optimizing the regex. See my blog post for a detailed description:

[Regular Expression for Scripture References, Part II: Mechanics and Optimization]
(http://jtrichardson.com/code/regular-expression-scripture-references-part-ii-mechanics-and-optimization)

The files:

File                 | Description
---------------------| ------------------------------------------------------------------------
 `BibleBooks.pm`     | A port to Perl of `biblebooks.php`, containing data on Bible book titles.
 `peterpope.html`    | Large sample blog text for testing.
 `raw_matches.pl`    | A list of Scripture matches from `peterpope.html` for use in testing replacement routines apart from matching.
 `swp_regex.pl`      | Regex and replacement routines at beginning of optimization.
 `swp_regex_cli.pl`  | Command-line interface to matching and replacement routines, for use in testing as pipe.
 `swp_regex_match_only.pl` | The matching regex only, for clocking without replacement.
 `swp_regex_replace_only.pl` | Replacement routines only, for clocking without regex.
 `swp_regex_rev1.pl` | First revision: Factoring out redundant and buggy alternate case.
 `swp_regex_rev2.pl` | Second revision: Compiling array of book names and abbreviations into regex.
 `swp_regex_rev3.pl` | Third revision: Cleaning up a messy segment in the book name match.
 `tag_scripture_time.php` | Modification of `tag_scripture.php` for testing and timing regex under PHP.
 `tag_scripture_pass.php` | Modification of `tag_scripture_time.php` for passing Scripture matching and replacement out to a Perl process via pipe.

Copyright 2014, Joseph T. Richardson (LonelyPilgrim @ GitHub).
All code is released freely under the [MIT License] (http://opensource.org/licenses/MIT).
