<?php
/**
 * tag_scripture_pass.php: Modification of tag_scripture_time.pass that passes actual
 *   regex processing and replacement out to a Perl process
 *   (swp_regex_cli.pl).
 */
 
require_once('biblebooks.php');

header("Content-type: text/html; charset=utf-8");
$localfile = "peterpope.html";
$text = file_get_contents($localfile);
$scriptureMarkup = new ScriptureMarkup($text);
$times = array();
     
$t = 100;
for ($i = 0; $i < $t; ++$i) {
	$starttime = microtime(true);
	$scriptureMarkup->pass_text($text);
	$endtime = microtime(true);
	$times[] = $endtime - $starttime;
	#echo $scriptureMarkup->get_text();
}

$average = array_sum($times) / count($times);

echo "Average time: $average seconds";


class ScriptureMarkup {
    private $last_book;
    private $last_chapter;
    private $text;
    private $debug = false;
    private $booknames;
    
    public function __construct($text) {
        global $BIBLEBOOKS;
        
        $this->last_book = '';
        $this->last_chapter = 0;
        
        /* If it hasn't been already, prepare the table of books. */
        if (! isset($BIBLEBOOKS['BOOKS'])) {
            BibleBooks::prepare_book_maps();
        }
        $bookrefs = array_keys($BIBLEBOOKS['BOOKS']);
        sort($bookrefs);
        $this->booknames = implode('|', $bookrefs);
        #echo $this->booknames;
    }
    
    /**
     * Return the marked-up text.
     */
    public function get_text() {
        return $this->text;
    }
    
    public function pass_text($text = null) {
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w")
        );
        $cmd = "perl swp_regex_cli.pl";
        $cwd = "/var/www/wordpress/wp-content/plugins/sacred-wordpress/";
        $env = array();
        
        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
        
