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

</section>

";

//Display the book's associated courses
	echo "<section class=\"content\">
<h3>Dependent Courses</h3>
<figure class=\"courses\"></figure>

</section>

";

//Display the user's comments
	echo "<section class=\"content stripe\">
<h3>User Comments</h3>
<figure class=\"comments\"></figure>

</section>
</section>

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