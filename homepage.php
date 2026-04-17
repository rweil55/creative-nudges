<?php
class homepage
{
    public static function displayHomePage($attributes)
    {
        $msg = "";

        ini_set("display_errors", true);
        error_reporting(E_ALL);
        $msg .= "
<style>
.creative-nudges-header2 {
    font-size: larger;
    font-weight: bold;
    font-style: italic;
    }
.creative-nudges-button {
        background: #a1dcf7;
        font-weight: bold;
        color: black;
        padding: 10px 20px;
        border: none;
        box-shadow: none;
        border-radius: 10px;
        cursor: pointer;
    }
.creative-nudges-link {
        background: #a1dcf7;
        font-weight: bold;
        color: black;
        padding: 10px 20px;
        border: none;
        box-shadow: none;
        border-radius: 10px;
        cursor: pointer;
    }
.creative-nudges-button a {
        text-decoration: none;
        font-weight: bold;
        color: black;
   }
.creative-nudges-input {
        background: #a1dcf7;
        font-weight: bold;
        color: black;
        padding: 10px 20px;
        box-shadow: none;
        border: none;
        border-radius: 10px;
        cursor: pointer;
    }
.creative-nudge-menu {
    float: right;
    align: right;
}
    .creative-nudges-wp-button {
    height=px; important;
    width=160px; important;
    }


</style>
    ";
        $lastPercent = 40;
        $pagename = $_SERVER['REQUEST_URI'];
        if (true == strpos($pagename, "store"))
            $homePageText = new storePageTexts();
        else
            $homePageText = new homePageTexts();
        $msg .= "<table><tr>
<td width=$lastPercent" . "% >";
        $msg .= $homePageText::c1r1();
        $msg .= self::td($lastPercent, 1, 1);
        $msg .= $homePageText::c2r1();
        $msg .= self::td($lastPercent, 2, 1);
        $msg .= $homePageText::c1r2();
        $msg .= self::td($lastPercent, 1, 2);
        $msg .= $homePageText::c2r2();
        $msg .= self::td($lastPercent, 2, 2);
        $msg .= $homePageText::c1r3();
        $msg .= self::td($lastPercent, 1, 3);
        $msg .= $homePageText::c2r3();
        $msg .= self::td($lastPercent, 2, 3);
        $msg .= $homePageText::c1r4();
        $msg .= self::td($lastPercent, 1, 4);
        $msg .= $homePageText::c2r4();
        $msg .= "</td></tr></table>
        <tr><hr style='border:3px solid #365c82;' /></tr>
        </table>";
        return $msg;
    }
    public  static function td(&$lastPercent, $col, $row)
    {
        $msg = "";
        $msg .= "</td>";
        if (2 == $col) {
            $msg .= "</tr></table><table><tr>\n";
            $newPercent = $lastPercent;
        } else {
            $newPercent = 100 - $lastPercent;
        }
        $style = "style='border:1px solid black; border-collapse: collapse;'";

        $style = "";
        $msg .= "<td width='$newPercent" . "%' $style >\n";
        $lastPercent = $newPercent;
        return $msg;
    } // End of td function
} // End of homepage class

class homePageTexts
{
    public static $imageScale = "<img width=65% height=30% ";
    public static $eol = "<br>\n";

    public static function c1r1()
    {
        $msg =  "";
        $msg =  self::$imageScale . "src='https://creative-nudges.com/wp-content/uploads/2026/04/ULblob-box.png' />" . self::$eol;
        return $msg;
    }
    public static function c1r2()
    {
        $msg =  "";
        $msg .= "<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2' >Insightful nudges for creativity and design</p>
                    <p>Each card has an insight on the front, which is elaborated on the back with more detail and an attribution.</p>
                    <p>Nudges include observations about the nature of the world and advice on what to do in practice.</p>
                    <p>Nudges offer advice on design, engineering, systems & society, research, abstraction & specification, and education.</p>
                    <p>They draw heavily on software system design but apply to a wide spectrum of design and educational settings.</p>
                </p>
            <table><tr><td>
                <button class='creative-nudges-button' ><a class='creative-nudges-link' href='/home/'>Show a different card</a></button>
            </td><td>
                <form class='creative-nudges-input;' action='/home/' onlostfocus='this.submit();' method='get'>
                    <input class='creative-nudges-input;'
                        style=' background: #a1dcf7; font-weight: bold; color: black; padding: 10px 20px; box-shadow: none;
                            border:none; border-radius: 10px; cursor: pointer;'
                        type='submit' value='Find me a nudge(s)' />
                    <br />
                    <input class='creative-nudges-input;'type='text' style='width:198px;' name='SearchBox' id='SearchBox' placeholder='Enter a one word search term' />
                </form>
            </td></tr></table>
" .
            self::$eol;
        return $msg;
    }
    public static function c1r3()
    {
        $msg =  "";
        $msg = self::$imageScale . " src='https://creative-nudges.com/wp-content/uploads/2026/04/Lblob-fan.png' />" . self::$eol;
        return $msg;
    }
    public static function c1r4()
    {
        $msg =  "";
        $msg = "<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2' >More details available</p>
                    <p>The explanations on the backs of the cards contain citations to the sources that influenced
                        my understanding of the nudges. In many cases I recall the events that triggered the insight
                        behind the nudge, and these nudges cite the source.
                        In other cases, more recent research has resonated strongly with the nudge, and these are also cited.
                        Sometimes, though, the nudges express opinions that formed gradually over time,
                        so there some stuff I just figured out.<p>
                    <p>The full bibliography that translates those citations to full references is available. <p>
                    <p>A booklet with the references and images of all the cards is also available.</p>
                <table><tr><td>
                    <button class='creative-nudges-button' ><a class='creative-nudges-link' href= '/references/'>Show the references</a></button>
                </td><td>
                    <button class='creative-nudges-button' ><a class='creative-nudges-link' href='/booklet/'>Print the booklet</a></button> &nbsp;
                    <button class='creative-nudges-button' ><a class='creative-nudges-link' href='/store/'>Add to Cart</a></button>
                </td></tr></table>";
        return $msg;
    }


