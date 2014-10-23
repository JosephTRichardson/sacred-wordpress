<?php
/**
 * scripture.php: Receive list of Scripture references from scriptureTooltip.js,
 *   make a request to Bible API for Scripture text, and return Scripture
 *   text as JSON.
 * 
 * @package Sacred Wordpress
 */

require_once('biblebooks.php');
require_once('bibles-org-request.php');

class ScriptureRequest {
    static public $config = array(
        'standard_request' => 'BiblesOrg',
        'deutero_request'  => 'BiblesOrg',
        'standard_version' => 'eng-ESV',
        'deutero_version'  => 'eng-KJVA',
        'debug'            => false,
    );

    /**
     * This will assemble a query string of semicolon-separated Scripture
     * references and send them to the APIs requested in $config.
     * (Comma-separated also works with Bibles.org API, but I want to be
     *  able to keep compound references (e.g. Jn 3:3,5) together on our
     *  end, though they are broken down when sent to the APi.)
     * 
     * @param The query string of references passed by the Ajax request.y
     */
    public function get_passages($ref) {
        global $BIBLEBOOKS;
        
        /* Expects JSON object in response from API request:
         *   {
         *      passages: {
         *          [
         *               title: '',
         *               version: '',
         *               text: ''
         *          ]
         *      }
         *      copyright: {
         *          'ESV': ...
         *          'NKJV': ...
         *      }
         *   }
         * Any specific API Request objects should implement this. 
         */
        
        $refs = explode(";", $ref);
        $refs = array_unique($refs);   # Remove duplicate requests

        $proto_refs = array();
        $deutero_refs = array();
        
        # Keep compound requests such as John 3:3,5 separate
        # We will request the verses separately and reassmble later.
        $compounds = array();
        
        $whole_chapter = array();
        
        while ($ref = array_shift($refs)) {
            if (preg_match('/((?:\d )?(?:[A-Za-z ]+)) (\d.*)$/',
              $ref, $matches)) {
                $book = $matches[1];
                $verse = $matches[2]; # Everything to the end
            } else {
                # Then it will probably fail at the API level, too;
                #   Skip it, or we're crash the whole thing.
                continue;
            }
            
            /* Challenge: Bibles.org API returns a request for a whole
             *  chapter (e.g. 1 Corinthians 13) as a range of verses
             *  (i.e. 1 Corinthians 13:1-13). 
             * Our end needs to be able to understand that
             *  1 Corinthians 13 === 1 Corinthians 13:1-13.
             * Will give the script a table of chapters
             *  ($BIBLEBOOKS[CHAPTERS]) and the number
             *  of verses in them ($BIBLEBOOKS[CHAPTER_VERSES],
             *  but bible versions vary slightly in verse numbering
             *  and this method will not be absolute.
             * Thankfully the API doesn't do this for a
             *  range of chapters (e.g. 1 John 1-2) */

            # Identify such a case of a whole chapter reference
            if (preg_match('/^\d+$/', $verse) and
              $BIBLEBOOKS['CHAPTERS'][$book] != 1) {
                $whole_chapter[] = $ref;
                # So we will know to look for it later.
            }

            # Convert compounds of adjacent verses (e.g. John 3:3,4)
            #  to a range. That's what we are really asking for` --
            #  otherwise API will treat as two separate verses.
            if (preg_match('/\b(\d+), ?(\d+)\b/', $verse, $matches) and
              abs($matches[2] - $matches[1]) == 1) {
                # Put them in the right order
                if ($matches[2] < $matches[1]) {
                    $repl = "$matches[2]-$matches[1]";
                } else {
                    $repl = "$matches[1]-$matches[2]";
                }
                # We do it this way rather than simply assign the string
                #  above in case this were a more complex reference, 
                #  e.g. John 3:3,4,9
                $verse = str_replace($matches[0], $repl, $verse);
                # Redefine the ref
                $ref = "$book $verse";
            }
            
            # Break up other compounds
            $verses = self::get_compound_parts($ref);
                # Will return array of chapter-verses if compound
                #  (e.g 1 John 1:6, 8)

            if (self::is_deutero($book)) {
                if ($verses) { # Then a compound reference
                    while ($v = array_shift($verses)) {
                        # Push each piece as a separate request, but
                        #  remember it's a compound.
                        $compounds[] = $ref;
                        $deutero_refs[] = $v;
                    }
                } else 
                    $deutero_refs[] = $ref;
            } else {
                if ($verses) { # Then a compound reference
                    while ($v = array_shift($verses)) {
                        # Push each piece as a separate request, but
                        #  remember it's a compound.
                        $compounds[] = $ref;
                        $proto_refs[] = $v;
                    }
                } else
                    $proto_refs[] = $ref;
            }
        }

        if ($proto_refs) {
            # Double-check for duplicates (may be new ones after compounding)
            $proto_refs = array_unique($proto_refs); 

            $ref = implode(';', $proto_refs);
            
            # A new object of the requested request
            $request_class = self::$config['standard_request'] . '_Request';
            $req = new $request_class();
            $res1 = $req->get_passages($ref, self::$config['standard_version']);
        }

        /* Have to make a separate request for deutero references since
         *  Protestant Bible translations (using the ESV by default)
         *  don't have them and API will return nothing.
         */
        if ($deutero_refs) { 
            
            # Double-check for duplicates (may be new ones after compounding)
            $deutero_refs = array_unique($deutero_refs);

            $ref = implode(';', $deutero_refs);
            
            # A new object of the requested request
            $request_class = self::$config['deutero_request'] . '_Request';
            $req = new $request_class();
            $res2 = $req->get_passages($ref, self::$config['deutero_version']);
        }
        
        if (! isset($res1)) {
            $res1 = ["passages" => [], "copyright" => []];
        }
        if (! isset($res2)) {
            $res2 = ["passages" => [], "copyright" => []];
        }
        
        $res = $this->merge_responses($res1, $res2);
        $this->reassemble_compounds($res, $compounds);
        
        /* Now try to find those whole chapter requests
         * @todo Need to make this part slightly more tolerant of variations in versification */
        
        foreach ($whole_chapter as $ref) {
            # First try to find how many verses were in that chapter
            self::debug_print("Trying to find whole_chapter $ref\n");
            preg_match('/^([A-Za-z0-9 ]+) (\d+)$/', $ref, $matches);
            $book = $matches[1];
            $chapter = $matches[2];
            $verses = $BIBLEBOOKS['CHAPTER_VERSES'][$book][$chapter-1];
            self::debug_print("Checking key '$book $chapter:1-$verses'\n");
            if ($verses) {
                if (array_key_exists("$book $chapter:1-$verses",
                  $res["passages"])) { # e.g. 1 Corinthians 13:1-13
                    self::debug_print("Found key '$book $chapter:1-$verses'\n");
                    # Make an alias key for the whole chapter
                    $res["passages"]["$book $chapter"] =
                        $res["passages"]["$book $chapter:1-$verses"];
                }
            }
        }
        
        # Finally (and this is an afterthought), wrap each response in
        #  a div so jQuery can deal with it.
        foreach ($res["passages"] as $ref => $passage) {
            $res["passages"][$ref]['text'] = '<div class="scriptureTooltip">' .
                $passage['text'] . '</div>';
        }
        
        return $res;
    }

