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
    private function __construct()
    {
        ini_set('display_errors', true);
    }
    private static function displaySql($sql, $includeText = false)
    {
        global $wpdb;
        $eol = "<br/>\n";
        $msg = "";
        try {
            $debugSql = rrwParam::isDebugMode("debugSql");
            if ($debugSql) $msg .= "displaySsl:sql = $sql" . $eol;
            $recsNudge = $wpdb->get_results($sql, ARRAY_A);
            if (empty($recsNudge) || 0 == $wpdb->num_rows) {
                $arrayZero = array("id" => 404, "type=>" => "none", "nudge" => "Default Nudge", "reference" => "Default Reference");
                array_push($recsNudge, $arrayZero);;
            }
            if ($debugSql) $msg .= "displaySql:results = " . rrwUtil::print_r($recsNudge, true, "DisplaySql:recsNudge") . $eol;
            $cntOut = 0;
            foreach ($recsNudge as $recNudge) {
                $cntOut++;
                if ($cntOut > 4) {
                    break;
                }
                $type = $recNudge["type"];
                $nudge = $recNudge["nudge"];
                $reference = $recNudge["reference"];
                $keyId = $recNudge["id"];
                if ($debugSql) $msg .= "key id = $keyId" . $eol;
                $imageDire = plugins_url("") . "/creative-nudges";
                $nudgePng = "<img class='card' src='$imageDire/images-nudges/nudge-$keyId.png' alt='$nudge key =#$keyId' />";
                $refPng = "<img class='card', src='$imageDire/images-nudges/reference-$keyId.png' alt='$reference key =#$keyId' />";
                $nudgeRef = "<img class='card' src='$imageDire/images-combined/combined-$keyId.png' alt='$nudge - $reference key =#$keyId'
                                align='left' />";
                $msg .= "$nudgeRef";
            } // end foreach
        } catch (Exception $e) {
            $msg .= self::$errorBeg . " E456 " . $e->getMessage() . self::$errorEnd;
        }
        return $msg;
    } // end displaySql
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
                "SELECT * FROM " . self::$DatabaseNudges . " WHERE nudge LIKE %s OR reference LIKE %s ORDER BY type, id",
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
    public static function getRandomImage()
    {
        $debugRandom = rrwParam::isDebugMode("debugRandom");
        $keyId = floor(rand(1, self::$maxNumberOfNudges));     // assuming IDs range from 1 to $maxNumberOfNudges
        $sql = "select * from " . self::$DatabaseNudges . " where id = $keyId";
        if ($debugRandom) print "getRandomImage:sql = $sql " . self::$eol;
        $image = self::displaySql($sql);
        return $image;
    } // end getRandomImage
    private static function cardReferences($attr)
    {
        $msg = "";
        $msg .= "/\[([^\]]*)\]/g";
        return $msg;
    }
    public static function readReferencesPage($attributes)
    {
        global $wpdb;
        $msg = "";
        ini_set("display_errors", true);
        try {
            // Fetch the page content
            $html = file_get_contents("https://creative-nudges.com/references/");
            if ($html === false) {
                throw new Exception("Error: Could not retrieve page content." . self::$eol);
            }
            // Extract body content using regex
            if (!preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
                throw new Exception("Could not extract body content.");
            }
            $bodyContent = $matches[1];
            // $msg .= "Extracted Body Content." . htmlspecialchars(substr($bodyContent, 0, 8000)) . self::$eol . self::$eol ;
            //extract the paragraphs
            $pregResult = preg_match_all('/(<p.*?<\/p>)/', $bodyContent, $matches);
            if (false === $pregResult || $pregResult == 0) {
                throw new Exception("Could not extract paragraphs.");
            }
            $msg .= "Extracted " . count($matches[1]) . " Paragraphs:" . self::$eol;
            $wpdb->query("truncate table " . self::$DatabaseReference);
            $cntParas = 0;
            //        $msg .= rrwUtil::print_r( $matches, true, "readReferencesPage:matches" ) . self::$eol;
            foreach ($matches[1] as $para) {
                $cntParas++;
                if ($cntParas > 500)
                    break;
                $para = str_replace("<p>", '', $para);
                $para = str_replace("</p>", '', $para);
                if (empty(trim($para)))
                    continue; // Skip empty paragraphs
                //	$msg .= "($cntParas) " . htmlspecialchars($para) . self::$eol;
                $brackets = self::extractSomething($para, "[", "]");
                $title = self::extractSomething($para, "<em>", "</em>");
                $isbn = self::extractItem2End($para, "ISBN");
                $doi = self::extractDoi($para);
                $citation = "";
                if (!empty($doi)) {
                    $citation = creative_nudges_get_Citation::fromDOI($doi);
                }
                if (empty($citation) && !empty($isbn)) {
                    $citation = creative_nudges_get_Citation::fromIsbn($isbn);
                }
                if (empty($doi) && empty($isbn)) {
                    $citation = creative_nudges_get_Citation::fromTitle($title);
                }
                $updateData = array(
                    'contents' => $para,
                    'reference' => $brackets,
                    'title' => $title,
                    'lookup' => "$doi\t$isbn",
                    "citation" => $citation,
                );
                $updateCnt = $wpdb->insert(
                    self::$DatabaseReference,
                    $updateData
                );
            } // end foreach
            $msg .= self::dumpReferences();
        } catch (Exception $e) {
            $msg .= self::$errorBeg . "Error: " . $e->getMessage() . self::$errorEnd;
        }
        return $msg;
    } // end readReferencesPage
    private static function extractSomething($para, $openChar, $closeChar): string
    {
        $iiOpen = stripos($para, $openChar);
        $iiClose = strpos($para, $closeChar);
        if (false === $iiOpen || false === $iiClose || $iiClose <= $iiOpen)
            return "";
        $ignoreOpenChars = strlen($openChar);
        $ignoreCloseChars = strlen($closeChar);
        $output = substr($para, $iiOpen + $ignoreOpenChars, $iiClose - $iiOpen + $ignoreOpenChars - $ignoreCloseChars);
        return $output;
    } // end extractSomething
    private static function extractItem2End($para, $lookFor): string
    {
        try {
            $iiOpen = stripos($para, "$lookFor");
            if (false === $iiOpen)
                return "";
            if ("isbn" == strtolower($lookFor))
                $iiEnd = strlen($para); // ISBN goes to end of paragraph
            elseif ("doi" == strtolower($lookFor))
                $iiEnd = strpos($para, " ", $iiOpen);
            else {
                return "";  // unknown lookFor// nt one of the know things
            }
            $ignoreChars = strlen($lookFor);
            //  print "Item2End: $iiOpen to $iiEnd, $ignoreChars " . self::$eol;
            $output = substr($para, $iiOpen + $ignoreChars + 1, $iiEnd - $iiOpen - $ignoreChars - 1);
        } catch (Exception $e) {
            throw new Exception(" Error in extractItem2End: " . $e->getMessage());
        }
        return $output;
    } // end extractItem2End
    private static function extractDoi($para): string
    {
        try {
            $iiOpen = stripos($para, "doi.org");
            if (false !== $iiOpen) {
                $iiSpace = strpos($para, " ", $iiOpen);
                if (false === $iiSpace)
                    $iiSpace = strlen($para);
                $iiQuote = strpos($para, '"', $iiOpen);
                if (false !== $iiQuote && $iiSpace > $iiQuote)
                    $iiSpace = $iiQuote;
                $output = substr($para, $iiOpen + 7, $iiSpace - $iiOpen - 7);
                return "https://$output";
            }
            $para = str_replace("doi: ", "doi:", $para);
            $para = str_replace("doi=", "doi:", $para);
            $iiOpen = strpos($para, "doi:");
            if (false !== $iiOpen) {
                $iiSpace = strpos($para, " ", $iiOpen);
                if (false === $iiSpace)
                    $iiSpace = strlen($para);
                $output = substr($para, $iiOpen + 4, $iiSpace - $iiOpen - 4);
                return "https://$output";
            }
            $iiOpen = strpos($para, "10.");
            if (false !== $iiOpen) {
                $iiSpace = strpos($para, " ", $iiOpen);
                if (false === $iiSpace)
                    $iiSpace = strlen($para);
                $output = substr($para, $iiOpen + 3, $iiSpace - $iiOpen - 3);
                return "https://$output";
            }
            if (strpos($para, "doi") === false)
                return "";
            else
                return creative_nudges::$errorBeg . "E123: unrecognized DOI format in paragraph." . creative_nudges::$errorEnd;
        } catch (Exception $e) {
            throw new Exception(" Error in extractDoi: " . $e->getMessage());
        }
    } // end extractDoi
    private static function dumpReferences(): string
    {
        global $wpdb;
        $msg = "";
        $sql = "select * from " . self::$DatabaseReference;
        $recsRef = $wpdb->get_results($sql, ARRAY_A);
        $refCnt = 0;
        $msg .= "<table>";
        $msg .= rrwFormat::HeaderRow("Reference", "Type", "Content", "Title", "Lookup", "Citation");
        $color = rrwFormat::colorSwap();
        foreach ($recsRef as $recRef) {
            $refCnt++;
            if ($refCnt > 50) {
                break;
            }
            $reference = $recRef["reference"];
            $type = $recRef["type"];
            $content = $recRef["contents"];
            $title = $recRef["title"];
            $lookup = $recRef["lookUp"];
            $citation = $recRef["citation"];
            if (!empty($title))
                $reference = str_replace($title, "<em>$title</em>", $reference);
            if (!empty($isbn)) {
                $reference = str_replace($isbn, "<strong>$isbn</strong>", $reference);
            }
            if (stripos($reference, "doi") !== false) {
                $reference = str_replace('doi', '<strong>doi</strong>', $reference);
            }
            $color = rrwFormat::colorSwap($color);
            $msg .= rrwFormat::CellRow($color, $reference, $type, $content, $title, $lookup, $citation);
        } // end foreach
        $msg .= "</table>";
        return $msg;
    } // end dumpReferences
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
            $sql = "update " . self::$DatabaseNudges . " set permalink = '$permaLink' where id = $id;";
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
add_shortcode('creative_nudge_editOne', array('creative_edit', 'editOne'));
add_shortcode('creative_nudge_all', array('creative_nudges', 'displayAll'));
add_shortcode('creative_nudge_permalink', array('creative_nudges', 'permalink'));
add_shortcode('creative_nudge_search', array('creative_nudges', 'displaySearch'));
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
