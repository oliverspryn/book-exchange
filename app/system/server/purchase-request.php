<?php
//Include the system's core
	define("WP_USE_THEMES", false);
	require_once("../../../../../../wp-blog-header.php");
	
//Output as application/json
	header("content-type: application/json; charset=utf-8");

//Is this user logged in?
	if (!isset($_GET['key']) || !is_user_logged_in()) {
		die($_GET['callback'] . '(' . "{'message' : 'You are not logged in'}" . ')');
	}
	
//Have we been given the proper parameters?
	if (!isset($_GET['id'])) {
		die($_GET['callback'] . '(' . "{'message' : 'What book do you want to buy?'}" . ')');
	}
	
//SMTP logon information
	$username = "no-reply@forwardfour.com";
	$password = "431fc9b9-b977-4bfd-ab55-1472f0687a40";
	
//Grab the buyer's information
	$current_user;
	get_currentuserinfo();
	
	$userData = $current_user;	
	$fromEmail = $userData->user_email;
	$fromName = $userData->display_name;
	
//Grab the seller's information
	$sellerData = $wpdb->get_results("SELECT * FROM wp_users WHERE id = (SELECT userID FROM ffi_be_books WHERE id = '{$_GET['id']}')");
	
	if (count($sellerData)) {
		$seller = $sellerData[0];
		$toEmail = $seller->user_email;
		$toName = $seller->display_name;
	} else {
		die($_GET['callback'] . '(' . "{'message' : 'We cannot find a user for this book or the book is unavaliable'}" . ')');
	}
	
//Don't let the buyer buy from themself!
	if ($userData->ID == $seller->ID) {
		die($_GET['callback'] . '(' . "{'message' : 'Wait... you can't buy from yourself!'}" . ')');
	}
	
//Grab the book information
	$now = strtotime("now");
	$bookData = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_books.*, ffi_be_bookcategories.* FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.id = '{$_GET['id']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}");
	
	if (count($bookData)) {
		$book = $bookData[0];
	} else {
		die($_GET['callback'] . '(' . "{'message' : 'This book does not exist'}" . ')');
	}
	
//Generate the body of the email
	$subject = "Purchase Request for " . stripslashes($book->title);
	$bodyHTML = "<!DOCTYPE html>
<html lang=\"en-US\">
<head>
<meta charset=\"utf-8\">
<title>" . $subject . "</title>
</head>

<body>
<table border=\"0\" style=\"background: url(" . site_url() . "wp-content/themes/student-government/img/ribbon.png) repeat-x; font-family: Arial, Helvetica, sans-serif; font-size: 16px; padding-top: 15px;\" width=\"100%\">
<tr>
<td rowspan=\"2\" style=\"padding-right: 15px;\" valign=\"top\" width=\"250\">
<div align=\"center\"><img alt=\"" . htmlentities(stripslashes($book->title)) . " Cover - Please enable viewing images to see this picture\" height=\"200\" src=\"" . htmlentities(str_replace("sgagcc.co.cc", "sga.forwardfour.com", stripslashes($book->imageURL))) . "\" style=\"max-height: 200px;\"></div>
<h2 style=\"margin-bottom: 0px;\">" . stripslashes($book->title) . "</h2>
<div style=\"margin-bottom: 15px;\"><span style=\"background-color: #3D3D3D; border: 1px solid #FFFFFF; color: #FFFFFF; display: inline-block; font-size: 15px; margin: 5px 0px 5px 0px; max-width: 187px; padding: 4px 1px 4px 1px; vertical-align: top; white-space: no-wrap; width: auto;\"><span style=\"background: #42B6C9; border: 1px solid #FFFFFF; padding: 2px 7px 2px 7px;\">\$" . stripslashes($book->price) . "</span></span></div>
<div><span style=\"display: block; font-size: 14px; max-width: 240px; overflow: hidden; padding-top: 5px; text-overflow: ellipsis; white-space: nowrap;\" title=\"ISBN: " . htmlentities(stripslashes($book->ISBN)) . "\"><strong style=\"display: inline-block; width: 55px;\">ISBN:</strong> " . stripslashes($book->ISBN) . "</span></div>
<div><span style=\"display: block; font-size: 14px; max-width: 240px; overflow: hidden; padding-top: 5px; text-overflow: ellipsis; white-space: nowrap;\" title=\"Author: " . htmlentities(stripslashes($book->author)) . "\"><strong style=\"display: inline-block; width: 55px;\">Author:</strong> " . stripslashes($book->author) . "</span></div>" . (!empty($book->edition) ? "
<div><span style=\"display: block; font-size: 14px; max-width: 240px; overflow: hidden; padding-top: 5px; text-overflow: ellipsis; white-space: nowrap;\" title=\"Edition: " . htmlentities(stripslashes($book->edition)) . "\"><strong style=\"display: inline-block; width: 55px;\">Edition:</strong> " . stripslashes($book->edition) . "</span></div>" : "") . "
</td>