    public static function c2r1()
    {
        $msg =  "";
        $rrw_trail_menuText = wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => 'nav-menu menucolor',
            'echo' => false
        ));
        $rrw_trail_menuText = str_replace("container", "container creative-nudge-menu", $rrw_trail_menuText);
        $msg = " <p >$rrw_trail_menuText</p>
        <p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2'>Sometimes you get stuck in a creative rut, <br>and it takes a li’l nudge to get out of the rut, jus’ sayin‘</p>
                    <p>This deck of cards on professional playing-card stock offers 70 insights into design, creativity, and software.</p>
                    <p>These insights prompt you to adopt new perspectives, identify dissonance, look for structure, and explore different approaches.</p>
                    <p>Drawn from Mary Shaw’s decades of experience in software design and engineering, they provide thoughtful guidance to overcome creative blocks and discover fresh perspectives.</p>
                    <button  class='creative-nudges-button' ><a class='creative-nudges-link' href='/store/'>Buy The Cards</a></button>
                </p>";
        return $msg;
    }
    public static function c2r2()
    {
        $msg =  "";
        $searchTerm = rrwParam::String("SearchBox");
        $msg = creative_edit::displayLookFor($searchTerm);
        return $msg;
    }
    public static function c2r3()
    {
        $msg =  "";
        $msg = "<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2'>Useful in many settings</p>
                    <p>These creativity nudges capture some of the insights that I offer in technical discussions
                    to remind people to take a fresh point of view, identify dissonance, look for structure, and more.
                    They capture what should be common sense about creativity and design, especially in software,
                    but often isn’t recognized as such.</p>
                <p>You can use the cards for personal stimulation or to help structure group discussions.
                    There's probably a game to be had as well,</p>
                    <button  class='creative-nudges-button' ><a class='creative-nudges-link' href='/how-to-use/' >How can I use these cards</a></button>
                </p>";

        return $msg;
    }
    public static function c2r4()
    {
        $msg =  "";
        $msg = self::$imageScale . " style='float:right; ' src='https://creative-nudges.com/wp-content/uploads/2026/04/LRblob-book.png' />" . self::$eol;
        return $msg;
    }
} // End of homePageTexts class

class storePageTexts
{


    public static $imageScale = "<img width=65% height=30% ";
    public static $eol = "<br>\n";

