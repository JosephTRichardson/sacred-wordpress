/**
 * scriptureTooltip.js: Gathers Scripture references from page, requests
 *   from scripture.php, and generates tooltips (via jQuery + Tooltipster)
 *   of Scripture text.
 * 
 * @package Sacred Wordpress
 */
 
var scriptureTooltip = {
    pathToPlugin: '/wp-content/plugins/sacred-wordpress',
    debug: false, // Gives debugging output to console
    
    /**
     * Default Bible site for reference to link to (from list in getLink).
     * As it is set up, the Scripture reference in the tooltip is linked
     * (the reference in the blog text is only styled like a link).
     * @see getLink
     */
    defaultScriptureSite: 'BibleGateway',

    /**
     * URLs to copyright info for particular Bible versions we will use.
     * Will be linked in tooltip if showCopyrightDataInTooltip is true.
     */
    copyrightData: {
        ESV:  'http://www.crossway.org/rights-permissions/esv/',
        NASB: 'http://www.lockman.org/tlf/copyright.php'
    },
    showCopyrightDataInTooltip: false,

    /**
     * All the stuff to get it started.
     */
    init: function() {
        "use strict";
        // Gather list of the scriptures we need from the whole page
        var neededScriptures = this.getScriptureList();
        // Preload scriptures from webservice
        this.preloadScriptures(neededScriptures);
    
        /* Apply Tooltipster tooltips to all elements classed 'scriptureRef'
         * (which should be those tagged by tag_scripture.php)
         * For documentation of Tooltipster options, see
         * @link http://iamceege.github.io/tooltipster/
         * I intend to also implement option to use jQuery UI tooltips
         *   instead of Tooltipster if you wish.
         */
        /* Because WordPress loads jQuery in noConflict mode, we address
         * jQuery by its full name. */
        jQuery(".scriptureRef").tooltipster({
            /* You're welcome to display something more creative while loading.
             * In a long page of posts, it can take a few seconds to load. */
            content:      "Loading...",
            contentAsHTML: true,
            maxWidth:      300,
            theme:         'tooltipster-shadow', // You can customize the theme.
            interactive:   true, /* Allows a momentary delay for user to
                                  * mouseover tooltip, in which case it then stays. */
            multiple:      false
            //autoClose:     false  // Useful to enable this for debugging DOM and CSS
        });
    
        if (this.debug) {
            console.log("scriptureTooltip: All ready!");
        }
    },

    /**
     * Gather the list of Scripture references in whole page from all
     * elements classed 'scriptureRef' (applied by mark_scripture.php).
     * The returned array will be passed to scripture.php to request
     * Scripture text.
     */
    getScriptureList: function () {
        "use strict";
        if (this.debug) {
            console.log("Getting scripture list.\n");
        }
        var scriptureList = [];
        
        /* For each element classed 'scriptureRef', gather the reference
         * into the array. */
        jQuery(".scriptureRef[data-scriptureref]").each(function() {
            scriptureList.push(jQuery(this).attr("data-scriptureref"));
        });
        
        return scriptureList;
    },

    /**
     * Make a single Ajax request at page load to get text of all
     * Scripture references on page. This is slow up front but then allows
     * immediate popup of all tooltips.
     * Presumably someone actually reading will not jump immediately
     * to requesting the text of a Scripture reference.
     * @todo Allow an option to request texts separately on demand.
     * 
     * @param scriptureList    Array of Scripture references returned by getScriptureList().
     */
    preloadScriptures: function (scriptureList) {
        "use strict";
        
        /* We use semicolons to separate references here rather than commas
         * since we want to treat compound references like John 3:3,5
         * as a single reference. */
        var queryString = scriptureList.join(";");
        
        // Make the request.
        jQuery.ajax({
            url: scriptureTooltip.pathToPlugin +
                "/scripture.php" + "?ref=" + queryString,
            type: "GET",
            dataType: "JSON",
            success: function(json) {
                scriptureTooltip.setTooltips(json);
            },
            error: function(jq, textStatus, errorThrown) {
                if (scriptureTooltip.debug) {
                    console.log("scriptureTooltip: Ajax request failed!");
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            },
            complete: function(json) {
                if (scriptureTooltip.debug) {
                    console.log("scriptureTooltip: Ajax request completed!");
                }
            }
        });
    },

    /**
     * Set the content of the tooltips with the Scripture text received
     * from the Ajax request.
     * 
     * @param json             The JSON object we received from our request.
     */
    setTooltips: function (json) {
        "use strict";       
        
        jQuery(".scriptureRef[data-scriptureref]").each(function() {
            var ref, text, $html, url, version, $copyrightInfo;
            
            if (scriptureTooltip.debug) {
                console.log(this);
            }

            ref = jQuery(this).attr("data-scriptureref");
            url = scriptureTooltip.getDefaultLink(ref);
            
            if (scriptureTooltip.debug) {
                console.log("Getting ref: " + ref);
            }
            
            /* If everything worked the way it should have in scripture.php,
             * there ought to be a member matching 'ref' in the received
             * JSON object... */
            if (json.passages.hasOwnProperty(ref)) {
                text = json.passages[ref].text;

                /* Transmute hyphen to en dash for verse ranges, e.g. 1 Cor 9:10-12
                 * (I'm a stickler for proper style) */
                text = text.replace(/\b(\d+)-(\d+)\b/, "$1\u2013$2"); 
                
                /* Wrap the scriptureVerseHead (the reference at the top
                 * of the tooltip) in a link to the default Bible site.
                 * I would like to set a secondary tooltip here to show
                 * a menu of available Bible sites. */
                
                $html = jQuery(text);
                $html.find(".scriptureVerseHead")
                    .wrap('<a href="' + url + '"></a>');
                
                // An uglier way of doing that.
                /* text = text.replace(/(<h([0-9])[^>]*? class="scriptureVerseHead"[^>]*?>)(.+?)(<\/h\2>)/, 
                     "$1<a href=\"" + url + "\">$3</a>$4", text); */
                
                version = json.passages[ref].version;
                $copyrightInfo = scriptureTooltip.getCopyrightData(
                    version, json.copyright[version]);
                if (scriptureTooltip.showCopyrightDataInTooltip) {
                    $html.find(".scriptureText:last").after($copyrightInfo);
                }
                jQuery(this).tooltipster("content", $html);
            } else {
                /* If there was not a matching 'ref' member in the JSON
                 * object, either the reference was incorrectly parsed,
                 * or it was an invalid reference, perhaps not Scripture
                 * at all. We should leave the ref alone and even strip
                 * the 'scriptureRef' class from the element. */
                 jQuery(this).removeClass('scriptureRef');
            }
        });
    },
    
    /**
     * Assemble the copyright data to include in the tooltip,
     * if showCopyrightDataInTooltip is true.
     * 
     * @param version      The abbreviation for the version.
     * @param versionText  The copyright text returned by the API.
     */
    getCopyrightData: function (version, versionText) {
        "use strict";
        var copyright, copyrightURL;

        if (this.copyrightData.hasOwnProperty(version)) {
            /* This is a URL to a page giving copyright info for the
             * version. */
            copyrightURL = this.copyrightData[version];
        } else if (! versionText) {
            /* If there's no copyright page for the version to link,
             * and no copyright text returned from the API, then
             * we've got nothing to show.
             */
            return '';
        }

        /* This is ugly but it works. */
        if (copyrightURL) {
            // We have a page to link to.
            copyright = '<a href="' + copyrightURL + '"';
        } else {
            // Otherwise we just need text we can hover over for a basic tooltip.
            copyright = '<abbr';
        }
        copyright += ' title="' + versionText + '">Copyright Information' +
            (copyrightURL ? '</a>' : '</abbr>');
        copyright = '<p class="scriptureCopyright">' + copyright + '</p>';
        
        return copyright;
    },

    /**
     * This wraps each tagged scriptureRef in a link to the default
     * Bible site. I decided not to do this (and instead to apply the
     * link to the verse header in the tooltip) so mobile users could
     * click the scriptureRef (styled like a link but not a link)
     * to raise the tooltip, without leaving the page.
     * We can probably refine this behavior (i.e. possibly link the
     * scriptureRefs only when viewed by non-mobile users).
     */
    setLinks: function () {
        "use strict";
        jQuery(".scriptureRef").wrap(function() {
            var ref, url;
            ref = jQuery(this).attr("data-scriptureref");
            url = this.getDefaultLink(ref);
            return '<a class="scriptureLink" href="' + url + '"></a>';
        });
    },
    
    /**
     * Gets the default URL (at defaultScriptureSite) to link a
     * scriptureRef to.
     */
    getDefaultLink: function (ref) {
        "use strict";
        // Give the standard URL to use as a link for the reference.
        // May want to customize this to give user an option.
        return this.getLink[this.defaultScriptureSite](ref);
    },

    /**
     * Gets a link to a scriptureRef to several popular Bible sites.
     *
     * This is something I was working on to give a menu of links to
     * various Bible sites in the tooltip. I will probably implement
     * this as a secondary popup.
     */
    getLink: {
        BibleGateway: function (ref) {
            "use strict";
            var baseUrl, url, version;
            // e.g. https://www.biblegateway.com/passage/?search=Gen+1%3A1&version=RSV = Genesis 1:1
            baseUrl = 'https://www.biblegateway.com/passage/?search=';
            version = 'RSVCE';
            url = baseUrl + ref.replace(' ', '+', 'g') + '&version=' + version;
            return url;
        },
        NewAdvent: function (ref) {
            "use strict";
            var baseUrl, book, chapter, chapterstr, id, url, matches;
            // e.g. http://www.newadvent.org/bible/sir003.htm = Sirach 3
            baseUrl = 'http://www.newadvent.org/bible/';
            
            /* All books are first three letters of book name, lowercase
             * with exceptions of Judith (jdt) and Philemon (phm) --
             * then three-digit chapter number
             * i.e. mar016.htm for Mark 16 */
               
            matches = ref.match(/((?:\d )?(?:[A-Za-z ]+)) (\d+):?/);
            book = matches[1];
            chapter = matches[2];
            book = book.replace(/ /, '', 'g');
            if (chapter < 10) {
                chapterstr = '00' + chapter;
            } else if (chapter < 100) {
                chapterstr = '0' + chapter;
            } else {
                chapterstr = chapter;
            }
            if (book === 'Judith') {
                id = 'jdt';
            } else if (book === 'Philemon') {
                id = 'phm';
            } else {
                id = book.substr(0, 3).toLowerCase();
            }
            url = baseUrl + id + chapterstr + '.htm';
            return url;
        }
        /* Some more sites I was thinking of adding:
         * 
         * BibleStudyTools:
         * e.g. http://www.biblestudytools.com/genesis/passage.aspx?q=genesis+1:1-5
         *      = Genesis 1:1-5
         * 
         * Biblia:
         * e.g. http://biblia.com/bible/nrsv/Sir3.30 = Sirach 3:30
         *      http://biblia.com/books/nrsv/Ge1.1-5 = Genesis 1:1-5
         * 
         * BibleHub:
         * e.g. http://biblehub.com/genesis/1-5.htm = Genesis 1:5
         * 
         * and more!
         */
    }
};

/* Because WordPress loads jQuery in noConflict mode, we address
 * jQuery by its full name. */
jQuery(document).ready(function() {
    "use strict";
    scriptureTooltip.init();
});

// Debugging strings for JSLint, JSHint, etc.
/* global jQuery: false */
/* jslint browser: true, devel: true, unparam: true, todo: true, white: true */
