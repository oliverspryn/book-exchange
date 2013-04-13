<?php
//Include the necessary scripts
	$essentials->includePluginClass("display/Book_Details");
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
<section class=\"cover\">
<img src=\"" . FFI\BE\General::bookCoverPreview($book->data[0]->ImageID) . "\">

<footer class=\"social\">
<ul>
<li class=\"facebook\"></li>
<li class=\"twitter\"></li>
<li class=\"google-plus\"></li>
<li class=\"pinterest\"></li>
<li class=\"email\"></li>
</ul>
</footer>
</section>

<section class=\"quick-info\">
<h2>" . $book->data[0]->Title . " <span class=\"author\">by " . $book->data[0]->Author . "</span></h2>
<button class=\"btn btn-large btn-primary\">Buy for \$" . $book->data[0]->Price . ".00</button>
</section>
</article>

";

//Display the page sidebar
	echo "<aside class=\"sidebar\">
<h2>More in Physics</h2>
</aside>";
?>