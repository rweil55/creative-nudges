<?php
require_once "display_tables_class.php";
class creative_edit
{
    public static function editOne($attributes)
    {
        global $wpdbExtra;
        global $eol, $errorBeg, $errorEnd;

        $msg = "";
        //$msg .= "into editOne #3 $eol";
        //
        $id = rrwParam::number("update_value", $attributes, -1);
        if (0 > $id) {
            $id = rrwParam::number("id", $attributes, -1);
            if (0 > $id) {
                $id = floor(rand(0, 70));
            }
        }
        $msg .= "found id = $id <a href='https://creative-nudges.com/edit/?nohead=1&id=" . ($id + 1) . "'> Next</a> $eol";


        $msg .= "<img src='https://creative-nudges.com/wp-content/plugins/creative-nudges/images-combined/combined-$id.png'
                        style='float:center;margin:10px; width:300px ' /><br/>";

        $table = new rrwDisplayTable();
        $msg .= $table->tableName(creative_nudges::$DatabaseNudges);
        $msg .= $table->keyName("id");
        $msg .= $table->keyValue($id);
        $msg .= $table->noDelete(true);
        $msg .= $table->columnRead("Nudge", "nudge", 300, 1);
        $msg .= $table->columnRead("Explanation", "reference", 2000, 1);
        $msg .= $table->columnRead("Permalink", "permalink", 50, 15);
        $msg .= $table->DropDownSelf("Design Group", "grouping");

        $msg .= $table->DropDownSelf("Design Moves", "move");
        $msg .= $table->DropDownSelf("Patterns", "pattern");
        $msg .= $table->columnRead("tags", "tags", 300, 15);
        $msg .= $table->DoAction();
        return $msg;
    }
}
