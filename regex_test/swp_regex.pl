#!/usr/bin/perl

# swp_regex.pl: Scripture regex before optimization.
# See http://jtrichardson.com/code/regular-expression-scripture-references-part-ii-mechanics-and-optimization

use v5.18;
use strict;
use warnings 'all';
use Data::Dumper;
use BibleBooks qw(%BIBLEBOOKS);
use Time::HiRes qw(time);

my $debug = 1;
my $use_pcre = 0;
my $get_average_time = 0;
my $infile = 'peterpope.html';

my ($last_book, $last_chapter);

open (my $fh, '<', $infile) or die "Couldn't open file: $!";
local $/ = undef;
my $content = <$fh>;
close $fh;

my @times;

my $loop = sub {
    my $starttime = time;

    if ($use_pcre) {
        tag_text_pcre($content);
    } else {
        tag_text($content);
    }

    my $endtime = time;
    my $timediff = $endtime - $starttime;
    printf("%0.6f seconds.\n", $timediff);
    push (@times, $timediff);
};

if ($get_average_time) {
    for (1..100) {
        &$loop();
    }

    my $total = 0;
    foreach (@times) {
        $total += $_;
    }
    my $avg = $total / @times;
    printf "Average time: %0.5f\n", $avg;
} else {
    &$loop();
}



sub tag_text {
    my $text = shift;
    
    if (! defined($BIBLEBOOKS{'BOOKS'})) {
        BibleBooks::prepare_book_maps();
    }

    my $count = 0;

    if ($debug) {
        print "<!-- function mark_text() -->\n";
    }

    $count = $text =~ s/\b    # Match a word boundary
          (?:
            (     # First capture group: The name of the book
            (?:(?:[1-4]  # The numeric prefix, e.g. *1* Corinthians
              |(?:I|II|III|IV)|(?:1st|2nd|3rd|4th)
              |(?:First|Second|Third|Fourth))\b\s)?  # Match this, or not
            (?:[A-Z][a-z]+\b\.?)  # The first (capital) letter of the title
              # Additional words of the title (up to two more)
              (?:\s(?:[oO]f(?:\s[tT]he)?
                # Allow for cases of "Song of Solomon" or
                #   "Acts of the Apostles"
              |[A-Z][a-z]+)\b\.?){0,2}
            )
            \s? # Space between book name and verse reference
            (     # Second capture group: Verse references
              (?:
                (?:
                  \d+[:.]\d+             # Basic Scripture reference, e.g. 3:16
                    (?:[-\N{EN DASH}]    # Or a possible range of verses (e.g. 3:16-19)
                      (?:\d+[:.])?\d+)?  # End verse of range, possibly in 
                                         #   another chapter
                |
                  \d+                    # Can be a chapter (or verse in
                                         #   single-chapter book)
                    (?:[-\N{EN DASH}]
                       \d+)?             # Or possible range of chapters
                )
                (?:,\s*                  # A comma or other separator between refs
                  |\sand\s               #   Allow for cases e.g. 'John 6:37 and 10:27–30x'
                  |\sor\s
                )?
                (?!\s(?:[1-4]\s)?        # Keep matching a string of references  
                    [A-Z][a-z]+          #   until we hit another book name
                )
              )+
            )  # Capture the whole string of references after a book name
          |
            (                       # An alternate case: no book name present
              (?<=v\.\s)            # Look-behind to v. (verse) or vv. (verses)
                \d+                   # A verse number
                  (?:[-\N{EN DASH}]   # Or possible range of verses
                    \d+
                  )?
                  (?:,\s?\d+         # Or single verses separated by commas
                    (?!\s+[A-Z][a-z]+)  # But not another book name
                  )*
            )
          )
        \b
        (?![^<]+>)/mark_scripture([$&, $1, $2, $3])/gesx;

    if ($count) {
        print "Substitution matched $count times.\n";
    } else {
        print "Subsstitution failed to match at all!\n";
    }
    return $text;
}

