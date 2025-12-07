<?php
class creative_store
{
    public static function displayPage($attributes)
    {
        // Your code to display the store page goes here
        $msg = "";
        $imgSplayed = "<img style='width:50%; height:auto; 'src=\"https://creative-nudges.com/wp-content/uploads/2025/11/splayed-cardsR90.png\" />";
        $imgBooklet = "<img style='width:80%; height:auto;  border: 2px solid #ccc;; ' src=\"https://creative-nudges.com/wp-content/uploads/2025/11/25-things-refs-v4-Title-Page-scaled.png\" />";
        $imgCard1 = creative_nudges::getRandomImage();
        $imgCard2 = creative_nudges::getRandomImage();
        //$imgCard =  "<img style='width:100%' src=\"" . $image . "\" />";
        //$imgCard  "<img style='width:100%' src=\"https://creative-nudges.com/wp-content/plugins/creative-nudges/images-combined/combined-57.png\" />";
        $button1 = '<button>Add to Cart</button>';
        $button2 = '<button>Add to Cart</button>';
        $button3 = '<button>Add to Cart</button>';
        $button4 = '<button>Buy Now</button>';
        $button5 = '<button>Subscribe</button>';
        //
        $msg .= "
<style>
.card{
width:100% !important;
height:auto !important;
margin-right:10px;
}
</style>
";
        $msg .= "<h1>Welcome to the Creative Nudges&trade;Store</h1>
        	<table>
		<tr>
			<td width=\"50%\" style='margin:10px'>
				<table>
                    <tr >
                        <td colspan=\"3\" >$imgSplayed &nbsp;<br>
                        A deck of cards, each with a “Nudge” on the blue/green side, and more explanation on the yellow side.
                        70 nudge cards in all, 2 cards with instructions.<hr></td>
                    </tr>
                    <tr>
                        <td style='padding-right:10px'> 1 to 5 decks at $23.99/ deck includes sales tax, shipping and handling<br />
                            $button1</td>
                        <td> more than 5 decks $19.20/ deck (20 % discount) includes sales tax, shipping and handling<br />
                            $button2</td>
                        <td style='padding-left:10px'>	more than 10 decks at $14.40/ deck +shipping (40% commercial discount, sales tax form required)<br />
                            $button3</td>
                    </tr>
                    </table>
                    &nbsp;
			</td>
            <td style='padding:10px'>	$imgBooklet<br />An 8.5 in X 5.5, 12 page booklet. Contains some explanation, and print out of the 70 nudge cards.
                        See ,a> href='https:/completeContents'>complete contents'</a> $1.50/booklet.<br>
                         $button4</td>
            <td style='padding:10px'>	$imgCard1<br>One card mailed randomly over a 6 month period. $70/mailing address. includes sales tax, shipping and handling<br />
                        $button4</td>
            <td style='padding:10px'>	$imgCard2<br>One card e-mailed each day for 70 days. $10/e-mail address. <br />
                        $button5</td>
		</tr>
	</table>
    ";
        return "$msg";
    } // end displayPage
    public static function displayCategories($attributes)
    {
        global $wpdb;
        // Your code to display categories goes here
        $msg = "<h2>Card Categories</h2>";
        ini_set('display_errors', 1);


        $sql = "select permalink , nudge, reference, id from " . creative_nudges::$DatabaseNudges . " order by id, nudge;";
        $records = $wpdb->get_results($sql, ARRAY_A);
        $msg .= "\n <table>\n";
        $color = rrwFormat::colorSwap();
        foreach ($records as $rec) {
            $nudge = $rec["nudge"];
            if (empty($nudge))
                continue;
            $id = $rec['id'];
            $permalink  = $rec['permalink'];
            $color = rrwFormat::colorSwap($color);
            $msg .= "<tr style='background-color:$color' >\n";
            $msg .= "  <td style='width:130px' >" . $permalink  . "</td>\n";
            $msg .= "  <td>" . $nudge . "</td>\n";
            $msg .= "  <td>" . $rec['reference'] . "</td>\n";
            $msg .= "  <td>$id</td>\n";
            $msg .= "</tr>\n";
        }
        $msg .= "</table>\n";
        print "
  <script>
  function add2dropDown( id ) {
                var promptText = \"Enter the new text to add to the bottom of the list \" + id.name ;
                alert(id.name);
                var newText = prompt(promptText, '');
                var radioNew = document.createElement('radio');
                radioNew.text = newText;
                radioNew.value = newText;
                id.add(radioNew);
            }
    </script>
    ";
        return $msg;
    } // end displayCategories
}