<td style=\"font-size: 18px; padding-bottom: 30px;\">
<strong>" . stripslashes($userData->display_name) . "</strong> would like to purchase your book <strong>" . stripslashes($book->title) . "</strong>.
<div style=\"font-size: 16px; padding-top: 5px;\">" . stripslashes($userData->display_name) . "'s email: <a href=\"mailto:" . stripslashes($userData->user_email) . "\" style=\"color: #4BF; text-decoration: none;\">" . stripslashes($userData->user_email) . "</a></div>
</td>
</tr>

<tr>
<td class=\"content\" valign=\"top\">
<p>Congratulations! You are just two steps away from selling your book:</p>
<ol>
<li>Reply to this email with a <strong>time and location</strong> to meet with " . stripslashes($userData->display_name) . " to <strong>exchange</strong> the book and funds <strong>in person</strong>.</li>
<li>Go and get your cash!</li>
</ol>

<div style=\"background: #FFCCCD; border: 3px solid #FF9999; margin: 30px 10% 0px 10%; padding: 7px; width: 80%;\">This book is now considered sold and has been automatically removed from the book exchange. If you choose to not sell this book to " . stripslashes($userData->display_name) . ", you may login at any time and restore this book to the exchange with one click under the &quot;My Account&quot; tab.</div>
</td>
</tr>

<tr>
<td colspan=\"2\" style=\"text-align: right;\"><hr>
<table border=\"0\" width=\"100%\" style=\"font-family: Arial, Helvetica, sans-serif; font-size: 16px;\">
<tr>
<td>
<div align=\"right\">
<div>Thank you, we hope that was easy!</div>
<br>
<div style=\"font-size: 12px; padding-top: 10px;\">~ The Student Government Association</div>
</div>
</td>

<td width=\"150\"><img alt=\"SGA Logo - Please enable viewing images to see this picture\" src=\"" . site_url() . "wp-content/themes/student-government/img/banner.png\"></td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>";
	
	$altBody = stripslashes($userData->display_name) . " would like to purchase your book " . stripslashes($book->title) . ".
	
" . stripslashes($userData->display_name) . "'s email: " . stripslashes($userData->user_email) . "
	
|**************************************************
|  BOOK INFORMATION
|
|  Title:    " . stripslashes($book->title) . "
|  Price:     \$" . stripslashes($book->price) . "
|  ISBN:     " . stripslashes($book->ISBN) . "
|  Author:   " . stripslashes($book->author) . (!empty($book->edition) ? "
|  Edition:  " . stripslashes($book->edition) . "
|**************************************************" : "
|**************************************************") . "
	
Congratulations! You are just two steps away from selling your book:
 
 - 1. Reply to this email with a time and location to meet with " . stripslashes($userData->display_name) . " to exchange the book and funds in person.
 - 2. Go and get your cash!
 
|**************************************************
|  This book is now considered sold and has been automatically removed from the book exchange. If you choose
|  to not sell this book to " . stripslashes($userData->display_name) . ", you may login at any time and restore this
|  book to the exchange with one click under the \"My Account\" tab.
|**************************************************

Thank you, we hope that was easy!
~ The Student Government Association";
	
//Send a notification email
	function email($toEmail, $toName, $fromEmail, $fromName, $subject, $HTMLBody, $textBody) {
	//Assemble the API call
		$args = array(
			"key" => "431fc9b9-b977-4bfd-ab55-1472f0687a40",
			"message" => array(
				"to" => array(array("email" => $toEmail, "name" => $toName)),
				"from_name" => $fromName,
				"from_email" => $fromEmail,
				"subject" => $subject,
				"html" => $HTMLBody,
				"text" => $textBody,
				"track_opens" => true,
				"track_clicks" => true,
				"auto_text" => false
			)
		);
		
	//Open a cURL session for making the call
		$curl = curl_init('https://mandrillapp.com/api/1.0/messages/send.json');
		
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($args));
		
		$response = curl_exec($curl);
	}

	email($toEmail, $toName, $fromEmail, $fromName, $subject, $bodyHTML, $altBody);
	echo $_GET['callback'] . '(' . "{'message' : 'success'}" . ')';
	
//Mark the book as sold
	$wpdb->get_results("UPDATE ffi_be_books SET sold = '1' WHERE linkID = '{$book->linkID}'");
	
//Log this purchase in the purchases database
	$buyerID = $userData->ID;
	$sellerID = $seller->ID;
	$bookID = $_GET['id'];
	$time = strtotime("now");
	
	$wpdb->get_results("INSERT INTO ffi_be_purchases (
				 	id, buyerID, sellerID, bookID, time
				 ) VALUES (
				 	NULL, '{$buyerID}', '{$sellerID}', '{$bookID}', '{$time}'
				 )");
?>