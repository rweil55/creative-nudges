<?php
class isbn_doi
{
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
}
