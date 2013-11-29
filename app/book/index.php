<?php
//Include the necessary scripts
	$essentials->includePluginClass("display/Book_Details");
	$essentials->includePluginClass("display/Course");
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includeCSS("book.min.css");
	$essentials->includeJS("//tinymce.cachefly.net/4/tinymce.min.js");
	$essentials->includeJS("buy.min.js");
	$essentials->includeHeadHTML("<script>(function(\$){\$(function(){\$(document).FFI_BE_Buy(" . (is_user_logged_in() ? "{'showLogin':false}" : "") . ")})})(jQuery);</script>");

//Fetch the book information
	$params = $essentials->params ? $essentials->params[0] : 0;
	$failRedirect = $essentials->friendlyURL("");
	$book = new FFI\BE\Book_Details($params, $failRedirect);

//Set the page title
	$essentials->setTitle($book->data[0]->Title);
	
//Display the page
	echo "<h1>" . $book->data[0]->Title . "</h1>
	
";

//Display the page header
	echo "<article class=\"book-welcome\" style=\"background-image: url(" . FFI\BE\Cloudinary::background($book->data[0]->ImageID) . ")\">
<section class=\"quick-info\">
<h2>" . $book->data[0]->Title . "</h2>
<h3>by " . $book->data[0]->Author . "</h3>
</section>

<section class=\"cover\">
<img src=\"" . FFI\BE\Cloudinary::cover($book->data[0]->ImageID) . "\">
<span class=\"purchase\" data-id=\"" . $book->data[0]->BookID . "\" data-title=\"" . htmlentities($book->data[0]->Title) . "\" data-author=\"" . htmlentities($book->data[0]->Author) . "\" data-price=\"" . htmlentities($book->data[0]->Price) . "\" data-image=\"" . htmlentities(FFI\BE\Cloudinary::coverPreview($book->data[0]->ImageID)) . "\">Buy for \$" . $book->data[0]->Price . ".00</span>
</section>
</article>

";

//Display the container header
	echo "<section class=\"container\">
<h2>" . $book->data[0]->Title . " Book Details</h2>

<div class=\"row\">

";

//Display the sidebar
	echo "<aside class=\"supplement\">
<span class=\"purchase\" data-id=\"" . $book->data[0]->BookID . "\" data-title=\"" . htmlentities($book->data[0]->Title) . "\" data-author=\"" . htmlentities($book->data[0]->Author) . "\" data-price=\"" . htmlentities($book->data[0]->Price) . "\" data-image=\"" . htmlentities(FFI\BE\Cloudinary::coverPreview($book->data[0]->ImageID)) . "\">Buy for \$" . $book->data[0]->Price . ".00</span>

<ul class=\"navigation\">
<li class=\"more\"><a href=\"" . $essentials->friendlyURL("") . "\">See More Courses</a></li>
<li class=\"sell\"><a href=\"" . $essentials->friendlyURL("sell-books") . "\">Sell a Book</a></li>
</ul>

<hr>

" . FFI\BE\Course::getRecentBooksInCourse($book->data[0]->CourseID, 4, $book->data[0]->SaleID) . "
</aside>

";
	
//Display the merchant and book's condition
	$condition = array("Poor", "Fair", "Good", "Very Good", "Excellent");
	$conditionCSS = array("poor", "fair", "good", "very-good", "excellent");

	echo "<section class=\"details\">
<section class=\"content first overview\">
<h3>Merchant and Condition</h3>

<ul>
<li class=\"merchant\"><span>" . $book->data[0]->Merchant . "</span></li>
<li class=\"condition " . $conditionCSS[$book->data[0]->Condition - 1] . "\"><span>" . $condition[$book->data[0]->Condition - 1] . "<span class=\"desktop\"> Condition</span></span></li>
<li class=\"markings" . ($book->data[0]->Written == "1" ? " writing" : "") . "\"><span>" . ($book->data[0]->Written == "1" ? "Contains" : "No") . " Writing<span class=\"desktop\"> or Markings</span></span></li>
</ul>
</section>

";

//Display the book information
	echo "<section class=\"content stripe info\">
<h3>Book Information</h3>
<figure class=\"info\"></figure>

<ul class=\"columns\">
<li>
<dl>
<dt>ISBN-10</td>
<dd>" . $book->data[0]->ISBN10 . "</dd>
<dt>ISBN-13</td>
<dd>" . $book->data[0]->ISBN13 . "</dd>
</dl>
</li>

<li>
<dl>
<dt>Author</dt>
<dd>" . $book->data[0]->Author . "</dd>
";

	if ($book->data[0]->Edition != "") {
		echo "<dt>Edition</dt>
<dd>" . $book->data[0]->Edition . "</dd>
";
	}
	
	echo "</dl>
</li>
</ul>
</section>

";

//Display the book's associated courses
	echo "<section class=\"content courses\">
<h3>Dependent Courses</h3>
<figure class=\"courses\"></figure>

<ul class=\"course-list\">";

	foreach($book->data as $course) {
		echo "
<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $course->CourseID . "/small.png") . ")\">
<p>" . $course->Name . " " . $course->Number . " " . $course->Section . "</p>
</li>
";
	}

	echo "</ul>
</section>

";

//Display the user's comments
	if ($book->data[0]->Comments != "") {
		echo "<section class=\"content stripe comments\">
<h3>User Comments</h3>
<figure class=\"comments\"></figure>

" . $book->data[0]->Comments . "
</section>
";
	}
	
	echo "</section>
</div>
</section>";
?>
