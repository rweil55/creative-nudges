<?php
require_once "display_tables_class.php";
class creative_edit
{
    public static function author($attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;

        $msg = "";

        $table = new rrwDisplayTable();
        $msg .= $table->tableName(creative_nudges::$DatabaseAuthors);
        $msg .= $table->keyName("name");
        $msg .= $table->keyValue("name");
        $msg .= $table->columnRead("Author Name", "name", 300, 0);
        $msg .= $table->columnRead("Author Priority", "defaultpriority", 10, 0);
        //
        $action = rrwParam::String("action", $attributes, "list");
        if (!empty($action))
            $action = "list";
        $msg .= $table->DoAction($action);
        return $msg;
    }

    public static function editOne($attributes)
    {
        global $eol, $errorBeg, $errorEnd;
        global $commentSuccessful;
        $msg = "";
        $debugEditOne = rrwParam::isDebugMode("debugEditOne");
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        try {
            if ($debugEditOne) $msg .= "into editOne #3 $eol";
            $submitButton = rrwParam::String("submitButton", $attributes, "");
            $commentSuccessful = "";
            if (! empty($submitButton)) {
                if ($debugEditOne) $msg .= rrwUtil::print_r($attributes, true, " before Processing comment submission");
                $msg .= self::ProcessComment($attributes);
            }
            //
            if ($debugEditOne)  $msg .= rrwUtil::print_r($attributes, true, "Attributes for displayLookFor before looking for searchBox");
            $searchBox = rrwParam::String("SearchBox", $attributes, "");
            if (empty($searchBox))
                $searchBox = rrwParam::String("n", $attributes, "");
            if (empty($searchBox))
                $searchBox = rrwParam::String("id", $attributes, "");
            if (empty($searchBox))
                $searchBox = rrwParam::String("update_value", $attributes, "");
            if ($debugEditOne) $msg .= " calling displayLookFor($searchBox) $eol";
            $msg .= self::displayLookFor($searchBox);
        } catch (Exception $e) {
            $msg .= rrwFormat::backtrace("$errorBeg Exception in editOne: " . $e->getMessage() . " $errorEnd ");
        }
        return $msg;
    }
    public static function EditCard($attributes): string
    {
        $msg = "";
        $id = rrwParam::Integer("id", $attributes, -1);
        if ($id < 0) {
            $msg .= " Invalid or missing nudge ID. ";
            return $msg;
        }
        $table = new rrwDisplayTable();
        $msg .= $table->tableName(creative_nudges::$DatabaseNudges);
        $msg .= $table->keyName("id");
        $msg .= $table->keyValue($id);
        $msg .= $table->noDelete(true);
        $msg .= $table->columnRead("Nudge", "nudge", 300, 1);
        $msg .= $table->columnRead("Explanation", "reference", 2000, 1);
        $msg .= $table->columnRead("PermalinkAuto", "permalinkAuto", 50, 2);
        $msg .= $table->columnRead("Permalink", "permalink", 50, 15);
        $msg .= $table->DropDownSelf("Card Category Group", "grouping");
        $msg .= $table->DropDownSelf("Design Moves", "move");
        $msg .= $table->DropDownSelf("Patterns", "pattern");
        $msg .= $table->columnRead("tags", "tags", 300, 15);
        $msg .= $table->DoAction();

        $action = rrwParam::String("action", $attributes, "");
        if ($action == "update") {
            $attributes = array();
        }
        return $msg;
    }
    public static function displayLookFor($lookFor)
    {
        $msg = "";
        global $wpdb;

        global $eol, $errorBeg, $errorEnd;
        $debugCard = rrwParam::isDebugMode("debugCard");
        $msg = "";

        if ($debugCard) $msg .= "into displayLookFor #2 with lookFor = '$lookFor' $eol";
        if (empty($lookFor)) {
            $msg .= " $errorBeg E#900 Missing search term $errorEnd ";
            return $msg;
        }
        $sqlWhere = creative_nudges::buildWhere($lookFor);
        $sqlCard = "SELECT * FROM " . creative_nudges::$DatabaseNudges .
            " $sqlWhere ORDER BY type, id ";
        if ($debugCard) $msg .= " $eol SQL to find nudge: $sqlCard $eol";
        $cards = $wpdb->get_results($sqlCard, ARRAY_A);
        if (null == $cards || 0 == count($cards)) {
            $msg .= " $errorBeg e#901 No matching nudge found for search '$lookFor' $errorEnd  $sqlCard $eol";
            return $msg;
        }
        if ($debugCard) $msg .= " displayLookFor $sqlCard  $eol";
        if (count($cards) > 1) {
            foreach ($cards as $card) {
                $msg .= creative_nudges::buildImage($card, "300px", "center");
            }
            return $msg;
        }
        $card = $cards[0];
        $id = $card["id"];

        if ($debugCard) $msg .= "found id = " . $id . $eol;
        $msg .= creative_nudges::buildImage($card, "400px", "center");

        $msg .= self::displayComments($id);
        $msg .= self::displayForm($id);
        if (is_user_logged_in()) {
            unset($_POST["submitButton"]);
            $msg .= self::editCard(array("id" => $id));
        }

        return $msg;
    }  // end function displayLookFor
    /*
    private static function get_id($parmaLink): int
    {
        global $eol;
        global $wpdbExtra, $wpdb;

        $msg = "";
        $msg .= "into editCard #4 $eol";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        //
        $sql = $wpdb->prepare(
            "SELECT id FROM " . creative_nudges::$DatabaseNudges .
                " WHERE nudge LIKE %s OR reference LIKE %s or permalinkAuto like %s or permalink like %s ORDER BY type, id  ",
            $parmaLink,
            $parmaLink,
            $parmaLink,
            $parmaLink
        );
        $id = $wpdb->get_var($sql);
        if (null == $id || empty($id))
            return -1;
        return intval($id);
    } // end function get_id
    */
    private static function displayComments($id): string
    {
        global $eol;
        global  $wpdb;
        global $commentSuccessful;      // se in ProcessComment routine

        $msg = "";
        // $msg .= "into displayComments #5 $eol";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        $debugDisplay = rrwParam::isDebugMode("debugDisplay");
        //
        $sql = $wpdb->prepare(
            "SELECT author, comment, created FROM " . creative_nudges::$DatabaseComments .
                " WHERE cardId = %d and not status='pending' ORDER BY priority asc, created DESC ",
            $id
        );
        $results = $wpdb->get_results($sql, ARRAY_A);
        if (null == $results || 0 == count($results)) {
            if ($debugDisplay) $msg .= " $eol No approved comments found.";
            $sqlPending = "SELECT status FROM " . creative_nudges::$DatabaseComments .
                " WHERE cardId = $id and status = 'pending' ";
            if ($debugDisplay) $msg .= " $eol Checking for pending comments with query: $sqlPending ";
            $sqlPending = $wpdb->get_results($sqlPending, ARRAY_A);
            if ($debugDisplay) $msg .= rrwUtil::print_r($sqlPending, true, "Pending Comments");
            $cnt = $wpdb->num_rows;
            if (!empty($commentSuccessful)) {
                $msg .= $commentSuccessful;
            }
            if (0 != $cnt)
                $msg .= " $eol$eol There are $cnt comments pending, waiting for approval. $eol $eol";
            else
                $msg .= "$eol$eol Be the first to make a comment $eol $eol";
            return $msg;
        }
        $msg .= "<h3> Comments </h3> $eol";
        if ($debugDisplay) $msg .= rrwUtil::print_r($results, true, "Approved Comments");
        foreach ($results as $row) {
            $author = $row["author"];
            $comment = $row["comment"];
            $created = $row["created"];
            $msg .= "<b>$author</b> on $created said: $eol $comment $eol";
        } // end foreach
        return $msg;
    } // end function displayComments

