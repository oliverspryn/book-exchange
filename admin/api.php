<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/lib/display/Admin.php");
	
//Fetch the data from the plugin API table
	$API = FFI\BE\Admin::APIData();
	
//Display a table containing a listing of all system APIs
	echo "<div class=\"wrap\">
<h2>API Management</h2>
<p>The Book Exchange requires access to several third-party services in order to provide features such as searching, book cover suggestions, image hosting and maniuplation, and sending emails.</p>
<p>Please open an account with <a href=\"http://cloudinary.com\" target=\"_blank\">Cloudinary</a> for the hosting and manipulation of book covers, <a href=\"https://code.google.com/apis/console/\" target=\"_blank\">Google Shopping API</a> for new book cover suggestions, <a href=\"http://indexden.com\" target=\"_blank\">IndexDen</a> for searching available books, and <a href=\"http://mandrill.com\" target=\"_blank\">Mandrill</a> for sending emails. Copy the API keys from these services into the form below. A free subscription to each of these services will suffice for sites with low or medium amounts of traffic.</p>
<p><strong>Note:</strong> You will need to manually enable the Google Shopping API service within Google APIs Console in order to have access to this feature.</p>

<form action=\"" . $_SERVER['REQUEST_URI'] . "\" method=\"post\">
<table class=\"form-table\">
<tbody>
<tr>
<th><label for=\"cloudinary-cloud-name\">Cloudinary cloud name:</label></th>
<td><input class=\"regular-text\" id=\"cloudinary-cloud-name\" name=\"cloudinary-cloud-name\" type=\"text\" value=\"" . $API->CloudinaryCloudName . "\"></td>
</tr>

<tr>
<th><label for=\"cloudinary-api-key\">Cloudinary API Key:</label></th>
<td><input class=\"regular-text\" id=\"cloudinary-api-key\" name=\"cloudinary-api-key\" type=\"text\" value=\"" . $API->CloudinaryAPIKey . "\"></td>
</tr>

<tr>
<th><label for=\"cloudinary-api-secret\">Cloudinary API Secret:</label></th>
<td><input class=\"regular-text\" id=\"cloudinary-api-secret\" name=\"cloudinary-api-secret\" type=\"text\" value=\"" . $API->CloudinaryAPISecret . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-url\">IndexDen API URL:</label></th>
<td><input class=\"regular-text\" id=\"indexden-url\" name=\"indexden-url\" type=\"text\" value=\"" . $API->IndexDenURL . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-username\">IndexDen API Username:</label></th>
<td><input class=\"regular-text\" id=\"indexden-username\" name=\"indexden-username\" type=\"text\" value=\"" . $API->IndexDenUsername . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-password\">IndexDen API Password:</label></th>
<td><input class=\"regular-text\" id=\"indexden-password\" name=\"indexden-password\" type=\"text\" value=\"" . $API->IndexDenPassword . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-name\">IndexDen Index Name:</label></th>
<td><input class=\"regular-text\" id=\"indexden-name\" name=\"indexden-name\" type=\"text\" value=\"" . $API->IndexDenIndex . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-name\">InvisibleHand App ID:</label></th>
<td><input class=\"regular-text\" id=\"invisiblehand-app\" name=\"invisiblehand-app\" type=\"text\" value=\"" . $API->InvisibleHandAppID . "\"></td>
</tr>

<tr>
<th><label for=\"mandrill\">Mandrill API Key:</label></th>
<td><input class=\"regular-text\" id=\"mandrill\" name=\"mandrill\" type=\"text\" value=\"" . $API->MandrillKey . "\"></td>
</tr>
</tbody>
</table>

<p class=\"submit\">
<input class=\"button button-primary\" id=\"submit\" name=\"submit\" value=\"Update Settings\" type=\"submit\">
</p>
</form>
</div>";
?>