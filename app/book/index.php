<?php
//Include the necessary scripts
	$essentials->includeCSS("book.min.css");
	$essentials->includeHeadHTML("<script>\$(function(){\$(document).FFI_BE_Buy(" . (is_user_logged_in() ? "{'showLogin':false}" : "") . ")})</script>");
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includePluginClass("display/Book");
	$essentials->includePluginClass("display/Course");
	$essentials->includeJS("//tinymce.cachefly.net/4/tinymce.min.js");
	$essentials->includeJS("buy.min.js");

//Fetch the book information
	$params = $essentials->params ? $essentials->params[0] : 0;
	$book = FFI\BE\Book::details($params, $failRedirect);
	
//Check to see if any information was returned
	if (!count($book)) {
		wp_redirect($essentials->friendlyURL(""));
		exit;
	}
	
	$info = &$book[0];

//Set the page title
	$essentials->setTitle($info->Title);
	
//Display the page
	echo "<h1>" . $info->Title . "</h1>
	
";

//Display the page header
	echo "<article class=\"book-welcome\" style=\"background-image: url(" . FFI\BE\Cloudinary::background($info->ImageID) . ")\">
<section class=\"quick-info\">
<h2>" . $info->Title . "</h2>
<h3>by " . $info->Author . "</h3>
</section>

<section class=\"cover\">
<img src=\"" . FFI\BE\Cloudinary::cover($info->ImageID) . "\">
<span class=\"purchase\" data-id=\"" . $info->SaleID . "\" data-title=\"" . htmlentities($info->Title) . "\" data-author=\"" . htmlentities($info->Author) . "\" data-price=\"" . htmlentities($info->Price) . "\" data-image=\"" . htmlentities(FFI\BE\Cloudinary::coverPreview($info->ImageID)) . "\">Buy for \$" . $info->Price . ".00</span>
</section>
</article>

";

//Display the container header
	echo "<section class=\"container\">
<h2>" . $info->Title . " Book Details</h2>

<div class=\"row\">

";

//Display the sidebar
	echo "<aside class=\"supplement\">
<span class=\"purchase\" data-id=\"" . $info->SaleID . "\" data-title=\"" . htmlentities($info->Title) . "\" data-author=\"" . htmlentities($info->Author) . "\" data-price=\"" . htmlentities($info->Price) . "\" data-image=\"" . htmlentities(FFI\BE\Cloudinary::coverPreview($info->ImageID)) . "\">Buy for \$" . $info->Price . ".00</span>

<ul class=\"navigation\">
<li class=\"more\"><a href=\"" . $essentials->friendlyURL("") . "\">See More Courses</a></li>
<li class=\"sell\"><a href=\"" . $essentials->friendlyURL("sell-books") . "\">Sell a Book</a></li>
</ul>

<hr>

" . FFI\BE\Course::getRecentBooksInCourse($info->CourseID, 4, $info->SaleID) . "
</aside>

";
	
//Display the merchant and book's condition
	$condition = array("Poor", "Fair", "Good", "Very Good", "Excellent");
	$conditionCSS = array("poor", "fair", "good", "very-good", "excellent");

	echo "<section class=\"details\">
<section class=\"content first overview\">
<h3>Merchant and Condition</h3>

<ul>
<li class=\"merchant\"><span>" . $info->Merchant . "</span></li>
<li class=\"condition " . $conditionCSS[$info->Condition - 1] . "\"><span>" . $condition[$info->Condition - 1] . "<span class=\"desktop\"> Condition</span></span></li>
<li class=\"markings" . ($info->Written == "1" ? " writing" : "") . "\"><span>" . ($info->Written == "1" ? "Contains" : "No") . " Writing<span class=\"desktop\"> or Markings</span></span></li>
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
<dd>" . $info->ISBN10 . "</dd>
<dt>ISBN-13</td>
<dd>" . $info->ISBN13 . "</dd>
</dl>
</li>

<li>
<dl>
<dt>Author</dt>
<dd>" . $info->Author . "</dd>
";

	if ($info->Edition != "") {
		echo "<dt>Edition</dt>
<dd>" . $info->Edition . "</dd>
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

	foreach($book as $course) {
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
	if ($info->Comments != "") {
		echo "<section class=\"content stripe comments\">
<h3>User Comments</h3>
<figure class=\"comments\"></figure>

" . $info->Comments . "
</section>
";
	}
	
	echo "</section>
</div>
</section>";
?>