    private static function displayForm($id): string
    {
        global $eol;
        global $wpdbExtra, $wpdb;

        $msg = "";
        //   $msg .= "into displayForm #6 $eol";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        //
        $msg .= "<script>
            function enableSubmitButton() {
                alert('got here');
                var author = document.getElementsByName('author')[0].value;
                var comment = document.getElementsByName('Comment')[0].value;
                var privacy = document.getElementsByName('privacy')[0].checked;
                var submitButton = document.querySelector('input[type=\"submit\"]');
                alert('Author: ' + author + ', Comment: ' + comment + ', Privacy: ' + privacy);
                if (author.trim() !== '' && comment.trim() !== '' && privacy) {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }
            }
        </script>";
        $msg .= "<h3> Comment on the above nudge</h3> $eol";
        $msg .= "<form method='post' action=''> \n";
        $msg .= "<input type='hidden' name='nudge_id' id='nudge_id' value='$id' /> ";
        $msg .= "Name: <input type='text' name='author' id='author' size='30' OnLostFocus='enableSubmitButton();' /> $eol";
        $msg .= "E-Mail Address: <input type='email' name='email' id='email' size='30' /> $eol";
        $msg .= "Comment: <br/> ";
        $fieldNameCount = 3000;
        $requestSize = 3000;
        $msg .= "<textarea name='Comment' id='Comment'  style='resize:both;' cols='200' rows='4' \n
                                    maxsize='$requestSize' onkeyup='countCharacters(this, \"$fieldNameCount\", $requestSize);'
                                    onLostFocus='enableSubmitButton();' $requestSize = 3000;> </textarea> $eol
                                    <span id='$fieldNameCount'> 0/$requestSize</span>$eol";
        $msg .= "<input type='checkbox' name='privacy' onLostFocus='enableSubmitButton();'/>
                    We collect your email address solely to respond to your comments and
                        communicate about this website. Your data will not be shared with third parties.
                        You can request its deletion at any time.
                        For full details, please read our <a href='https://creative-nudges.com/privacy-policy/' target='privacy' > Privacy Policy</a>.$eol";
        $msg .= "<input type='submit' name='submitButton' id='submitButton'value='Send Comment for Approval' /> $eol";
        $msg .= "</form> $eol";

        return $msg;
    } // end function displayForm

