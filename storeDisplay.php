<?php
class creative_store {
    public static function displayPage($attributes) {
        // Your code to display the store page goes here
        $msg = "";
        $imgSplayed = "<img style='width:50%; height:auto; 'src=\"https://creative-nudges.com/wp-content/uploads/2025/11/splayed-cardsR90.png\" />";
     	$imgBooklet = "<img style='width:80%; height:auto;  border: 2px solid #ccc;; ' src=\"https://creative-nudges.com/wp-content/uploads/2025/11/25-things-refs-v4-Title-Page-scaled.png\" />";
        $imgCard1 = creative_nudges::getRandomImage();
        $imgCard2 = creative_nudges::getRandomImage();
        //$imgCard =  "<img style='width:100%' src=\"" . $image . "\" />";
        //$imgCard  "<img style='width:100%' src=\"https://creative-nudges.com/wp-content/plugins/creative-nudges/images-combined/combined-57.png\" />";
	   $button1= '<button>Add to Cart</button>';
        $button2= '<button>Add to Cart</button>';
        $button3= '<button>Add to Cart</button>';
        $button4= '<button>Buy Now</button>';
        $button5= '<button>Subscribe</button>';
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
                        A deck of cards, each with a “Nudge” on the blue side, and more explanation on the yellow side.
                        70 nudge cards in all, three blank to create your specialized nudges.<hr></td>
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
    }
}