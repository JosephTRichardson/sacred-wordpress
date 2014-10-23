<?php
/**
 * bibles-org-request.php: Request object for Bibles.org API
 * (American Bible Society's Bible Search API)
 * @link https://bibles.org/pages/api
 * 
 * @package Sacred Wordpress
 */

class BiblesOrg_Request {

    /* To use this API, you need a free API key.
     * @link http://bibles.org/pages/api/signup
     */
    private $APIKEY = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

    /* API actually provides a variety of different requests, but the
     * 'passages' one proved to be the most useful one for what we're
     * doing here.
     */
    private $urls = array(
        "passages" => "https://bibles.org/v2/passages.js"
    );
    
    /* API will include footnotes and crossreferences in text, but it
     * is very muddy HTML and it was more information than I wanted to
     * load in the tooltip. If you want it and want to deal with it,
     * you can enable it here.
     */
    private $include_marginalia = 0;
    
    /* This keeps the responses until we package them to return. */
    private $buffer;
    private $copyright;

    /**
     * We use Curl to make our requests to the API.
     * Initialize it here.
     */
    private function init_curl() {
        $apikey = $this->APIKEY;
        $ch = curl_init();
        // Don't verify SSL certificate.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // Return the response as a string.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Follow any redirects.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // Set up authentication.
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $apikey . ":X");
        return $ch;
    }

    /**
     * Actually make the request (a string of all passages we want)
     * to the API via Curl.
     * 
     * @param $url  The REST URL containing the query string.
     */
    private function curl_get($url) {
        $ch = $this->init_curl();
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response; # As JSON
    }

    /**
     * This is the method called by the main ScriptureRequest object
     * to make the request to the API. It retrieves the response and
     * returns it in the format ScriptureRequest is expecting.
     * 
     * @param $ref      The string of references we want.
     * @param $version  The Bible version we want them in, in the form API expects.
     * 
     * (See https://bibles.org/v2/versions.xml [for XML] or
     *  https://bibles.org/v2/versions.js [for JSON] with your API key
     *  [i.e. https://APIKEY:X@https://bibles.org:443/...]
     *  to get a list of all the supported versions, in a multiplicity
     *  of different languages.
     *  https://bibles.org/v2/versions.xml?language=eng-US and
     *  https://bibles.org/v2/versions.xml?language=eng-GB are easier
     *  to manage if you want English (of which, as of now, there are
     *  only a few supported versions:
     *       eng-AMP   Amplified Bible
     *       eng-CEV   Contemporary English Version
     *       eng-CEVD  Contemporary English Version (with deuterocanon)
     *       eng-CEVUK Contemporary English Version (Anglicised)
     *       eng-ESV   English Standard Version
     *       eng-GNTD  Good News Translation (with deuterocanon)
     *                     (formerly known as Today's English Version)
     *       eng-GNBDC Good News Bible (with deuterocanon)
     *                     (only apparent difference from GNTD is
     *                      Anglicisation)
     *       eng-KJV   King James Version
     *       eng-KJVA  King James Version with Apocrypha
     *       eng-MSG   The Message
     *       eng-NASB  New American Standard Bible
     *  Other useful highlihghts:
     *       por-NTLH  Nova Tradução na Linguagem de Hoje (Portuguese)
     *       spa-DHH   Biblia Dios Habla Hoy (Spanish)
     *       spa-RVR60 Biblia Reina Valera 1960 (Spanish)
     *       cym-BCN   Beibl Cymraeg Newydd (Welsh)
     */
    public function get_passages($ref, $version) {
        # This will send query string of semicolon-separated references
        #  and receive a JSON <passages> object with <passage> members.
        # (Comma-separated also works with this API.)
        ScriptureRequest::debug_print("[bibles-org-request] get_passages('$ref')\n");
        
        # This isn't really necessary I don't think, but play it safe.
        $ref = preg_replace('/\s+/', '+', $ref);

        $url = $this->urls["passages"] . "?q[]=" . $ref;
        $url .= "&version=" . $version;
        if ($this->include_marginalia) {
            $url .= "&include_marginalia=true";
        }
        $jsonstr = $this->curl_get($url);
        
        $refs = explode(';', $ref);
        $this->parse_passages_response($jsonstr, $refs);
        
        $wrapper = $this->return_data();
        return $wrapper;
    }

    /**
     * Parse the JSON received from the API, reformat it as we want it,
     * and return it in a form we can use.
     * 
     * @param $jsonstr  The JSON string received from the API.
     * @param $refs     An array of the requests we made.
     */
    public function parse_passages_response($jsonstr, $refs) {
        $json = json_decode($jsonstr);
        if (! $json) {
            # Then something is wrong; we didn't receive any JSON.
            return [];
        }
        $passages = $json->response->search->result->passages;
        $fums_tid = $json->response->meta->fums_tid;
        
        # A container to keep all our copyright information.
        $copyright_obj = array();
        
        foreach ($passages as $passage) {
            $return_text = "";
            $version = $passage->version_abbreviation;

            $copyright = $passage->copyright;
            # Strip the API's HTML from copyright data; we are going
            #  to format it otherwise.
            $copyright = preg_replace('/<\/?p>/', '', $copyright);
            $copyright = trim($copyright);
            # We only need the copyright info once per version.
            # This saves a good many bytes in the response.
            $copyright_obj[$version] = $copyright;
            
            # Reformat the HTML in the returned text.
            # This probably wastes some bytes in the response, but it's
            # easier to deal with stylistically.
            $text = $passage->text;
            $text = preg_replace('/\s+/', " ", $text);
            $text = preg_replace('/class="(?:p|m)"/',
                "class=\"scriptureText\"", $text);
            $text = preg_replace('/class="s1"/',
                "class=\"scriptureHeading\"", $text);
            $text = preg_replace('/class="q1"/',
                "class=\"scriptureText scripturePoetry1\"", $text);
            $text = preg_replace('/class="q2"/',
                "class=\"scriptureText scripturePoetry2\"", $text);
            $title = $passage->display;
            $return_text .= "<h2 class=\"scriptureVerseHead\">$title " .
                "<span class=\"version\">($version)</span></h2>\n";
            $return_text .= $text;
            $return_text .= "<script>_BAPI.t('$fums_tid');</script>";

            # This is why we wanted an array of the requests: so we could
            #  search through it and make sure the passages we received
            #  are the ones we were expecting. If there's an unfamiliar
            #  one, then it's possible the API is calling something by a
            #  different name (e.g. Ecclesiasticus == Sirach).
            if (! array_search($title, $refs)) {
                # If we don't recognize the title as one we requested,
                #  load the book tables and try to convert it to
                #  something canonical.
                $title = ScriptureRequest::to_canonical($title);
            }
            
            # Package the text for return.
            $passage_obj = array(
                'title'     => $title,
                'text'      => $return_text,
                'version'   => $version,
            );
            # Put it in the object buffer.
            $this->buffer[$title] = $passage_obj;
        }
        # Store the aggregated copyright data.
        $this->copyright = $copyright_obj;
    }
    
    /**
     * Package all the received data for return.
     */
    public function return_data() {
        if (count($this->buffer)) {
            $wrapper = array(
                'passages'  => $this->buffer,
                'copyright' => $this->copyright
            );
        } else {
            $wrapper = [];
        }
        return $wrapper;
    }
}
