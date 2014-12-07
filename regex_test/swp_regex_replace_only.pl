#!/usr/bin/perl

# Runs the tag_scripture replacement routine only, with a static list
# of predetermined matches (raw_matches.pl), for use in clocking.

use v5.18;
use strict;
use warnings 'all';
#use re 'debug';
use Data::Dumper;
use BibleBooks qw(%BIBLEBOOKS);
use Time::HiRes qw(time);

my $debug = 0;
my $use_pcre = 0;
my $get_average_time = 1;
my $infile = 'peterpope.html';

my ($last_book, $last_chapter);

BibleBooks::prepare_book_maps();

open (my $fh, '<', $infile) or die "Couldn't open file: $!";
local $/ = undef;
my $content = <$fh>;
close $fh;

our $matches;
require "raw_matches.pl";
my @times;

my $loop = sub {
	my $starttime = time;

	foreach my $match (@$matches) {
		mark_scripture($match);
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


sub mark_scripture {
	no warnings 'deprecated';
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
			print "<!--"; Dumper(@verses); print "-->\n";
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
		if (exists $BIBLEBOOKS{'BOOKS'}->{$book}) {
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
