<?php
//Include the necessary scripts
	$essentials->includePluginClass("display/Book_Details");
	$essentials->includePluginClass("display/Book_Overview");
	$essentials->includePluginClass("display/General");
	$essentials->includeCSS("styles/book.css");

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
	echo "<article class=\"book-welcome\" style=\"background-image: url(" . FFI\BE\General::bookBackgroundLarge($book->data[0]->ImageID) . ")\">
<section class=\"quick-info\">
<h2>" . $book->data[0]->Title . "</h2>
<h3>by " . $book->data[0]->Author . "</h3>
</section>

<section class=\"cover\">
<img src=\"" . FFI\BE\General::bookCover($book->data[0]->ImageID) . "\">

<span class=\"purchase\">Buy for \$" . $book->data[0]->Price . ".00</span>
</section>
</article>

";

//Display the book condition and whether or not it has been written in
	echo "<section class=\"container\">
<h2>" . $book->data[0]->Title . " Book Details</h2>

<div class=\"row\">
<section class=\"details\">
<section class=\"content first\">
<h3>Condition and Markings</h3>

</section>

";

//Display the book information
	echo "<section class=\"content stripe\">
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
<dt>Author</dt>
<dd>" . $book->data[0]->Author . "</dd>
";

	if ($book->data[0]->Edition != "") {
		echo "<dt>Edition</dt>
<dd>" . $book->data[0]->Edition . "</dd>
";
	}
	
	if (is_user_logged_in()) {
		echo "<dt>Merchant</dt>
<dd>" . $book->data[0]->Merchant . "</dd>
";
	} else {
		echo "<dt>Merchant</dt>
<dd><a href=\"\"" . $book->data[0]->Merchant . "</dd>
";
	}
	
	echo "</li>
</ul>
</section>

";

//Display the book's associated courses
	echo "<section class=\"content\">
<h3>Dependent Courses</h3>
<figure class=\"courses\"></figure>

<ul class=\"course-list\">";

	foreach($book->data as $course) {
		echo "
<li style=\"background-image: url(//localhost/SGA-Template/images/tiles/" . $course->CourseID . "/icon_048.png)\">
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