<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/lib/display/Settings.php");
	
//Instantiate form element display class
	$settings = new FFI\BE\Settings();
	
//Display a the page heading
	echo "<div class=\"wrap\">
<h2>Settings</h2>

";

//Display the page's success message
	if (isset($_GET['updated'])) {
		echo "<div class=\"updated\">
<p><strong>Success:</strong> The Book Exchange settings have been updated.</p>
</div>

";
	}

//Display a table containing a listing of all plugin settings
	echo "<form action=\"" . site_url() . "/wp-content/plugins/book-exchange/admin/processing/settings.php\" method=\"post\">
<table class=\"form-table\">
<tbody>
<tr>
<th><label for=\"expire\">Months Before Book Expires:</label></th>
<td>
" . $settings->getExpire() . "
<p class=\"description\">You will need to press the &quot;Reload IndexDen Index&quot; button below after changing this value.</p>
</td>
</tr>

<tr>
<th><label for=\"email-name\">Automated Email &quot;From&quot; Name:</label></th>
<td>" . $settings->getEmailName() . "</td>
</tr>

<tr>
<th><label for=\"email-address\">Automated Email &quot;From&quot; Address:</label></th>
<td>
" . $settings->getEmailAddress() . "
<p class=\"description\">Should come from a domain you've registered in the Mandrill Control Panel.</p>
</td>
</tr>

<tr>
<th><label for=\"timezone\">Plugin Time Zone:</label></th>
<td>
" . $settings->getTimeZone() . "
</td>
</tr>

<tr>
<th><label>Reload IndexDen Index:</label></th>
<td>
<button class=\"button button-primary button-hero\">Reload IndexDen Index</button>
<p class=\"description\">This action will delete all of the books in the IndexDen index, and then add all of them back in. Rebuilding the index may take several minutes to complete, depending on the number of published books, and will <strong>temporarly disable the book exchange's searching mechanism</strong>.</p>
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