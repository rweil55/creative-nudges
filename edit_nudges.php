<?php
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
            $sqlWhere = "SELECT FLOOR(RAND()*71)";
            $newId = $wpdb->get_var($sqlWhere);
            $sqlCard = "SELECT * FROM " . creative_nudges::$DatabaseNudges .
                " where id = $newId ";
        } else {
            $sqlWhere = creative_nudges::buildWhere($lookFor);
            $sqlCard = "SELECT * FROM " . creative_nudges::$DatabaseNudges .
                " $sqlWhere ORDER BY type, id ";
        }
        if ($debugCard) $msg .= " $eol SQL to find nudge: $sqlCard $eol";
        $cards = $wpdb->get_results($sqlCard, ARRAY_A);
        if (null == $cards || 0 == count($cards)) {
            $card = "select * from " . creative_nudges::$DatabaseNudges . " where id = 404 "; // no results
            $cards = $wpdb->get_results($card, ARRAY_A);
            $msg .= creative_nudges::buildImage($cards[0], "300px", "center");
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
        $msg .= creative_nudges::buildImage($card, "300px", "center");

        $thisUrl = $_SERVER["REQUEST_URI"];
        if (true == strpos($thisUrl, "home")) {
            if (creative_nudges::allowedToEdit()) {
                $msg .= "<br>Click card to comment";
            }
            return $msg;
        }
        $thisUrl = $_SERVER["REQUEST_URI"];
        if ($debugCard) $msg .= " this url is $thisUrl $eol";

        $msg .= "<br>" . self::displayComments($id);
        $msg .= self::displayForm($id);
        if (creative_nudges::allowedToEdit()) {
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
    private static function displayComments($cardId): string
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
        $sql = "SELECT *  FROM " . creative_nudges::$DatabaseComments .
            " WHERE cardId = $cardId ";
        if (creative_nudges::notAllowedToEdit())
            $sql .= " and displayState ='approved'  ";
        $sql .= " ORDER BY priority asc, created DESC ";
        if ($debugDisplay) $msg .= " $eol SQL to get approved comments: $sql ";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if ($debugDisplay) $msg .= " $eol No approved comments found.";
        // now get the count  of not approved comments
        $sqlPending = "SELECT displayState FROM " . creative_nudges::$DatabaseComments .
            " WHERE cardId = $cardId and not displayState = 'approved' ";
        if ($debugDisplay) $msg .= " $eol Checking for pending comments with query: $sqlPending ";
        $sqlPending = $wpdb->get_results($sqlPending, ARRAY_A);
        if ($debugDisplay) $msg .= rrwUtil::print_r($sqlPending, true, "Pending Comments");
        $cnt = $wpdb->num_rows;
        if (!empty($commentSuccessful)) {   // display the successfully saved message
            $msg .= $commentSuccessful;
        }
        if (0 != $cnt)
            $msg .= " $eol$eol There are $cnt comments pending, waiting for approval. $eol $eol";
        else
            $msg .= "$eol$eol Be the first to make a comment $eol $eol";
        // are there any approved comments to display?
        if (null == $results || 0 == count($results)) {
            return $msg;
        }
        $msg .= self::displayCommentRecords($results);
        return $msg;
    } // end function displayComments
    private static function displayCommentRecords($results): string
    {
        global $eol;
        $debugDisplay = rrwParam::isDebugMode("debugDisplay");
        $msg = "";

        $msg .= "$eol $eol<h3> Comments </h3> $eol";
        if ($debugDisplay) $msg .= rrwUtil::print_r($results, true, "Approved Comments");
        foreach ($results as $row) {
            $author = $row["author"];
            $comment = $row["comment"];
            $created = $row["created"];
            $displayState = $row["displayState"];
            $commentId = $row["id"];
            if (creative_nudges::allowedToEdit()) {
                $iconUrl = rrwUtil::$penIconUrl;
                $url = "https://creative-nudges.com/edit-Comment/?action=edit&id=$commentId";
                $msg .= "<a href='$url' >\n" .
                    "<img src='$iconUrl' alt='Edit' width='23' height='15' ></a>";
            }
            $msg .= "<a href='https://creative-nudges.com/edit-Comment/?action=list&author=$author' ><strong>$author</strong> </a>";
            if (creative_nudges::allowedToEdit()) {
                $msg .= " on <a href='https://creative-nudges.com/edit-Comment/?action=list&created=$created' >$created</a>
                                   <strong>$displayState</strong>$eol $comment$eol$eol";
            } else {
                $msg .= " on $created $eol ";
            }
            $msg .= "$comment$eol$eol";
        } // end foreach
        return $msg;
    } // end function displayComments
    /*
 * Edit an existing comment in the database
 * first display the selected comments

    */
    public static function editComment($attributes): string
    {
        global $eol, $errorBeg, $errorEnd;
        global $wpdbExtra;;
        $msg = "";
        $msg .= "into editComment #8 $eol";
        ini_set("display_errors", 1);
        error_reporting(E_ALL);

        $id = rrwParam::Integer("id", $attributes, -1);
        $author = rrwParam::String("author", $attributes, "");
        $created = rrwParam::String("created", $attributes, "");
        if (!empty($author)) {
            $sqlAuthor = "SELECT * FROM " . creative_nudges::$DatabaseComments . " where author = '$author'";
            $resultAuthor = $wpdbExtra->get_resultsA($sqlAuthor);
            $msg = self::displayCommentRecords($resultAuthor);
            return $msg;
        } elseif (!empty($created)) {
            $sqlCreated = "SELECT * FROM " . creative_nudges::$DatabaseComments . " where created = '$created'";
            $resultCreated = $wpdbExtra->get_resultsA($sqlCreated);
            $msg = self::displayCommentRecords($resultCreated);
            return $msg;
        } elseif ($id > 0) {
            $keyName = "id";
            $keyValue = $id;

            $msg .= "<h3> Edit Comment </h3> $eol";
            $msg .= "Editing comment where $keyName = '$keyValue' $eol";

            $table = new rrwDisplayTable();
            $msg .= $table->tableName(creative_nudges::$DatabaseComments);
            $msg .= $table->keyName("id");
            $msg .= $table->keyValue($id);
            $msg .= $table->noDelete(true);
            $msg .= $table->columnRead("Author Name", "author", 300, 0);
            $msg .= $table->columnRead("E-Mail", "email", 300, 0);
            $msg .= $table->columnRead("Comment", "comment", 3000, 1);
            $msg .= $table->columnRead("Created Date", "created", 50, 0);
            $msg .= $table->DropDownSelf("Display State", "displayState");
            $msg .= $table->debugEditData(true);
            $msg .= $table->sqlWhere(" where $keyName = '$keyValue' ");
            //$msg .= $table->debugList(true);;
            $msg .= $table->DoAction();

            $action = rrwParam::String("action", $attributes, "");
            if (empty($action))
                $action = "list";
            $table->DoAction();
            return $msg;
        } else {
            $msg .= " $errorBeg E#2280 Missing author or created date or comment ID $errorEnd ";
        }
        return $msg;
    } // end function editComment

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
        $msg .= "<form class='creative-nudges-input;' method='post' action=''> \n";
        $msg .= "<input type='hidden' name='nudge_id' id='nudge_id' value='$id' /> ";
        $msg .= "Name: <input class='creative-nudges-button'type='text' name='author' id='author' size='30' OnLostFocus='enableSubmitButton();' /> $eol";
        $msg .= "E-Mail Address: <input type='email' name='email' id='email' size='30' /> $eol";
        $msg .= "Comment: <br/> ";
        $fieldNameCount = 3000;
        $requestSize = 3000;
        $msg .= "<textarea name='Comment' id='Comment'  style='resize:both;' cols='80' rows='4' \n
                                    maxsize='$requestSize' onkeyup='countCharacters(this, \"$fieldNameCount\", $requestSize);'
                                    onLostFocus='enableSubmitButton();' $requestSize = 3000;> </textarea> $eol
                                    <span id='$fieldNameCount'> 0/$requestSize</span>$eol";
        $msg .= "<input type='checkbox' name='privacy' onLostFocus='enableSubmitButton();'/>
                    We collect your email address solely to respond to your comments and
                        communicate about this website. Your data will not be shared with third parties.
                        You can request its deletion at any time.
                        For full details, please read our <a href='https://creative-nudges.com/privacy-policy/' target='privacy' > Privacy Policy</a>.$eol";
        $msg .= "<input class='creative-nudges-input;'
                        style=' background: #a1dcf7; font-weight: bold; color: black; padding: 10px 20px; box-shadow: none;
                            border:none; border-radius: 10px; cursor: pointer;'
                        type='submit' name='submitButton' id='submitButton'value='Send Comment for Approval' /> $eol";
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
        $msg .= self::sendEmail($author, $nudge_id, $comment);
        return $msg;
    } // end function ProcessComment
    private static function sendEmail($author, $nudge_id, $comment)
    {
        $eol = "<br>\n";
        $msg = "";
        //print "sendEmail($cntBad, $cntTotal, $emailMsg)$eol";
        ini_set("display_errors", "1");
        $mail = setMailCredentials();
        $to1 = "creativeCommentt@royweil.com";
        $to2 = "creativeCommentt@maryshaw.org";
        $fromEmail = "no-reply@creative-nudges.com";
        // $mail->isHTML(true);
        $mail->setFrom($fromEmail, "Creative Nudges New Comment Notification");
        $mail->addAddress($to1);
        $mail->addAddress($to2);
        $mail->Subject = "Creative Nudges New Comment Notification ";
        $mail->Body = "A new comment was just submitted on the Creative Nudges website. \n" .
            "Please go to https://creative-nudges.com/pending to approve/defer. \n\n";

        $result = $mail->send();
        if ($result) {
            // $msg .= "Email sent to $to1 with subject" . $mail->Subject . $eol;
        } else {
            $msg .= "Email failed to send to $to1 with subject - Subject: " . $mail->Subject . $eol;
            $errorMsg = $mail->ErrorInfo;
            $msg .= "Error is $errorMsg $eol";
        }
        return $msg;
    } // end function <sendEmail>

} // end class creative_edit
