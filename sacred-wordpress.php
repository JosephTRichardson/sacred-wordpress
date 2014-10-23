<?php
/**
 * sacred-wordpress.php: Information about plugin and hooks for WordPress.
 * 
 * @package Sacred Wordpress
 * @author Joseph T. Richardson <joseph.t.richardson@gmail.com>
 * @copyright 2014 Joseph T. Richardson
 * @license MIT License (http://opensource.org/licenses/MIT)
 */

/**
 * Plugin Name: Sacred WordPress
 * Plugin URI: http://jtrichardson.com/projects/sacred-wordpress
 * Description: A plugin to find and mark Scripture references in post content, retrieve Scripture text from a webservice API, and present it to the reader as a tooltip to the post reference.
 * Version: 0.10.1
 * Author: Joseph T. Richardson <joseph.t.richardson@gmail.com>
 * Author URI: http://jtrichardson.com/
 * License: MIT License (http://opensource.org/licenses/MIT)
 */

define("SACRED_WORDPRESS_VERSION", '0.10.1');
require_once('tag_scripture.php');
require_once('scripture.php');

// Add filters and actions
add_filter( 'the_content', 'tag_scriptures_in_the_content_filter' );
add_filter( 'comment_text', 'tag_scriptures_in_comment_text_filter' );
add_action( 'wp_enqueue_scripts', 'sacred_wordpress_enqueue_scripts' );

/**
 * Implements WordPress filter 'the_content'
 */
function tag_scriptures_in_the_content_filter( $content ) {
    $scriptureMarkup = new ScriptureMarkup($content);
    $content = $scriptureMarkup->tag_text();  // in tag_scripture.php
    return $content;
}

/**
 * Implements WordPress filter 'comment_text'
 */
function tag_scriptures_in_comment_text_filter( $comment_text ) {
    $scriptureMarkup = new ScriptureMarkup($content);
    $comment_text = $scriptureMarkup->tag_text();  // in tag_scripture.php
    return $comment_text;
}

/**
 * Implements WordPress action 'wp_enqueue_scripts'
 */
function sacred_wordpress_enqueue_scripts() {
    wp_enqueue_script('tooltipster',       plugins_url('3rdparty/jquery.tooltipster.js', 
        __FILE__), array('jquery'), '3.2.6'); // This is Tooltipster version
        
    /* If you'd rather use the jsmin'ed version, you save only about
     *  9K bytes in load. */
    wp_enqueue_script('scriptureRef',      plugins_url('scriptureTooltip.js',
        __FILE__), array('jquery', 'tooltipster'), SACRED_WORDPRESS_VERSION);
    /* wp_enqueue_script('scriptureRef',      plugins_url('scriptureTooltip.min.js',
        __FILE__), array('jquery', 'tooltipster'), SACRED_WORDPRESS_VERSION); */

    /* This is for the Fair Use Information System (FUMS) requested by the Bibles.org API.
     * Should check this URL from time to time to keep current.
     * @link http://bibles.org/pages/api/documentation/fums */
    wp_enqueue_script('biblesorg_fums',
        'http://d2ue49q0mum86x.cloudfront.net/include/fums.c.js', null, null);

    wp_enqueue_style('tooltipster-css',  plugins_url('3rdparty/tooltipster.css',
        __FILE__), null, '3.2.6');
    wp_enqueue_style('tooltipster-shadow-css',plugins_url('3rdparty/tooltipster-shadow.css',
        __FILE__), array('tooltipster-css'), '3.2.6'); // This is Tooltipster version
    wp_enqueue_style('scriptureTooltip-css',   plugins_url('scriptureRef.css',
        __FILE__), array('tooltipster-css', 'tooltipster-shadow-css'), SACRED_WORDPRESS_VERSION);
}