    public static function c1r1()
    {
        $msg =  "";
        $msg =  self::$imageScale . "src='https://creative-nudges.com/wp-content/uploads/2026/04/ULblob-boxfan.png' />" . self::$eol;
        return $msg;
    }
    public static function c1r2()
    {
        $msg =  "";
        $msg .= do_shortcode("<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2'>Order the explanatory booklet</p>
                    <p>An 8.5 in X 5.5, 20 page booklet. Contains some explanation, the bibliography that explains the citations, and printout of the 70 nudge cards.
                        $5.00 includes shipping and handling.</p>
                    <table><tr><td>
                        <button  class='creative-nudges-button' ><a class='creative-nudges-link' href='/booklet/'> Print the booklet (free)</a></button>
                    </td><td class='creative-nudges-wp-button'>
                        [wp_paypal button='cart' name='20 page booklet' amount='5.00'
                                button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png'] </td>
                    </td></tr></table>
                </p>");
        return $msg;
    }
    public static function c1r3()
    {
        $msg =  "";
        $msg = self::$imageScale . " src='https://creative-nudges.com/wp-content/uploads/2026/04/Lblob-fan.png' />" . self::$eol;
        return $msg;
    }
    public static function c1r4()
    {
        $msg =  "";
        $msg = "<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2' >What else should we sell</p>
                    <p>Hats, T-Shirts, Mugs, Posters, mouse pads,Shampoo, skin care, Sneakers, and boots  </p>
                    <p>Soap that as it wears done reveals a new nudge</p>
                    <p>Exorbitantly over-priced custom t-shirts/poly with the front and back of your favorite card on the front and back</p>
                    <p>Other stuff that we haven’t thought of yet</p>
                    <p><button  class='creative-nudges-button' ><a class='creative-nudges-link' href='/contact/'>Contact us with your ideas</a></button>
                    </p>
                </p>";
        return $msg;
    }


    public static function c2r1()
    {
        $msg =  "";
        $rrw_trail_menuText = wp_nav_menu(array(
            'theme_location' => 'primary',
            'menu_class' => 'nav-menu menucolor',
            'echo' => false
        ));
        $rrw_trail_menuText = str_replace("container", "container creative-nudge-menu", $rrw_trail_menuText);
        $msg = " <p >$rrw_trail_menuText </p>";
        $msg .= do_shortcode(
            "
            <style>
            .creative-nudges-store-amounts {
                padding-right: 30px;
                padding-left: 20px;
                margin-right: 30px;
                vertical-align: top;
            }
            </style>

        <p style='padding:20px; vertical-align:center;'>
            <p class='creative-nudges-header2' >Order full decks for US delivery</p>
            <p>Each deck has a full set of 70 creative nudges</p>
            <table>
            <tr class='creative-nudges-store-amounts;' >
                <td class='creative-nudges-store-amounts'>&nbsp; </td>
                <td><br>1-5 decks, @19.99/deck,includes tax, US shipping, handling </td>
                <td class='creative-nudges-wp-button' >[wp_paypal button='cart' name='1 deck' amount='19.99'
                        button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png'] </td>
            </tr><trclass='creative-nudges-store-amounts;'><td>&nbsp; </td></tr><tr >
                <td class='creative-nudges-store-amounts'>&nbsp; </td>
                <td ><br>6-9 decks, $15.99/deck 20% discount includes tax, US shipping, handling</td>
                <td class='creative-nudges-wp-button'>[wp_paypal button='cart' name='6-9 decks' amount='19.99'
                        button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png'] </td>
            </tr><tr class='creative-nudges-store-amounts;'><td>&nbsp; </td></tr><tr >
                <td class='creative-nudges-store-amounts'>&nbsp; </td>
               <td><br>10 or more decks, $11.99/deck + shipping, 40% commercial/non-pofit discount, sales tax form required </td>
                <td class='creative-nudges-wp-button'>[wp_paypal button='cart' name='10 or more decks' amount='11.99'
                        button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png' ] </td>
            </tr><tr><td>&nbsp; </td></tr><tr >
            <table>
            <p>Sorry, this is for shipping to US postal addresses only.  We also sell on
                <a href='https://www.amazon.com/s?k=0972732470'>Amazon</a>, which is better able to
                handle the complexity of international shipping.
                </p>
        </p>"
        );
        return $msg;
    }
    public static function c2r2()
    {
        $msg =  "";
        $msg .= self::$imageScale . " style='float:right; ' src='https://creative-nudges.com/wp-content/uploads/2026/04/Rblob-book.png' />" . self::$eol;
        return $msg;
    }
    public static function c2r3()
    {
        $msg =  "";
        $msg .= do_shortcode(
            "<p style='padding:10px; vertical-align:center;'>
                    <p class='creative-nudges-header2'>Get push notifications</p>
                <table><tr' >
                    <td>A different card from the deck will be emailed overnight for 70 days. The arrival time depends on the
                        vagaries of the e-mail system.$10/e-mail address includes sales tax, emailing, and handling.</td>
                    <td  width='160px' >[wp_paypal button='cart' name='email every night' amount='10.00'
                                    button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png' ] </td>
                            </tr><tr>
                <td>&nbsp; </td>
                <td>&nbsp; </td></tr><tr>
                    <td>A different card from the deck is mailed every 2-3 days, with arrival time depending on the
                        vagaries of the post office, over 6 months. $70/mailing address includes sales tax, shipping, and handling></td>
                    <td class='creative-nudges-wp-button'  >[wp_paypal button='cart' name='postal mail every 2-3 days' amount='70.00'
                            button_image ='https://creative-nudges.com/wp-content/uploads/2026/04/cartButton.png'] </td>
                </tr></table>
        </p>"
        );
        return $msg;
    }
    public static function c2r4()
    {
        $msg =  "";
        $msg = self::$imageScale . " style='float:right; ' src='https://creative-nudges.com/wp-content/uploads/2026/04/LRblob-query.png' />" . self::$eol;
        return $msg;
    }
} // End of storePageTests class