sub tag_text_pcre {
    use re::engine::PCRE;
    
    my $text = shift;
    
    if (! defined($BIBLEBOOKS{'BOOKS'})) {
        BibleBooks::prepare_book_maps();
    }

    my $count = 0;

    if ($debug) {
        print "<!-- function mark_text() -->\n";
    }

    $count = $text =~ s/\b    # Match a word boundary
          (?:
            (     # First capture group: The name of the book
            (?:(?:[1-4]  # The numeric prefix, e.g. *1* Corinthians
              |(?:I|II|III|IV)|(?:1st|2nd|3rd|4th)
              |(?:First|Second|Third|Fourth))\b\s)?  # Match this, or not
            (?:[A-Z][a-z]+\b\.?)  # The first (capital) letter of the title
              # Additional words of the title (up to two more)
              (?:\s(?:[oO]f(?:\s[tT]he)?
                # Allow for cases of "Song of Solomon" or
                #   "Acts of the Apostles"
              |[A-Z][a-z]+)\b\.?){0,2}
            )
            \s? # Space between book name and verse reference
            (     # Second capture group: Verse references
              (?:
                (?:
                  \d+[:.]\d+             # Basic Scripture reference, e.g. 3:16
                    (?:[-\X{2013}]    # Or a possible range of verses (e.g. 3:16-19)
                      (?:\d+[:.])?\d+)?  # End verse of range, possibly in 
                                         #   another chapter
                |
                  \d+                    # Can be a chapter (or verse in
                                         #   single-chapter book)
                    (?:[-\X{2013}]
                       \d+)?             # Or possible range of chapters
                )
                (?:,\s*                  # A comma or other separator between refs
                  |\sand\s               #   Allow for cases e.g. 'John 6:37 and 10:27–30x'
                  |\sor\s
                )?
                (?!\s(?:[1-4]\s)?        # Keep matching a string of references  
                    [A-Z][a-z]+          #   until we hit another book name
                )
              )+
            )  # Capture the whole string of references after a book name
          |
            (                       # An alternate case: no book name present
              (?<=v\.\s)            # Look-behind to v. (verse) or vv. (verses)
                \d+                   # A verse number
                  (?:[-\X{2013}]   # Or possible range of verses
                    \d+
                  )?
                  (?:,\s?\d+         # Or single verses separated by commas
                    (?!\s+[A-Z][a-z]+)  # But not another book name
                  )*
            )
          )
        \b
        (?![^<]+>)/mark_scripture([$&, $1, $2, $3])/gesx;

    if ($count) {
        print "Substitution matched $count times.\n";
    } else {
        print "Subsstitution failed to match at all!\n";
    }
    return $text; #) {

}