    private static function ProcessComment(&$attributes): string
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra, $wpdb;
        global $commentSuccessful;      // passed upward for later display

        $msg = "";
        $debugProcessing = rrwParam::isDebugMode("debugProcessing");
        if ($debugProcessing) $msg .= "into ProcessComment #7 $eol";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);
        //
        if ($debugProcessing) $msg .= rrwUtil::print_r($_POST, true, "post Comment Submission Data");
        $author = rrwParam::String("author", $attributes, "");
        $comment = rrwParam::String("Comment", $attributes, "");
        $email = rrwParam::String("email", $attributes, "");
        $privacy = rrwParam::Boolean("privacy", $attributes, false);
        $nudge_id = rrwParam::String("nudge_id", $attributes, "-5");
        if ($debugProcessing) $msg .= "nudge_id,id = '$nudge_id' $eol";
        if (empty($author) || empty($comment) || !$privacy) {
            $msg .= " $errorBeg Missing required fields for comment submission. Please fill in all fields and agree to the privacy policy. $errorEnd ";
            return $msg;
        }
        $priority = 75;
        $sqlAuthor = $wpdb->prepare(
            "SELECT defaultPriority FROM " . creative_nudges::$DatabaseAuthors .
                " WHERE name = %s ",
            $author
        );
        $resultAuthor = $wpdb->get_var($sqlAuthor, ARRAY_A);
        if (null != $resultAuthor) {
            $priority = $resultAuthor["defaultPriority"];
        }

        $dateInsert = array(
            'cardId' => $nudge_id,
            'author' => sanitize_text_field($author),
            'email' => sanitize_email($email),
            'comment' => sanitize_textarea_field($comment),
            'created' => current_time('mysql'),
            'priority' => $priority,
        );
        if ($debugProcessing) $msg .= rrwUtil::print_r($dateInsert, true, "Inserting Comment Data");
        $inserted = $wpdb->insert(creative_nudges::$DatabaseComments, $dateInsert);
        if (false === $inserted) {
            $msg .= " $errorBeg Error inserting comment into database. Please try again later. $errorEnd ";
            return $msg;
        }
        $commentSuccessful = "$eol$eol Thank you for your comment! It has been submitted successfully. $eol ";
        $attributes = array("id"  => "$nudge_id");
        if ($debugProcessing) $msg .= rrwUtil::print_r($attributes, true, "Attributes for displayLookFor after comment submission");
        return $msg;
    } // end function ProcessComment

} // end class creative_edit
