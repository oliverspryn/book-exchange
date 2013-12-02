<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/lib/display/Admin.php");
	
//Fetch the data from the plugin API table
	$API = FFI\BE\Admin::APIData();
	$cron = site_url() .  "/wp-content/plugins/book-exchange/admin/processing/cron.php";
	
//Display a table containing a listing of all system APIs
	echo "<div class=\"wrap\">
<h2>API Management</h2>

";

//Display the page's success message
	if (isset($_GET['updated'])) {
		echo "<div class=\"updated\">
<p><strong>Success:</strong> The Book Exchange API keys have been updated.</p>
</div>

";
	}

	echo "<p>The Book Exchange requires access to several third-party services in order to provide features such as searching, image hosting and maniuplation, book cover suggestions, and sending emails.</p>
<p>Please open an account with <a href=\"http://cloudinary.com\" target=\"_blank\">Cloudinary</a> for the hosting and manipulation of book covers, <a href=\"http://indexden.com\" target=\"_blank\">IndexDen</a> for searching available books, <a href=\"https://developer.getinvisiblehand.com/\" target=\"_blank\">InvisibleHand API</a> for new book cover suggestions, and <a href=\"http://mandrill.com\" target=\"_blank\">Mandrill</a> for sending emails. Copy the API keys from these services into the form below. A free subscription to each of these services will suffice for sites with low or medium amounts of traffic.</p>
<p>This plugin requires a maintenance script to be run periodically. This script will clean up old, expired books from the IndexDen database. Ideally, this script should be run every couple of hours to keep the index clean. Please configure a cron job to execute this script every few hours: <a href=\"" . $cron . "\" target=\"_blank\">" . $cron . "</a></p>

<form action=\"" . site_url() . "/wp-content/plugins/book-exchange/admin/processing/api.php\" method=\"post\">
<table class=\"form-table\">
<tbody>
<tr>
<th><label for=\"cloudinary-cloud-name\">Cloudinary cloud name:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"cloudinary-cloud-name\" name=\"cloudinary-cloud-name\" type=\"text\" value=\"" . $API->CloudinaryCloudName . "\"></td>
</tr>

<tr>
<th><label for=\"cloudinary-api-key\">Cloudinary API Key:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"cloudinary-api-key\" name=\"cloudinary-api-key\" type=\"text\" value=\"" . $API->CloudinaryAPIKey . "\"></td>
</tr>

<tr>
<th><label for=\"cloudinary-api-secret\">Cloudinary API Secret:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"cloudinary-api-secret\" name=\"cloudinary-api-secret\" type=\"text\" value=\"" . $API->CloudinaryAPISecret . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-url\">IndexDen API URL:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"indexden-url\" name=\"indexden-url\" type=\"text\" value=\"" . $API->IndexDenURL . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-username\">IndexDen API Username:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"indexden-username\" name=\"indexden-username\" type=\"text\" value=\"" . $API->IndexDenUsername . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-password\">IndexDen API Password:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"indexden-password\" name=\"indexden-password\" type=\"text\" value=\"" . $API->IndexDenPassword . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-name\">IndexDen Index Name:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"indexden-name\" name=\"indexden-name\" type=\"text\" value=\"" . $API->IndexDenIndex . "\"></td>
</tr>

<tr>
<th><label for=\"invisiblehand-app\">InvisibleHand App ID:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"invisiblehand-app\" name=\"invisiblehand-app\" type=\"text\" value=\"" . $API->InvisibleHandAppID . "\"></td>
</tr>

<tr>
<th><label for=\"invisiblehand-key\">InvisibleHand App Key:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"invisiblehand-key\" name=\"invisiblehand-key\" type=\"text\" value=\"" . $API->InvisibleHandAppKey . "\"></td>
</tr>

<tr>
<th><label for=\"mandrill\">Mandrill API Key:</label></th>
<td><input autocomplete=\"off\" class=\"regular-text\" id=\"mandrill\" name=\"mandrill\" type=\"text\" value=\"" . $API->MandrillKey . "\"></td>
</tr>
</tbody>
</table>

<p class=\"submit\">
<input class=\"button button-primary\" id=\"submit\" name=\"submit\" value=\"Update Settings\" type=\"submit\">
</p>
</form>
</div>";
?>