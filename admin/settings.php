<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/includes/forms/display/Settings.php");
	
//Instantiate form element display class
	$settings = new FFI\BE\Settings();
	
//Display a table containing a listing of all system settings
	echo "<div class=\"wrap\">
<h2>Settings</h2>

<form action=\"" . $_SERVER['REQUEST_URI'] . "\" method=\"post\">
<table class=\"form-table\">
<tbody>
<tr>
<th><label for=\"expire\">Months Before Book Expires:</label></th>
<td>
" . $settings->getExpire() . "
</td>
</tr>

<tr>
<th><label for=\"email-name\">Automated Email &quot;From&quot; Name:</label></th>
<td>" . $settings->getEmailName() . "</td>
</tr>

<tr>
<th><label for=\"email-address\">Automated Email &quot;From&quot; Address:</label></th>
<td>" . $settings->getEmailAddress() . "</td>
</tr>

<tr>
<th><label for=\"timezone\">Plugin Time Zone:</label></th>
<td>
" . $settings->getTimeZone() . "
</td>
</tr>
</tbody>
</table>

<p class=\"submit\">
<input class=\"button button-primary\" id=\"submit\" name=\"submit\" value=\"Update Settings\" type=\"submit\">
</p>
</form>
</div>";
?>