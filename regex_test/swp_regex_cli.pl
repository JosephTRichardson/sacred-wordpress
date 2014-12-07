#!/usr/bin/perl

# Command-line interface to swp_regex/tag_scripture routines.
# Accepts text as STDIN and returns as STDOUT.
# For use in passing text out from PHP as pipe.

use v5.18;
use strict;
use warnings 'all';
use BibleBooks qw(%BIBLEBOOKS);

my $debug = 0;

my ($last_book, $last_chapter);

$/ = undef;
my $text = <STDIN>;
$text = tag_text($text);
print $text;

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
			  (?:[A-Z][a-z]+\b\.?)  # The first (capital) word of the title
			  # Additional words of the title
			  (?:\s[oO]f(?:\s[tT]he)?\s[A-Z][a-z]+\.?)?
			    # Allow for cases of "Song of Solomon" or
			    #   "Acts of the Apostles"
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
		  )
		\b
		(?![^<]+>)/mark_scripture([$&, $1, $2])/gesx;
		
	return $text;
}

sub mark_scripture {
	my $matches = $_[0];
	#print Dumper($matches);
	my ($book, @verses, %flags);
	if ($debug) {
		print "<!-- Matched [0] '" . $matches->[0] . "'";
		if (defined ($matches->[1])) { print ", [1] '" . $matches->[1] . "'"; }
		if (defined ($matches->[2])) { print ", [2] '" . $matches->[2] . "'"; }
		print " -->\n";
	}
	
	$book = $matches->[1];
	my $versestr = $matches->[2] =~ s/ and | or /, /r;
	@verses = split('/(?:,\s*)(?=\d+[.:]|$)/', $versestr);
	if ($debug) {
		print "<!-- @verses -->\n";
	}
	
	if (! $book) {
		$book = '';
	}
	my $return_string = $matches->[0]; # The whole matched string
	my $i = -1;
	foreach my $verse (@verses) {
		my $matched_verse = $verse;
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
			$flags{'book_inferred'} = 1;

			if (is_chapterless($book, $verse)) { # e.g. v. 5
				if ($debug) {
					print "<!-- Chapterless reference. Is chapter '$last_chapter'? -->\n";
				}
				if (! $last_chapter) { goto FAIL; }
				$verse = $last_chapter . ":$verse";
				$flags{'chapter_inferred'} = 1;
			}
		}
		my $ref = is_it_scripture($book, $verse, \%flags);
		if ($ref) {
			my $flagstr = join(" ", keys %flags);
			my $replace;
			if ($matches->[1]) { $last_book = $book; }
			if ($i == 0) {
				$replace = "<span class=\"scriptureRef\"" . 
					($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
					" ref=\"$ref\">" .
					($matches->[1] ? $matches->[1] . " " : "") .
						$matched_verse . "</span>";
				my $search = ($matches->[1] ? $matches->[1] . " " : "") . $matched_verse;
				$return_string =~ s/$search/$replace/;
				
			} else {
				$replace = "<span class=\"scriptureRef\"" . 
					($flagstr ? " data-scriptureref-flags=\"$flagstr\"" : "") .
					" ref=\"$ref\">" . $matched_verse . "</span>";
				$return_string =~ s/$matched_verse/$replace/;
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
			$flags->{'deutero'} = 1;
		}
		if (is_single_chapter($book)) {
			$verse = "1:$verse";
			$flags->{'short'} = 1;
			$flags->{'chapter_inferred'} = 1;
		}
		$verse =~ s/\N{EN DASH}/-/g;
		$verse =~ s/\./:/g;
		if (! $verse =~ /[-,]/) {
			if (! $verse =~ m/:/ and
				! $flags->{'book_inferred'}) {
				$flags->{'whole_chapter'} = 1;
			} else { # e.g. 1 Corinthians 13:1
				$flags->{'single_verse'} = 1;
			}
		}
		if ($verse =~ /-/) {
			$flags->{'range'} = 1;
		}
		if ($verse =~ /,/) {
			$flags->{'compound'} = 1;
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