    /**
     * A utility function to merge response objects.
     * 
     * @param $res1  First of two response objects to merge.
     * @param $res2  Second of two response objects to merge.
     */
    private function merge_responses(array $res1, array $res2) {
        $passages = array_merge($res1["passages"], $res2["passages"]);
        $copyright = array_merge($res1["copyright"], $res2["copyright"]);
        return array('passages' => $passages, 'copyright' => $copyright);
    }
    
    /**
     * A utility function to merge passages within responses
     *  (for use in reassembling compound references).
     * 
     * @param $passages  An array of passages to merge.
     * @param $ref       A compound reference that describes the assembled compound.
     */
    private function merge_passages($passages, $ref) {
        $new_passage = array();
        $new_passage['title'] = $ref;
        $new_passage['version'] = $passages[0]['version'];
        $new_passage['text'] = '';
        foreach ($passages as $passage) {
            $new_passage['text'] .= $passage['text'];
        }
        return $new_passage;
    }

    /**
     * Reassemble the compound references (e.g. John 3:3,5) that we
     *  broke up before the request. Will take the array of compound
     *  references and pick the constituent verses out of the response.
     * 
     * @param &$res       Reference to the response object
     * @param $compounds  array of compounds
     */
    private function reassemble_compounds(&$res, array $compounds) {
        
        $passages = $res["passages"];
        $passages_to_compound = array();
                
        foreach ($compounds as $compound) {
            $passages_to_compound = [];
            # Will get an array of what verses to reassemble
            $verses = self::get_compound_parts($compound);

            foreach ($verses as $verse) {
                $passage = $passages[$verse];
                
                # Remove the old head
                $passage['text'] = preg_replace(
                    '/<h2[^>]* class="scriptureVerseHead[^>]*>(.*?)<\/h2>/',
                    '', $passage['text']);
                
                # Only want to return one scriptureVerseHead for each compound
                if (! isset($verseHead)) { # Only do this once
                    $verseHead = '<h2 class="scriptureVerseHead">' .
                    $compound .  ' <span class="version">(' .
                    $passage['version'] . ')</span></h2>';
                }
                
                if (! $passage) {
                    # Failed to receive expected part of compound
                } else {
                    $passages_to_compound[] = $passage;
                }
            }
            $compounded =
                $this->merge_passages($passages_to_compound, $compound);
            # Put the new head on it
            $compounded['text'] = $verseHead . $compounded['text'];
            $res["passages"][$compound] = $compounded;
        }
    }
    
