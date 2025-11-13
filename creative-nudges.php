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
require_once "rrw_util_inc.php";
require_once "rrwParam.php";

    error_reporting(E_ALL);
    ini_set("display_errors", true);
    global $eol, $errorBeg, $errorEnd;

    class creative_nudges {



	public static function displayAll() {
		global $eol, $wpdb;
		$msg = "";

		$sql = "select * from nodges order by type, id";
		$msg .= self::displaySql( $sql,true );
		return $msg;
	}
    private static function displaySql($sql, $includeText = false) {
        global $eol, $wpdb;
        $eol = "<br/>\n";
        $recsNudge = $wpdb->get_results( $sql, ARRAY_A );
		$msg = "";
$msg .="
<style>
.card{
width:200px !important;
height:auto !important;
}
</style>
";
        $msg .= "<table>";
        foreach ( $recsNudge as $nodge ) {
            $type = $nodge["type"];
            $nudge = $nodge["nudge"];
            $reference = $nodge["reference"];
            $keyId = $nodge["id"];
            $adjustCard = "200%";

            $nudgePng = "<img class='card' src='" . plugins_url( 'images/nudge-' . $keyId . '.png', __FILE__ ) . "' alt='$nudge' />";
            $refPng = "<img class='card', src='" . plugins_url( 'images/reference-' . $keyId . '.png', __FILE__ ) . "' alt='$reference' />";

            if ( $includeText )
                $msg .= rrwFormat::cellRow( $type, $nudgePng, $nudge, $reference, $refPng);
			else
                $msg .= "<tr><td>$nudgePng </td><td >$refPng</td></tr>";
        } // end foreach
        $msg .= "</table>";
        return $msg;
    } // end displayAll

 public static function displayRandom($attributes) {
		$keyId = rand( 1, 70 );
        $sql = "select * from nodges where id = $keyId";
		$msg = self::displaySql( $sql );
        return $msg;
    } // end display25

public static function setup1($attributes) {
		$directory = '../25 things cards/';  // up o
    $newestFile = '';
    $newestTime = 0;

    if (is_dir($directory)) {
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $directory . '/' . $file;
                if (is_file($filePath)) {
                    $fileTime = filemtime($filePath);
                    if ($fileTime > $newestTime) {
                        $newestTime = $fileTime;
                        $newestFile = $file;
                    }
                }
            }
        }

    }


		return "$newestFile";
    } // end setup

	public static function searchNudges( $attributes ) {
		$msg = "";
        $searchThing = rrwParam::String( 'SearchBox', $attributes );
		if ( ! empty( $searchThing ) ) {
			$sql = "select * from nodges where nudge like '%$searchThing%' or reference like '%$searchThing%' order by type, id";
			$msg = "sql = $sql<br/>";
			$msg = self::displaySql( $sql );
		}
        $msg .= "
        <form action='/search' >
<br />Enter a one word search term <br />
<input type='text' name='SearchBox' id='SearchBox'/><br/>
<input type='submit' value='Please find me a nudge(s)' >
</form>";
		return $msg;
	}
 } // end class


     add_shortcode('creative_nudge_all', array('creative_nudges', 'displayAll'));
    add_shortcode('creative_nudge_random', array('creative_nudges', 'displayRandom'));
    add_shortcode('creative_nudge_setup', array('creative_nudges', 'setup1'));
    add_shortcode('creative_nudge_search', array('creative_nudges', 'searchNudges'));