<?php
/**
 * Buyer Purchase Request Emailer class
 *
 * This is class is designed to build an email to be sent to a
 * buyer he or she makes a purchase request as a confirmation of
 * his or her action.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Email_Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.email
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Email_Base.php");

class Email_Buyer extends Email_Base {
/**
 * Hold the title of the book.
 *
 * @access public
 * @type   string
*/

	public $title;

/**
 * Hold the price of the book.
 *
 * @access public
 * @type   int
*/

	public $price;

/**
 * Hold the URL of the cover to the book.
 *
 * @access public
 * @type   string
*/

	public $imageURL;
	
/**
 * Hold the name of the merchant.
 *
 * @access public
 * @type   string
*/

	public $merchant;

/**
 * Hold the first name of the merchant.
 *
 * @access public
 * @type   string
*/

	public $merchantFirstName;

/**
 * Build the HTML and plain-text versions of the email body 
 * from the information gathered previously.
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	public function buildBody() {
	//Generate the absolute URL to the directory where the images in the email can be found
		$directory = "http://" . $_SERVER['HTTP_HOST'] . str_replace("ajax/purchase.php", "", $_SERVER['PHP_SELF']) . "images/email-assets/";
		
	//Clean the URL to the book cover
		$cleanURL = $this->imageURL;
		
		if (substr($cleanURL, 0, 7) == "http://" || substr($cleanURL, 0, 8) == "https://") {
			//Good!
		} else {
			$cleanURL = "http://" . $cleanURL;
		}
		
	//Generate the HTML version of the email
		$this->HTMLBody = "<!DOCTYPE html>
<html lang=\"en-US\">
<head>
<meta charset=\"utf-8\">
<title>" . $this->subject . "</title>
</head>

<body>
<table cellpadding=\"none\" style=\"border-collapse:collapse; border-top: 5px solid #000000;\" width=\"660\">
<tbody>
<tr>
<td align=\"center\" background=\"" . $directory . "header-buyer.jpg\" height=\"647\" valign=\"top\" style=\"border-left: 1px solid #000000; border-right: 1px solid #000000;\" width=\"660\">
<!--[if gte mso 9]>
<v:rect xmlns:v=\"urn:schemas-microsoft-com:vml\" fill=\"true\" stroke=\"false\" style=\"height: 647px; width: 660px;\">
<v:fill src=\"" . $directory . "header-buyer.jpg\" type=\"frame\" />
<![endif]-->
<table cellpadding=\"none\" style=\"border-collapse: collapse;\" width=\"640\">
<tbody>
<tr>
<td height=\"208\"></td>
</tr>

<tr>
<td align=\"center\" height=\"355\">
<img alt=\"" . htmlentities($this->title) . " Book Cover\" height=\"355\" src=\"" . $cleanURL . "\" width=\"275\" />
<p style=\"font-size: 17px; margin: 0px;\">&nbsp;</p>
<p align=\"center\" style=\"color: #FFFFFF; font-family: Arial,sans-serif; font-size: 16px; margin: 0px;\">\$" . $this->price . ".00</p>
</td>
</tr>
</tbody>
</table>
<!--[if gte mso 9]>
</v:rect>
<![endif]-->
</td>
</tr>

<tr>
<td style=\"border-left: 1px solid #000000; border-right: 1px solid #000000;\">
<table>
<tbody>
<tr>
<td width=\"25\"></td>
<td align=\"center\">
<p align=\"center\" style=\"font-family: Arial,sans-serif; font-size: 16px;\">Congratulations! You've just requested <strong>" . $this->title . "</strong> from <strong>" . $this->merchant . "</strong>. You're only three steps away from obtaining your book!</p>
</td>
<td width=\"25\"></td>
</tr>

<tr height=\"30\">
<td colspan=\"3\"></td>
</tr>
</tbody>
</table>

<img alt=\"Content Divider\" height=\"28\" src=\"" . $directory . "divider.jpg\" width=\"660\" />
</td>
</tr>

<tr>
<td style=\"border-left: 1px solid #000000; border-right: 1px solid #000000;\">
<table>
<tbody>
<tr>
<td colspan=\"5\" height=\"10\"></td>
</tr>

<tr>
<td align=\"center\" colspan=\"5\" height=\"25\">
<h2 style=\"font-family: Arial,sans-serif; font-size: 24px; font-weight: 100; margin: 5px 0px 0px 0px; text-align: center;\">Next Steps</h2>
</td>
</tr>

<tr>
<td colspan=\"5\" height=\"25\"></td>
</tr>

<tr>
<td width=\"35\"></td>
<td>
<img alt=\"Step Numbers\" height=\"232\" src=\"" . $directory . "numbers.jpg\" width=\"69\" />
</td>
<td width=\"25\"></td>
<td>
<table height=\"245\">
<tbody>
<tr>
<td height=\"80\" valign=\"middle\"><p style=\"font-family: Arial,sans-serif; font-size: 16px;\">Ensure that you <strong>have \$" . $this->price . ".00</strong> on hand.</p></td>
</tr>

<tr>
<td height=\"80\" valign=\"middle\"><p style=\"font-family: Arial,sans-serif; font-size: 16px;\">Wait for an <strong>email from " . $this->merchant . "</strong> with a propsed <strong>time and location</strong> to meet in person to exchange the book and funds.</p></td>
</tr>

<tr>
<td height=\"80\" valign=\"middle\"><p style=\"font-family: Arial,sans-serif; font-size: 16px;\"><strong>Meet " . $this->merchantFirstName . "</strong> at the agreed-upon time and location to obtain your book!</p></td>
</tr>
</tbody>
</table>
</td>
<td width=\"35\"></td>
</tr>

<tr height=\"30\">
<td colspan=\"5\"></td>
</tr>
</tbody>
</table>
</td>
</tr>

<tr>
<td align=\"center\" bgcolor=\"#181818\" height=\"45\" style=\"border-bottom: 1px solid #000000; border-left: 1px solid #000000; border-right: 1px solid #000000;\" valign=\"middle\">
<img alt=\"Small SGA Logo\" height=\"32\" src=\"" . $directory . "logo.jpg\" width=\"64\" />
</td>
</tr>

<tr>
<td><img alt=\"Shadow\" height=\"28\" src=\"" . $directory . "shadow.jpg\" width=\"660\" /></td>
</tr>
</tbody>
</table>
</body>
</html>";

	//Generate the plain-text version of the email
		$this->textBody = "Congratulations! You've just requested " . $this->title . " from " . $this->merchant . ". You're only three steps away from obtaining your book!
	
*** Book Information ***

   Title:    " . $this->title . "
   Price:     \$" . $this->price . "

*** Next Steps ***
 
   1. Ensure that you have \$" . $this->price . ".00 on hand.
   2. Wait for an email from " . $this->merchant . " with a propsed time and location to meet in person to exchange the book and funds.
   3. Meet " . $this->merchantFirstName . " at the agreed-upon time and location to obtain your book!

~ The Student Government Association";
	}
}
?>