        fwrite($pipes[0], $text);
        fclose($pipes[0]);
        $intext = stream_get_contents($pipes[1]);
        #echo $intext, "\n";
        $return_value = proc_close($process);
        echo "Command returned $return_value.\n";
        return $intext;
    }

    /**
     * Tag the Scripture references in the text (either provided in
     * constructor or passed as parameter here.
     * 
     * @param $text   Optional passing of text to tag.
     */
    public function tag_text($text = null) {
        if (! $text) {
            $text = $this->text;
        }

        $booknames = $this->booknames;
        $count = 0;
    
        if ($this->debug) {
            echo "<!-- function mark_text() -->\n";
        }
    
        /* This should match:
         *       "1 Corinthians 13:1"
         *       "1 Corinthians 13:1-3"
         *       "1 Corinthians 13:1,3"
         *       "1 Corinthians 13"
         * If a chapter was previously referred to, will match:
         *       v. 1 OR vv. 1-3
         * Should match in the same citation:    1 Corinthians 13:1,3
         * Should match in DIFFERENT citations:  1 Corinthians 13:1, 15:2 */

        $text = preg_replace_callback(
          "/\b    # Match a word boundary --
                  #   shouldn't be a part of anything else
           (?:
               (  # First capture group: The name of the book
                    # The numeric prefix, e.g. *1* Corinthians
                    (?:(?:[1-4]|(?:I|II|III|IV)|(?:1st|2nd|3rd|4th)
                        |(?:First|Second|Third|Fourth))\b\ )?

                    # The first word of the title, first letter capital
                    (?:[A-Z][a-z]+\b\.?)
                    # Additional words of the title (up to two more)
                    (?:\ (?:[oO]f(?:\ [tT]he)?|[A-Z][a-z]+)\b\.?){0,2}
                        #    Must begin in capital or be the word 'of'
               )
               \s?  # A space between the book name and the chapter-verse
               (  # Second capture group: The chapter-verse
                 (?:
                   (?: # A chapter-verse reference or range
                      # A basic chapter-verse reference, e.g. 3:16
                      \d+[:.]\d+
                        # With a possible range of verses (e.g. 3:16-19)
                        (?:[-\x{2013}]   # \x{2013} = EN DASH
                          # End verse of a range,
                          #   possibly in another chapter
                          (?:\d+[:.])?\d+)?
                   |
                      # Or can be a whole chapter
                      #   (or a verse in single-chapter book)
                      \d+
                        # Or a possible range of chapters
                        (?:[-\x{2013}]\d+)?
                   )
                   (?:,\ *   # A comma or other separator between refs
                     |\ and\   # Allow for cases e.g. 'John 6:37 and 10:27–30x'
                     |\ or\
                   )?
                   # But don't match the next one if it appears to be
                   #   another book.
                   (?!\ (?:[1-4]\ )?[A-Z][a-z]+)
                 )+  # But if possible, match a whole string of refs
               )
           )
           \b
           (?![^<]+>)    # This should make sure we weren't in the middle
                         # of an HTML tag and accidentally match things
                         # like Web and IP addresses
           /xu",
           array($this, 'mark_scripture'), $text, -1, $count);

        #if ($this->debug) {
            echo "<!-- preg_replace_callback() matched $count times. -->\n";
        #}
        return $text;
    }

    /**
     * The callback method for preg_replace_callback() in tag_text().
     * This cuts a string of apparent Scripture references into pieces,
     * determines if they are actually Scripture references after all,
     * and if they are, tags them appropriately and returns the tagged
     * string to be replaced in the text. 
     * 
     * @param $matches   The matches passed by preg_replace_callback().
     */
    private function mark_scripture($matches) {
        if ($this->debug) {
            print "<!-- Matched [0] '" . $matches[0] . "'";
            if (isset ($matches[1])) { print ", [1] '" . $matches[1] . "'"; }
            if (isset ($matches[2])) { print ", [2] '" . $matches[2] . "'"; }
            if (isset ($matches[3])) { print ", [3] '" . $matches[3] . "'"; }
            print " -->\n";
        }
        
        if (isset ($matches[3])) {
            # Then it's something we recognized as a Scripture reference
            #   without a book or chapter (e.g. "v." or "vv.")
            $book = '';
            $verses = array($matches[3]);
        } else {
            # We presume $matches[1] and $matches[2] matched: a book
            #   reference followed by a string of verse references
            
            $book = $matches[1];
            # Split comma-separated references into array
            $versestr = preg_replace('/ and | or /', ', ', $matches[2]);
                # 'and' or 'or' in the string is really a list separator
                #   e.g. John 6:37–40 and 10:27–30
            $verses = preg_split('/(?:,\ *)(?=\d+[.:]|$)/', $versestr);
                # Look-ahead to be sure we don't split at compound verse
                #   references, e.g. John 3:3,5.
                # Only split at full chapter:verse references,
                #   e.g. John 3:3, 6:35
            if ($this->debug) {
                echo "<!--"; var_dump($verses); echo "-->\n";
            }
        }
        
        if (! $book) {
            $book = '';
        }
        $return_string = $matches[0]; # The whole matched string
        $i = -1;
        foreach ($verses as $verse) {
            $matched_verse = $verse;
            $i++; # So will be 0 on first iteration (index of $verses)
            if ($this->debug) {
                print "<!-- verse match [$i]: '$verse' -->\n";
            }
            $flags = array();
            if ($this->debug) {
                print "<!-- Trying '$book', '$verse' -->\n";
            }
            if (! $book) {
                # If we didn't match a book name,
                #   then assume the last named reference
                if (! $this->last_book) { goto FAIL; }
                if ($this->debug) {
                    print "<!-- No \$1 match, trying '" . $this->last_book . "' -->\n";
                }
                $book = $this->last_book;
                $flags[] = "book_inferred";

                if ($this->is_chapterless($book, $verse)) { # e.g. v. 5
                    if ($this->debug) {
                        print "<!-- Chapterless reference. Is chapter '" . $this->last_chapter . "'? -->\n";
                    }
                    if (! $this->last_chapter) { goto FAIL; }
                    $verse = $this->last_chapter . ":$verse";
                    $flags[] = "chapter_inferred";
                }
            } # If no $last_book set, i.e. if this is first possible
              #   reference, but there is o book name, this will fail
            $ref = $this->is_it_scripture($book, $verse, $flags);
            if ($ref) {
                $flagstr = implode(" ", $flags);
                if ($matches[1]) { $this->last_book = $book; }
                if ($i == 0) { # The first one, with the book reference
                    $replace = "<span class=\"scriptureRef\"" . 
                        ($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
                        " ref=\"$ref\">" .
                        ($matches[1] ? $matches[1] . " " : "") .
                            $matched_verse . "</span>";
                    $return_string = str_replace(
                        # Also match the book reference here if it exists
                        ($matches[1] ? $matches[1] . " " : "") . $matched_verse,
                        $replace, $return_string);
                } else { # Just the verse reference
                    $replace = "<span class=\"scriptureRef\"" . 
                        ($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
                        " ref=\"$ref\">" . $matched_verse . "</span>";
                    $return_string = str_replace($matched_verse, $replace, $return_string);
                }
                if ($this->debug) {
                    print "<!-- \$replace = '$replace' -->\n";
                }
            } else { # It's probably not a Scripture reference after all
                FAIL:
                # Don't replace anything; leave it as it is
            }
        }
        if ($this->debug) {
            print "<!-- \$return_string = $return_string -->\n\n";
        }
        return $return_string;
    }
    
    /**
     * Passed an apparent Scripture reference, determines if it is
     * actually Scripture after all.
     *
     * @param $book    The book string matched in the regexp.
     * @param $verse   The chapter-verse string.
     * @param &$flags  Flags about the string we are passing around.
     */
    private function is_it_scripture($book, $verse, &$flags) {
        global $BIBLEBOOKS;
        if ($this->debug) {
            print "<!-- is_it_scripture('$book', '$verse')? -->\n";
        }
        if (! $book) {
            # If there's no book, not even an inferred one, then we
            #   obviously have nothing to do here.
            return 0;
        }
        # The cases when people spell out "First Corinthians" or use
        #   Roman or ordinal numerals are few and far between, but it's
        #   worthwhile to catch them.
        $book = preg_replace('/^(I|1st|First)\b/',   '1', $book);
        $book = preg_replace('/^(II|2nd|Second)\b/', '2', $book);
        $book = preg_replace('/^(II|3rd|Third)\b/',  '3', $book);
        $book = preg_replace('/^(II|4th|Fourth)\b/', '4', $book);
        $book = preg_replace('/\./', '', $book);
        if ($this->debug) {
            print "<!-- Found ref: $book $verse = ";
        }
        
        # Whether the matched book string is '1 Cor' or '1Co' or
        #   '1 Corinthians', it ought to match in the [BOOKS] table.
        if (array_key_exists($book, $BIBLEBOOKS['BOOKS'])) {
            $book = $BIBLEBOOKS['BOOKS'][$book];
            if (array_key_exists($book, $BIBLEBOOKS['DEUTERO'])) {
                $flags[] = 'deutero';
            }
            if ($this->is_single_chapter($book)) {
                # If the book is a single chapter (e.g. 1 John), at
                #   least the Bibles.org API likes to explicitly
                #   identify it with a chapter reference.
                $verse = "1:$verse";
                $flags[] = 'short';
                $flags[] = 'chapter_inferred';
            }
            $verse = preg_replace('/\x{2013}/u', '-', $verse);
                # EN DASH to hyphen: Makes it easier to deal with below.
            $verse = preg_replace('/\./', ':', $verse);
            if (! preg_match('/[-,]/', $verse)) {
                if (! preg_match('/:/', $verse) and
                    ! in_array('book_inferred', $flags)) {
                    # e.g. 1 Corinthians 13, but not just the numeral 13
                    #   detached from any other context.
                    $flags[] = 'whole_chapter';
                } else { # e.g. 1 Corinthians 13:1
                    $flags[] = 'single_verse';
                }
            }
            if (preg_match('/-/', $verse)) {
                $flags[] = 'range';
            }
            if (preg_match('/,/', $verse)) {
                # A case like John 3:3,5, which we will treat
                #   as a single reference (even if the API doesn't).
                $flags[] = 'compound';
            }
            # Save the chapter we matched this time, in case the next
            #   reference is a verse in the same inferred chapter. 
            $this->last_chapter = $this->last_chapter($book, $verse);
            $ref = "$book $verse";
            if ($this->debug) {
                print "'$ref' -->\n";
            }
            return $ref;
        } else {
            # It wasn't Scripture after all.
            if ($this->debug) {
                print "NOT SCRIPTURE -->\n";
            }
            return 0;
        }
    }
    
    /**
     * Is this a book composed of a single chapter? (i.e. Jude, 2 John)
     * 
     * @param $book  The name of a Bible book.
     */
    private function is_single_chapter($book) {
        global $BIBLEBOOKS;
        $book = $BIBLEBOOKS['BOOKS'][$book]; # The canonical book name
        if ($BIBLEBOOKS['CHAPTERS'][$book] == 1) {
            return 1;
        }
    }

    /**
     * Given a book and verse reference, is this a reference to verses
     * without a chapter reference? (e.g. 9-10, not 5:9-10)
     * (We already know that there was no actual book reference,
     *  only an inferred book -- since $matches[1] did not match above).
     * 
     * @param $book   The inferred Bible book.
     * @param $verse  The chapter-verse string
     */
    private function is_chapterless($book, $verse) {
        if ($this->is_single_chapter($book)) {
            return 0; # Because Chapter 1 will be inferred later
        }
        if (preg_match('/[:.]/', $verse, $matches)) {
            return 0; # Then an explicit chapter is given here, e.g. 13:10-15
        }
        # Otherwise -- something like 15 or 10-15.
        return 1;
    }

    /**
     * Identify the chapter element of the reference
     *   for use in forward inferences.
     * (This doesn't actually return the last chapter, but sets the
     *   $last_chapter for use in the future.)
     * 
     * @param $book  The book string.
     * @param $verse The chapter-verse string.
     */
    function last_chapter($book, $verse) {
        # 
        global $BIBLEBOOKS;
        # Get the last chapter referred to
        $book = $BIBLEBOOKS['BOOKS'][$book];
        if ($BIBLEBOOKS['CHAPTERS'][$book] == 1) {
            # Only one chapter in this book, so no chapter reference.
            # We can quit already.
            return 1;
        }
        # This regexp takes a string like 9:10-9:15, 9:10-15, etc.
        #   and picks out the chapter part.
        if (preg_match('/(\d+)([:.]\d+)?(?:[-\x{2013}](\d+)([:.]\d+))?/u',
          $verse, $matches)) {
            $begin_chapter = '';
            $begin_verse = '';
            $end_chapverse = '';
            $end_verse = '';
            if (array_key_exists(1, $matches)) {
                $begin_chapter = $matches[1];
            }
            if (array_key_exists(2, $matches)) {
                $begin_verse = $matches[2];
            }
            if (array_key_exists(3, $matches)) {
                $end_chapverse = $matches[3];
            } 
            if (array_key_exists(4, $matches)) {
                $end_verse = $matches[4];
            }
            if (! $end_chapverse or ! $end_verse) {
                # Either 3:3 OR 3:3-5
                return $begin_chapter;
            } else { # 3:3-4:1
                return $end_chapverse;
            }
        }
    }
}
