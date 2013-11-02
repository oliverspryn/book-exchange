<?php
//Include the necessary scripts
	$essentials->setTitle("Book Exchange");
	$essentials->includeCSS("styles/explore.min.css");
	$essentials->includeJS("scripts/explore.min.js");
	$essentials->includeJS("scripts/buy.min.js");
	$essentials->includeJS("//tinymce.cachefly.net/4/tinymce.min.js");
	$essentials->includePluginClass("display/Book");
	$essentials->includePluginClass("display/Course");
	$essentials->includeHeadHTML("<script>(function(\$){\$(function(){\$(document).FFI_BE_Buy(" . (is_user_logged_in() ? "{'showLogin':false}" : "") . ")})})(jQuery);</script>");
	
//Display the loader mask
	echo "<section class=\"loader\"></section>
	
";

//Display the welcome section
	$total = FFI\BE\Book::total();

	echo "<section class=\"welcome\">
<div>
<h2>Book Exchange</h2>

<ul>
<li class=\"sell\">
<a href=\"" . $essentials->friendlyURL("sell-books") . "\">
<h3>Sell Books</h3>
</a>
</li>

<li class=\"browse\">
<h3>Browse</h3>
<span class=\"count\"><span>" . $total . " " . ($total == 1 ? "Book" : "Books") . "</span></span>
</li>

<li class=\"my-books\">
<a href=\"" . $essentials->friendlyURL("my-books") . "\">
<h3>My Books</h3>
</a>
</li>
</ul>
</div>
</section>

";

//Display the explore section header
	$allCourses = FFI\BE\Course::getCourses();
	$menu = "";
	
	foreach($allCourses as $course) {
		$menu .= "<option value=\"" . $course->CourseID . "\">" . htmlentities($course->Name) . "</option>
";
	}

	echo "<section class=\"explore\">
<header>
<h2>Explore and Search the Exchange</h2>
<div class=\"wrapper\">
<div class=\"input\">
<input id=\"search-main\" placeholder=\"To search, just type...\" type=\"search\" />
</div>

<div class=\"options\">
<div>
<span class=\"byText\">by</span>
<select class=\"input-small by\" name=\"by\">
<option value=\"title\">Title</option>
<option value=\"author\">Author</option>
</select>

<span class=\"inText\">in</span>
<select class=\"in\" name=\"in\">
<option value=\"0\">All Courses</option>
" . $menu . "</select>

<span class=\"sortText\">and sort by</span>
<select class=\"input-medium sort\" name=\"sort\">
<option value=\"relevance\">Relevance</option>
<option value=\"title-asc\">Title A-Z</option>
<option value=\"title-desc\">Title Z-A</option>
<option value=\"price-asc\">Price Low to High</option>
<option value=\"price-desc\">Price High to Low</option>
<option value=\"author-asc\">Author A-Z</option>
<option value=\"author-desc\">Author Z-A</option>
</select>
</div>
</div>
</div>

<button class=\"btn btn-danger close-search\"><span class=\"large\">Close Search</span><span class=\"small\">X</span></button>
</header>

";

//Display the Arts and Letter section
	$arts = FFI\BE\Course::getAL();
	$artsListing = "";
	
	foreach($arts as $art) {
		$artsListing .= "<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $art->CourseID . "/small.png") . ")\">
<a href=\"" . $essentials->friendlyURL("browse/". $art->URL) . "\">
<h3>" . $art->Name . "</h3>
<p>" . $art->Total . " " . ($art->Total == 1 ? "Book" : "Books") . " Avaliable</p>
</a>
</li>
";
	}
	
	echo "<section class=\"content courses liberal-arts\">
<h2>Arts and Letters</h2>
	
<ul>
" . $artsListing . "</ul>
</section>

";

//Display the Science, Engineering & Mathematics section
	$sciences = FFI\BE\Course::getSEM();
	$sciencesListing = "";
	
	foreach($sciences as $science) {
		$sciencesListing .= "<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $science->CourseID . "/small.png") . ")\">
<a href=\"" . $essentials->friendlyURL("browse/". $science->URL) . "\">
<h3>" . $science->Name . "</h3>
<p>" . $science->Total . " " . ($science->Total == 1 ? "Book" : "Books") . " Avaliable</p>
</a>
</li>
";
	}

	echo "<section class=\"content even courses science-mathematics\">
<h2>Science, Engineering &amp; Mathematics</h2>

<ul>
" . $sciencesListing . "</ul>
</section>
</section>";
?>