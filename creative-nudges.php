<?php
/*Freewheeling Easy Mapping Application
 *
 *		A collection of routines for display of trail maps and amenities
 *
 *		copyright Roy R Weil 2019 - https://royweil.com
 *
 */
/*
Plugin Name: Creative Nudges
Plugin URI:  https://plugins.RoyWeil.com/creative-nudges/
Description: display the Creative Nudges
Author:      Roy Weil
Author URI:  https://RoyWeil.com
Donate URI: https://plugins.royweil.com/donate
Requires at least: 6.8.3
Tested up to: 6.8.3
Stable tag: 1.1.2	// change in .patch indicates a data, but not the code
License: private
Version: 2.1.1
Text Domain: creative-nudges
Domain Path: /lang
*/
// all in the www-include folder
require_once "freewheelingeasy-wpdpExtra.php";
require_once "display_tables_class.php";
require_once "rrwFormat.php";
require_once "rrw_util_inc.php";
require_once "rrwParam.php";
// in this directory
require_once "get-citation.php";
require_once "edit_nudges.php";
require_once "storeDisplay.php";
error_reporting(E_ALL);
ini_set("display_errors", true);
global $eol, $errorBeg, $errorEnd;
class creative_nudges
{
    private static $maxNumberOfNudges = 70;
    public static $errorBeg = "<span style='color:red; font-weight:bold;'>";
    public static $errorEnd = "</span>";
    public static $eol = "<br/>\n";
    private static $DatabaseReference = 'reference_page';
    public static $DatabaseNudges = 'nudges';
    public static $DatabaseAuthors = 'authors';
    public static $DatabaseComments = 'comments';
    private function __construct()
    {
        ini_set('display_errors', true);
    }
    private static function displaySql($sql, $size, $align)
    {
        global $wpdb;
        $eol = "<br/>\n";
        $msg = "";
        try {
            $debugSql = rrwParam::isDebugMode("debugSql");
            if ($debugSql) $msg .= "displaySsl:sql = $sql" . $eol;
            $recNudgeFound = $wpdb->get_results($sql, ARRAY_A);
            if (empty($recNudgeFound) || 0 == $wpdb->num_rows) {
                $sql = "select * from " . self::$DatabaseNudges . " where id = 404";
                $recNudgeFound = $wpdb->get_results($sql, ARRAY_A);
            }
            if ($debugSql) $msg .= "displaySql:results = " . rrwUtil::print_r($recNudgeFound, true, "DisplaySql:recsNudge") . $eol;
            $cntOut = 0;
            foreach ($recNudgeFound as $recNudge) {
                $cntOut++;
                if ($cntOut > 4) {
                    break;
                }
                $msg .= self::buildImage($recNudge, $size, $align);
            } // end foreach
        } catch (Exception $e) {
            $msg .= self::$errorBeg . " E456 " . $e->getMessage() . self::$errorEnd;
        }
        return $msg;
    } // end displaySql