    /**
     * Break a compound reference into parts (individual verses).
     * Returns an array of verses.
     * 
     * @param $ref  The compound reference to break up.
     */
    static public function get_compound_parts($ref) {
        if (preg_match('/((?:\d )?(?:[A-Za-z ]+)) (\d.*)$/', $ref, $matches)) {
            $book = $matches[1];
            $verse = $matches[2]; # Everything to the end
        } else {
            throw new Exception("Unrecognized reference: $ref");
        }
        
        if (preg_match('/(\d+):(\d+(?:,\ ?\d+)+)$/', $verse, $matches)) {
            $chapter = $matches[1];
            $verses_str = $matches[2];
            $verses = preg_split('/, ?/', $verses_str);
            foreach ($verses as &$v) {
                $v = "$book $chapter:$v";
            }
            return $verses;
        }
        return 0;
    }

    /**
     * Is the book deuterocanonical? Check it against the table.
     * 
     * @param $book  A book name.
     */
    static public function is_deutero($book) {
        global $BIBLEBOOKS;
        if (array_key_exists($book, $BIBLEBOOKS['DEUTERO'])) {
            return 1;
        }
        return 0;
    }

    /**
     * Attempt to transform the reference into something canonical,
     *  given an unfamiliar book name (possibly an alternate name).
     *  E.g. The Bibles.org API returns the book of Sirach as
     *  Ecclesiasticus. Change it back to Sirach -- since that is
     *  what the requesting script is expecting.
     * 
     * @param $ref   A ref to canonicalize.
     */
    static public function to_canonical($ref) {
        global $BIBLEBOOKS;
        if (! isset($BIBLEBOOKS['BOOKS'])) {
            # Don't do this unless we have to
            BibleBooks::prepare_book_maps();
        }
        
        if (preg_match('/((?:\d )?(?:[A-Za-z ]+)) (\d.*)$/', $ref, $matches)) {
            $book = $matches[1];
            $verse = $matches[2];
        }
        if ($BIBLEBOOKS['BOOKS'][$book]) {
            $book = $BIBLEBOOKS['BOOKS'][$book];
        }
        return "$book $verse";
    }

    /**
     * Print if the debug flag is on.
     * Only useful in accessing this script directly, since any stray
     * text disrupts the JSON the other end is expecting.
     * 
     * @param $string  String to print.
     */
    static public function debug_print($string) {
        if (! ScriptureRequest::$config['debug']) {
            return 0;
        }
        print $string;
    }

    /**
     * Constructor for ScriptureRequest object.
     * Gets the query string from the CGI and triggers the request.
     */
    public function __construct() {
        # Set the debug flag if requested.
        if (array_key_exists('debug', $_GET)) {
            ScriptureRequest::$config['debug'] = 1;
        }
        
        # Sanitize input
        $reflist = filter_input(INPUT_GET, 'ref', FILTER_SANITIZE_STRING, 
            FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
        
        if ($reflist) {
            header("Content-type: application/json");
            $res = $this->get_passages($reflist);
            print json_encode($res);
        }
    }
} /* class ScriptureRequest */

$scriptureReq = new ScriptureRequest();
