# Sacred WordPress
### A Scripture reference and tooltip plugin for WordPress.
##### http://jtrichardson.com/projects/sacred-wordpress
##### Version 0.10.1
##### Copyright (c) 2014, Joseph T. Richardson <joseph.t.richardson@gmail.com>

## Overview

Sacred WordPress is a plugin for WordPress that searches the text of
entries and comments for Scripture references (e.g. John 3:16), tags
them with appropriate HTML markup, and on page load, retrieves the
Scripture text of each reference from a Bible API and displays it in
a tooltip when the user hovers over the reference.

It will recognize references in a number of different formats:

Format | Description
-----------------------|------------------------------------------
John 6:53              | A simple, fully spelled-out Scripture reference
1 Corinthians 13:1     | A book with a numeric qualifier
First Corinthians 13:1 | A book with a numeric qualifier spelled out
1 Pet 3:21             | An abbreviated book name
Gen. 1:1               | Abbreviated with a period
Titus 1.3              | Using a period instead of a colon to identify chapter and verse
Romans 5:9-12          | A range of verses within a chapter
Romans 5:9–10          | A range using an en dash instead of a hyphen, as per proper style
Revelation 11:19–12:6  | A range of verses across multiple chapters
John 3:3,5             | A set of non-consecutive verses within a chapter
Jude 3                 | A verse reference within a single-chapter book
Sirach 3:30            | A deuterocanonical reference
v. 10 or vv. 9–13      | Verse references separated from their book name; presumes the last referenced book and chapter

For a demonstration, please see my own blog: [The Lonely Pilgrim] (http://lonelypilgrim.com/)

This plugin is intentionally ecumenical, designed to be useful to
Christians of all sects and stripes. It supports the full range of
Scriptures, including the deuterocanon (known to Protestants as the
"apocrypha"). I've made every effort to include every common name and
abbreviation that the books of the Bible are known by in English.
If you know of anything I can or should add, please let me know.
I plan in the near future to add international support.

If you find Sacred WordPress useful, [please let me know] (mailto:joseph.t.richardson@gmail.com)!

## Requirements

Sacred WordPress ought to work with any recent version of WordPress (v2.2+),
and requires jQuery 1.7+ (already built into WordPress) and
Tooltipster 3.0+ (packaged with Sacred WordPress). So all you really
need is a working WordPress installation.

[That is, an installation of the WordPress.org blog platform, hosted on
 your own server or shared hosting account. Sorry, but this doesn't
 work with blogs hosted on WordPress.com.]
  
You also need, as of the current version, a free API key to the
Bibles.org (American Bible Society Bible Search) API. I am planning, for
the first official release, support for other free APIs that don't
require getting an API key. But [getting a Bibles.org API key] (http://bibles.org/pages/api/signup) 
is free and easy.

## Installation

To install Sacred WordPress, just drop it in your WordPress's
`/wp-content/plugins` directory, and enable it on the plugins page
of your Dashboard (`/wp-admin/plugins.php`).

If you're using the Bibles.org API, you also need to edit
`bibles-org-request.php` in this package and put your own API key
into the variable `$APIKEY`.

## Configuration

I plan to give you a lot more options to configure, but as of the
current version, the most useful option is setting what Bible version
the tooltip will use. To do that, edit scripture.php in this package,
and in the `$config` array, change the values for `standard_version`
(for any Scripture reference that is not to the deuterocanonical books,
called by Protestants the "apocrypha") and `deutero_version` (for
deuterocanonical references). `standard_version` defaults to the ESV
(English Standard Version) and `deutero_version` defaults to KJVA
(King James Version with Apocrypha). Currently, the Bibles.org API
only supports a few English versions:

Code      | Version
----------|-------------
eng-AMP   | Amplified Bible
eng-CEV   | Contemporary English Version
eng-CEVD  | Contemporary English Version (with deuterocanon)
eng-CEVUK | Contemporary English Version (Anglicised)
eng-ESV   | English Standard Version
eng-GNTD  | Good News Translation (with deuterocanon) (formerly known as Today's English Version)
eng-GNBDC | Good News Bible (with deuterocanon) (only apparent difference from GNTD is Anglicisation)
eng-KJV   | King James Version
eng-KJVA  | King James Version with Apocrypha
eng-MSG   | The Message
eng-NASB  | New American Standard Bible

In `scriptureTooltip.js`, there are a couple of options you can set
(there will be more):

Option                        | Description
------------------------------|-----------------
`defaultScriptureSite`        | Sets the Bible website the Scripture header in the tooltip will link to. Defaults to BibleGateway.com. Not many more sites supported as yet, but it should be trivial to code support for another site.
`showCopyrightDataInTooltip`  | If enabled, will place a 'Copyright Information' link in each Scripture toolip, giving basic copyright information on hover. I found it more aesthetic to disable this and include the copyright information in my blog footer.

## Caveats

At present, the plugin can be quite slow if called on a long page full
of full-text blog entries. On my blog, the front page can contain as
many as 90+ Scripture references, and it can take as long as 30 seconds
to load the Ajax response. I plan to implement some response caching to
improve on this problem. The response is much quicker when called on
individual entries.

## To Do

Some things I'd like to accomplish in future versions of Sacred WordPress:

* General:
    * Implement a mechanism for easy configuration.
    * Internationalization: biblebooks.php for other-language users.
        (Spanish, Italian, French, Portuguese, German, Dutch,
        Russian, Greek, Turkish, Indonesian --
        probably in that order. Any others by request.)

* In `scriptureTooltip.js` (the Ajax-requesting tooltip plugin):
    * Adapt for optional use with jQuery UI tooltips.
    * Implement link menu to various Bible sites in tooltips --
        will probably be implemented as a secondary popup.
    * Allow an option to request Scripture texts separately on demand
        rather than all as a lump at page load.

* In `scripture.php`, etc. (the Ajax-responding, API-calling back end):
    * Implement at least two other APIs:
        * ESV.org
        * Biblia.org
        * (I am also planning my own API for Douay-Rheims requests.)
    * Implement caching of Scripture text received from APIs.
    * Adapt for optional use without Ajax --
        i.e. make calls for Scripture text during WordPress page load
        and include static Scripture text hidden in WordPress output.
        Would be most useful in combination with response caching.

* In `tag_scripture.php` (the Scripture-tagging WordPress back end):
    * Have it pass over references that are already tagged 'scriptureRef'
        i.e. so we can manually tag problematic references without risk
        of them being double-tagged (in which case we would get
        double tooltips).
    * At present there is no conflict with tagging references that are
        already linked (i.e. `<a href="">..</a>`), since tag_scripture.php
        applies `<span>` tags and only styles them as links.
        (This allows mobile users to click them to get tooltips without
        actually following a link.) We can possibly refine this behavior.

## License

Sacred WordPress is released under the [MIT License] (http://opensource.org/licenses/MIT).
This means you are free to use, copy, edit, modify, reuse, redistribute,
incorporate all or part into your own project, or do pretty much anything
else you'd like with this code and software, provided you keep intact the
attribution to me and this license information.

## References

* [WordPress] (http://wordpress.org/)
* [WordPress Codex] (http://codex.wordpress.org/)
* [WordPress Code Reference] (https://developer.wordpress.org/reference/)
* [PHP Manual] (http://php.net/manual/en/)
* [jQuery] (http://www.jquery.com/)
* [jQuery API Documentation] (http://api.jquery.com/)
* [jQuery Learning Center] (http://learn.jquery.com/)
* [Tooltipster] (http://iamceege.github.io/tooltipster/)
* [Bibles.org API] (http://bibles.org/pages/api/)