    /**
     * Builds an HTML image element with a link wrapper for a creative nudge record.
     *
     * This method generates a linked image that displays a creative nudge with specified
     * size and alignment. The image is sourced from the combined images directory and
     * links to the corresponding permalink on the creative-nudges.com website.
     *
     * @param array $record An associative array containing nudge record data with keys:
     *                      - 'id': The unique identifier for the nudge
     *                      - 'nudge': The nudge text content
     *                      - 'reference': The reference text for the nudge
     *                      - 'permalink': The permalink slug for the nudge page
     * @param string $size The CSS width value for the image (e.g., '100px', '50%')
     * @param string $align The CSS float alignment value ('left', 'right', 'none')
     *
     * @return string Complete HTML string containing the linked image element
     */
    public static function buildImage($record, $size, $align): string
    {
        $keyId  = $record["id"];
        $nudge = $record["nudge"];
        $reference = $record["reference"];
        $permalink = $record["permalink"];
        $imageDir = plugins_url("") . "/creative-nudges";
        $img1 = "<img class='card' src='$imageDir/images-combined/combined-$keyId.png' alt='$nudge - $reference key =#$keyId'
                                style='width:$size; float:$align' />";
        $img = "<a href='https://creative-nudges.com/" . $permalink . "' >$img1</a>";
        return $img;
    }
    /**
     * Builds a WHERE clause for searching nudges based on the provided search term.
     *
     * This method constructs a SQL WHERE clause that searches across multiple fields
     * (nudge, reference, permalinkAuto, and permalink) using LIKE operators with
     * wildcard matching.
     *
     * @param string $lookFor The search term to look for across the nudge fields
     * @return string The formatted WHERE clause ready for use in a SQL query
     *
     * NOTE: This method is NOT vulnerable to SQL injection attacks,
     *     because the input is sanitized on being read by the rrwParam routine
     */
    public static function buildWhere($lookFor): string
    {
        if (empty($lookFor))
            return "where id = 404 "; // no results
        if (strlen($lookFor) < 3 && is_numeric($lookFor)) {
            // if it's a short number, assume it's an ID
            return " where id = $lookFor ";
        }
        $where = " WHERE nudge LIKE '%$lookFor%' OR reference LIKE '%$lookFor%' OR permalinkAuto LIKE '%$lookFor%' OR permalink LIKE '%$lookFor%' ";
        return $where;
    }
    /**
     * Displays search results for creative nudges or a random image if no search term provided.
     *
     * This method handles searching through the nudges database based on a search term
     * from the SearchBox attribute. If a search term is provided, it searches both the
     * 'nudge' and 'reference' fields using LIKE queries. If no search term is provided,
     * it displays a random image instead.
     *
     * @param array $attributes Associative array of attributes containing search parameters
     *                         Expected to contain 'SearchBox' key with search term
     *
     * @return string HTML string containing either search results or random image display
     *               Includes debug SQL comments when debug mode is enabled
     * @since 1.0.0
     */
    /*
    public static function displaySearch($attributes)
    {
        global $wpdb;
        $imageHTML = "";        // get an html string to display an image
        $debugRandom = rrwParam::isDebugMode("debugRandom");
        $searchThing = rrwParam::String('SearchBox', $attributes);
        //
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, 'store') !== false) {
            $align = "left";
        } else {
            $align = "center";
        }
        $imageHTML .= "
<style>
.card{
width:400px !important;
height:auto !important;
margin-right:10px;
}
</style>
";
        if (empty($searchThing)) {
            // no search term so display a random image
            $image = self::getRandomImage();
            $imageHTML .= "<div style='text-align:$align;'> $image </div>";
        } else {
            $like_search = '%' . $wpdb->esc_like($searchThing) . '%';
            $sql = $wpdb->prepare(
                "SELECT distinct* FROM " . self::$DatabaseNudges .
                    " WHERE nudge LIKE %s OR reference LIKE %s or permalinkAuto like %s or permalink like %s ORDER BY type, id  ",
                $like_search,
                $like_search,
                $like_search,
                $like_search
            );
            $imageHTML .= "<!-- sql = $sql -->\n";
            if ($debugRandom)
                print "displaySearch:sql = $sql " . self::$eol;
            $imageHTML .= self::displaySql($sql);
        }
        return $imageHTML;
    } // end displaySearch
    */
    /**
     * Retrieves a random image from the nudges database.
     *
     * Generates a random ID between 1 and $maxNumberOfNudges to select a random record from the
     * nudges database table. Optionally outputs debug information if debug mode
     * is enabled for "debugRandom".
     *
     * @return mixed Returns the image data from the database query result
     *
     */
    public static function getRandomImage($attributes)
    {
        $debugRandom = rrwParam::isDebugMode("debugRandom");
        $size = rrwParam::String('size', $attributes, '300px');
        $align = rrwParam::String('align', $attributes, 'center');
        if ($debugRandom) print "getRandomImage: size = $size, align = $align " . self::$eol;
        $keyId = floor(rand(1, self::$maxNumberOfNudges));     // assuming IDs range from 1 to $maxNumberOfNudges
        $sql = "select * from " . self::$DatabaseNudges . " where id = $keyId";
        if ($debugRandom) print "getRandomImage:sql = $sql " . self::$eol;
        $image = self::displaySql($sql, $size, $align);
        return $image;
    } // end getRandomImage

