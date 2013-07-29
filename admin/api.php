<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/includes/display/Admin.php");
	
//Fetch the data from the plugin API table
	$API = FFI\BE\Admin::APIData();
	
//Display a table containing a listing of all system APIs
	echo "<div class=\"wrap\">
<h2>API Management</h2>

<form action=\"" . $_SERVER['REQUEST_URI'] . "\" method=\"post\">
<table class=\"form-table\">
<tbody>
<tr>
<th><label for=\"cloudinary\">Cloudinary cloud name:</label></th>
<td><input class=\"regular-text\" id=\"cloudinary\" name=\"cloudinary\" type=\"text\" value=\"" . $API->Cloudinary . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-url\">IndexDen API URL:</label></th>
<td><input class=\"regular-text\" id=\"indexden-url\" name=\"indexden-url\" type=\"text\" value=\"" . $API->IndexDenURL . "\"></td>
</tr>

<tr>
<th><label for=\"indexden-password\">IndexDen API Username:</label></th>
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