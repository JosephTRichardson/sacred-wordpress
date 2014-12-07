#!/usr/bin/perl

# Runs the swp_regex as a match only for clocking purposes,
# removing the tag_scripture routine.

use v5.18;
use strict;
use warnings 'all';
#use re 'debug';
use Data::Dumper;
use BibleBooks qw(%BIBLEBOOKS);
use Time::HiRes qw(time);

my $debug = 0;
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
	
	my @matches;
	
	while ($text =~ m/\b    # Match a word boundary
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
		(?![^<]+>)/gsx) {
		$count++;
		push @matches, [$&, $1, $2, $3];
	}

	open ($fh, '>', 'raw_matches.pl') or die "Couldn't open file for write: $!";
	print $fh Dumper(\@matches);
	close $fh;

	#$count = $text =~ s/\b
	#		(                       # An alternate case: no book name present
	#		  (?<=v\.\s)            # Look-behind to v. (verse) or vv. (verses)
	#			\d+                   # A verse number
	#			  (?:[-\N{EN DASH}]   # Or possible range of verses
	#				\d+
	#			  )?
	#			  (?:,\s?\d+         # Or single verses separated by commas
	#				(?!\s+[A-Z][a-z]+)  # But not another book name
	#			  )*
	#		)
	#		\b/mark_scripture([$&, undef, undef, $1])/gesx;

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
	my @matches;

	while ($text =~ m/\b    # Match a word boundary
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
		(?![^<]+>)/gsx) {
		$count++;
	}
		
	print "Match matched $count times.\n";
	return $text; #) {
}
