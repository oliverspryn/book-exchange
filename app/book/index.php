<?php
//Include the necessary scripts
	$essentials->includePluginClass("display/Book_Details");
	$essentials->includePluginClass("display/Book_Overview");
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includeCSS("styles/book.css");
	$essentials->includeHeadHTML("<script>\$(function() {\$('h3.haha').tooltip()})</script>");

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
	echo "<article class=\"book-welcome\" style=\"background-image: url(" . FFI\BE\Cloudinary::backgroundLarge($book->data[0]->ImageID) . ")\">
<section class=\"quick-info\">
<h2>" . $book->data[0]->Title . "</h2>
<h3>by " . $book->data[0]->Author . "</h3>
</section>

<section class=\"cover\">
<img src=\"" . FFI\BE\Cloudinary::cover($book->data[0]->ImageID) . "\">

<span class=\"purchase\" data-id=\"" . $book->data[0]->BookID . "\">Buy for \$" . $book->data[0]->Price . ".00</span>
</section>
</article>

";

//Display the merchant and book's condition
	$condition = array("Poor", "Fair", "Good", "Very Good", "Excellent");
	$conditionCSS = array("poor", "fair", "good", "very-good", "excellent");

	echo "<section class=\"container\">
<h2>" . $book->data[0]->Title . " Book Details</h2>

<div class=\"row\">
<section class=\"details\">
<section class=\"content first overview\">
<h3>Merchant and Condition</h3>

<ul>
<li class=\"merchant\"><span>" . $book->data[0]->Merchant . "</span></li>
<li class=\"condition " . $conditionCSS[$book->data[0]->Condition - 1] . "\"><span>" . $condition[$book->data[0]->Condition - 1] . " Condition</span></li>
<li class=\"markings" . ($book->data[0]->Written == "1" ? " writing" : "") . "\"><span>" . ($book->data[0]->Written == "1" ? "Contains" : "No") . " Writing or Markings</span></li>
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
<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $course->CourseID . "/icon_048.png") . ")\">
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

";

//Display the sidebar
	$additional = FFI\BE\Book_Overview::getRecentBooksInCourse($book->data[0]->CourseID, 5, $book->data[0]->SaleID);
	
	if ($additional != "") {
		echo "<aside class=\"supplement\">
<h3>More in " . $book->data[0]->Name . "</h3>

" . $additional . "
</aside>
";
	}
	
	echo "</div>
</section>";
?>