sub mark_scripture {
    my $matches = $_[0];
    #print Dumper($matches);
    my ($book, @verses, @flags);
    if ($debug) {
        print "<!-- Matched [0] '" . $matches->[0] . "'";
        if (defined ($matches->[1])) { print ", [1] '" . $matches->[1] . "'"; }
        if (defined ($matches->[2])) { print ", [2] '" . $matches->[2] . "'"; }
        if (defined ($matches->[3])) { print ", [3] '" . $matches->[3] . "'"; }
        print " -->\n";
    }
    
    if (defined ($matches->[3])) {
        $book = '';
        @verses = ($matches->[3]);
    } else {
        $book = $matches->[1];
        my $versestr = $matches->[2] =~ s/ and | or /, /r;
        @verses = split('/(?:,\s*)(?=\d+[.:]|$)/', $versestr);
        if ($debug) {
            print "<!-- @verses -->\n";
        }
    }
    
    if (! $book) {
        $book = '';
    }
    my $return_string = $matches->[0]; # The whole matched string
    my $i = -1;
    foreach my $verse (@verses) {
        $i++; # So will be 0 on first iteration (index of $verses)
        if ($debug) {
            print "<!-- verse match [$i]: '$verse' -->\n";
        }
        if ($debug) {
            print "<!-- Trying '$book', '$verse' -->\n";
        }
        if (! $book) {
            # If we didn't match a book name,
            #   then assume the last named reference
            if (! $last_book) { goto FAIL; }
            if ($debug) {
                print "<!-- No \$1 match, trying '$last_book' -->\n";
            }
            $book = $last_book;
            push (@flags, "book_inferred");

            if (is_chapterless($book, $verse)) { # e.g. v. 5
                if ($debug) {
                    print "<!-- Chapterless reference. Is chapter '$last_chapter'? -->\n";
                }
                if (! $last_chapter) { goto FAIL; }
                $verse = $last_chapter . ":$verse";
                push (@flags, "chapter_inferred");
            }
        }
        my $ref = is_it_scripture($book, $verse, \@flags);
        if ($ref) {
            my $flagstr = join(" ", @flags);
            my $replace;
            if ($matches->[1]) { $last_book = $book; }
            if ($i == 0) {
                $replace = "<span class=\"scriptureRef\"" . 
                    ($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
                    " ref=\"$ref\">" .
                    ($matches->[1] ? $matches->[1] . " " : "") .
                        $verse . "</span>";
                my $search = ($matches->[1] ? $matches->[1] . " " : "") . $verse;
                $return_string =~ s/$search/$replace/;
                
            } else {
                $replace = "<span class=\"scriptureRef\"" . 
                    ($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
                    " ref=\"$ref\">" . $verse . "</span>";
                $return_string =~ s/$verse/$replace/;
            }
            if ($debug) {
                print "<!-- \$replace = '$replace' -->\n";
            }
        } else {
            FAIL:
        }
    }
    if ($debug) {
        print "<!-- \$return_string = $return_string -->\n\n";
    }
    return $return_string;
}

sub is_it_scripture {
    my ($book, $verse, $flags) = @_;
    if ($debug) {
        print "<!-- is_it_scripture('$book', '$verse')? -->\n";
    }
    if (! $book) {
        return 0;
    }
    $book =~ s/^(I|1st|First)\b/1/;
    $book =~ s/^(II|2nd|Second)\b/2/;
    $book =~ s/^(II|3rd|Third)\b/3/;
    $book =~ s/^(II|4th|Fourth)\b/4/;
    $book =~ s/\.//s;
    if ($debug) {
        print "<!-- Found ref: $book $verse = ";
    }
    
    if (exists $BIBLEBOOKS{'BOOKS'}->{$book}) {
        $book = $BIBLEBOOKS{'BOOKS'}->{$book};
        if (exists $BIBLEBOOKS{'DEUTERO'}->{$book}) {
            push (@$flags, 'deutero');
        }
        if (is_single_chapter($book)) {
            $verse = "1:$verse";
            push (@$flags, 'short');
            push (@$flags, 'chapter_inferred');
        }
        $verse =~ s/\N{EN DASH}/-/g;
        $verse =~ s/\./:/g;
        if (! $verse =~ /[-,]/) {
            if (! $verse =~ m/:/ and
                ! grep('book_inferred', @$flags)) {
                push (@$flags, 'whole_chapter');
            } else { # e.g. 1 Corinthians 13:1
                push (@$flags, 'single_verse');
            }
        }
        if ($verse =~ /-/) {
            push (@$flags, 'range');
        }
        if ($verse =~ /,/) {
            push (@$flags, 'compound');
        }
        $last_chapter = last_chapter($book, $verse);
        my $ref = "$book $verse";
        if ($debug) {
            print "'$ref' -->\n";
        }
        return $ref;
    } else {
        # It wasn't Scripture after all.
        if ($debug) {
            print "NOT SCRIPTURE -->\n";
        }
        return 0;
    }
}

sub is_single_chapter {
    my $book = $_[0];
    $book = $BIBLEBOOKS{'BOOKS'}->{$book}; # The canonical book name
    if ($BIBLEBOOKS{'CHAPTERS'}->{$book} == 1) {
        return 1;
    }
}

sub is_chapterless {
    my ($book, $verse) = @_;
    if (is_single_chapter($book)) {
        return 0; # Because Chapter 1 will be inferred later
    }
    if ($verse =~ /[:.]/) {
        return 0; # Then an explicit chapter is given here, e.g. 13:10-15
    }
    return 1;
}

sub last_chapter {
    my ($book, $verse) = @_;
    $book = $BIBLEBOOKS{'BOOKS'}->{$book};
    if ($BIBLEBOOKS{'CHAPTERS'}->{$book} == 1) {
        return 1;
    }
    if ($verse =~ /(\d+)([:.]\d+)?(?:[-\N{EN DASH}](\d+)([:.]\d+))?/) {
        my $begin_chapter = $1;
        my $begin_verse = $2;
        my $end_chapverse = $3;
        my $end_verse = $4;
        if (! $end_chapverse or ! $end_verse) {
            return $begin_chapter;
        } else {
            return $end_chapverse;
        }
    }
}
