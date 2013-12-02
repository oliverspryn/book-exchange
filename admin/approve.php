<?php
//Include the necessary scripts
	require_once("../wp-content/plugins/book-exchange/lib/display/Admin.php");

//Display the directions for this page
	echo "<div class=\"wrap\">
<h2>Book Cover Approval</h2>
<p>Book covers are not automatically published onto the exchange until they have been verified by a human for accuracy and proper modesty.</p>
<p>If there are any book covers which require approval, they will show in a table below. Please take a moment to review each of the covers. Pressing &quot;Approve&quot; will immediately publish the cover to the exchange, while &quot;Inappropriate&quot; or &quot;Unavailable&quot; will publish a generic, alternative cover instead. Since these covers have been automatically suggested by the system, there is a chance that some of them may be incorrect. You can do a search on <a href=\"https://images.google.com\" target=\"_blank\">Google Images</a> for the correct book cover and paste the URL of the image of the correct cover into the corresponding text input.</p>

";
	
//Display the listing of books which need approval
	$books = FFI\BE\Admin::covers();

	echo "<table class=\"approve-covers form-table\">
<tbody>";

	if (count($books)) {
		foreach($books as $book) {
			echo "
<tr>
<td>
<img src=\"" . $book->ImageID . "\">
<h3>" . $book->Title . "</h3>
<h4>by " . $book->Author . "</h4>
" . ($book->Edition != "" ? "<h4>Edition: " . $book->Edition . "</h4>\n" : "") . "
<p>
<button class=\"approve button button-primary\" data-id=\"" . $book->BookID . "\">Approve</button>
<button class=\"button inappropriate\" data-id=\"" . $book->BookID . "\">Inappropriate</button>
<button class=\"button unavailable\" data-id=\"" . $book->BookID . "\">Unavailable</button>
</p>

<p>
<input class=\"alternative-url-input regular-text\" id=\"URL-" . $book->BookID . "\" name=\"URL-" . $book->BookID . "\" placeholder=\"Image of an alternative cover\" type=\"text\">
<button class=\"alternative-url button\" data-id=\"" . $book->BookID . "\" disabled>Apply</button>
</p>
</td>
</tr>
";
		}
	} else {
		echo "
<tr>
<td class=\"none\">
<p>Horray! No book covers need approval!</p>
</td>
</tr>
";
	}
	
	echo "</tbody>
</table>
</div>";
?>