    public static function permalink($attributes)
    {
        global $wpdbExtra;
        $msg = "";
        $sqlAll = "select * from " . self::$DatabaseNudges;
        $recs = $wpdbExtra->get_resultsA($sqlAll);
        foreach ($recs as $rec) {
            $id = $rec['id'];
            $permalink = $rec['permalink'];
            $nudge = $rec['nudge'];
            $reference = $rec['reference'];
            $search = $reference;
            $search = str_replace('“', '', $search);
            $search = str_replace("?", '', $search);
            $search = str_replace(",", '', $search);
            $search = str_replace(".", '', $search);
            $search = str_replace("'", '', $search);
            $search = str_replace("’", '', $search);
            $search = str_replace("[", '', $search);
            $search = str_replace("]", '', $search);
            $search = str_replace("”", '', $search);
            $search = str_replace("]", '', $search);
            $search = str_replace("]", '', $search);
            $matches = explode(" ", $search);
            //  $msg .= rrwUtil::print_r($matches, true, "permalink:matches for nudge $id - $search") . self::$eol;
            $permaLink = "";
            $cnt = 0;
            foreach ($matches as $word) {
                if (strlen($word) < 5)
                    continue;
                $cnt++;
                if ($cnt > 3)
                    break;
                $permaLink .= strtolower($word) . "-";
            }
            $permaLink = substr($permaLink, 0, -1); // remove last dash
            $sql = "update " . self::$DatabaseNudges . " set permalinkAuto = '$permaLink' where id = $id;";
            $msg .= $wpdbExtra->query($sql);
            $msg .= $permaLink  . self::$eol;


            if (empty($permalink))
                continue;
            if ($permalink == rrwParam::String("permalink", $attributes)) {
                $msg .= self::displaySql("select * from " . self::$DatabaseNudges . " where id = $id");
                return $msg;
            }
        }

        return $msg;
    }
} // end class
//
add_shortcode('creative_nudge_author', array('creative_edit', 'author'));
add_shortcode('creative_nudge_displayCard', array('creative_edit', 'editOne'));
add_shortcode('creative_nudge_editOne', array('creative_edit', 'editOne'));
add_shortcode('creative_nudge_all', array('creative_nudges', 'displayAll'));
add_shortcode('creative_nudge_permalink', array('creative_nudges', 'permalink'));
add_shortcode('creative_nudge_random', array('creative_nudges', 'getRandomImage'));
add_shortcode('reference_read_page', array('creative_nudges', 'readReferencesPage'));
add_shortcode('reference_read_card', array('creative_nudges', 'ReadReferencesCard'));
add_shortcode('reference_display', array('creative_nudges', 'ReadReferencesDisplay'));
add_shortcode('reference_check', array('creative_nudges', 'ReadReferencesCheck'));
add_shortcode("creative_store", array('creative_store', 'displayPage'));
add_shortcode("creative_categories", array('creative_store', 'displayCategories'));
add_shortcode('citation_lookup_title', function ($attribute) {
    $title = '';
    try {
        $title = rrwParam::String('title', $attribute);
    } catch (Throwable $t) {
        if (isset($attribute['title'])) $title = $attribute['title'];
    }
    if ($title === '') return creative_nudges::$errorBeg . 'Provide title=...' . creative_nudges::$errorEnd;
    return creative_nudges_get_Citation::fromTitle($title);
});
add_shortcode('isbn_citation', function ($attribute) {
    $isbn = '';
    $style = '';
    try {
        $isbn = rrwParam::String('isbn', $attribute);
    } catch (Throwable $t) {
        if (isset($attribute['isbn'])) $isbn = $attribute['isbn'];
    }
    try {
        $style = rrwParam::String('style', $attribute);
    } catch (Throwable $t) {
        if (isset($attribute['style'])) $style = $attribute['style'];
    }
    if ($isbn === '') return creative_nudges::$errorBeg . 'Provide isbn=...' . creative_nudges::$errorEnd;
    if ($style === '') $style = 'chicago-note-bibliography';
    return creative_nudges_get_Citation::fromIsbn($isbn, $style);
});
add_shortcode(
    'citation',
    function ($attribute) {
        try {
            $msg = "";
            $doi = rrwParam::String('doi', $attribute);
            $isbn = rrwParam::String('isbn', $attribute);
            $style = rrwParam::String('style', $attribute, 'chicago-note-bibliography');
            if (! empty($doi))
                $msg .= creative_nudges_get_Citation::fromDOI($doi, $style);
            if (! empty($isbn))
                $msg .= creative_nudges_get_Citation::fromIsbn($isbn, $style);
            if (empty($doi) && empty($isbn))
                return creative_nudges::$errorBeg . 'Provide either  ?doi=... or ?isbn=...' . creative_nudges::$errorEnd;
        } catch (Throwable $t) {
            return $msg . creative_nudges::$errorBeg . 'error at bottom of citation routine ' . $t->getMessage() . creative_nudges::$errorEnd;
        }
        return $msg;
    }
);
