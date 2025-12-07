<?php
require_once "display_tables_class.php";
class creative_pending
{
    public static function pending($attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;
        try {
            ini_set("display_errors", true);
            error_reporting(E_ALL);
            $msg = "";
            $debugPending = rrwParam::isDebugMode("debugPending", true);
            $sqlPending = "SELECT * FROM " . creative_nudges::$DatabaseComments . " WHERE displayState='pending' ORDER BY created DESC";
            if ($debugPending) $msg .= "SQL Pending: " . $sqlPending . $eol;
            $recPending = $wpdbExtra->get_results($sqlPending, ARRAY_A);
            if (empty($recPending) || 0 == $wpdbExtra->num_rows) {
                $msg .= "<h3>No Pending Comments to approve</h3>" . $eol;
                return $msg;
            }
            $cardId = $recPending[0]["cardId"];
            $sqlCard = "SELECT * FROM " . creative_nudges::$DatabaseNudges . " WHERE id='" . $cardId . "'";
            if ($debugPending) $msg .= "SQL Card: " . $sqlCard . $eol;
            $recCard = $wpdbExtra->get_results($sqlCard, ARRAY_A);
            if (empty($recCard) || 1 != $wpdbExtra->num_rows) {
                $msg .= "<h3>Unable to find Nudge Card for Pending Comment ID: " . $recPending[0]["id"] . "</h3>" . $eol;
                $msg .= rrwUtil::print_r($recCard, true, "The bad Pending Comment Record");
                return $msg;
            }
            $msg .= creative_nudges::buildImage($recCard[0], "400px", "center");
            $msg .= "$eol$eol";
            $id = $recPending[0]["id"];
            if ($debugPending) $msg .= "Pending Comment ID: " . $id . $eol;
            $table = new rrwDisplayTable();
            $msg .= $table->tableName(creative_nudges::$DatabaseComments);
            $msg .= $table->keyName("id");
            $msg .= $table->keyValue($id);
            $msg .= $table->columnRead("Author Name", "author", 50, 0);
            $msg .= $table->columnRead("E-Mail", "email", 120, 0);
            $msg .= $table->columnRead("priority", "priority", 10, 0);
            $msg .= $table->columnRead("Entered Date", "created", 20, 1);
            $msg .= $table->columnRead("Comment", "comment", 120, 0);
            $msg .= $table->DropDownSelf("Status", "displayState");
            //
            $action = rrwParam::String("action", $attributes);
            if ($debugPending) $msg .= " before Action: " . $action . $eol;
            if (empty($action))
                $action = "edit";
            if ($debugPending) $msg .= "about to Action: " . $action . $eol;
            $msg .= $table->DoAction($action);
        } catch (Exception $e) {
            $msg .= $errorBeg . "Error in pending(): " . $e->getMessage() . $errorEnd;
        }
        return $msg;
    }
} // end class creative_edit
