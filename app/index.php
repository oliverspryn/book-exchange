<?php
//Include the system's core
	$essentials->setTitle("Book Exchange");
	$essentials->includeCSS("styles/explore.css");
	$essentials->includeJS("scripts/explore.min.js");
	$essentials->includePluginClass("display/Book_Courses");
	$essentials->includePluginClass("display/Book_Overview");
	
//Display the loader mask
	echo "<section class=\"loader\"></section>
	
";

//Display the welcome section
	$total = FFI\BE\Book_Overview::getTotal();

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

<li class=\"search\">
<h3>Search</h3>
</li>
</ul>
</div>
</section>

";

//Display the explore section header
	$allCourses = FFI\BE\Book_Courses::getCourses();
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
<select name=\"by\" class=\"input-small by\">
<option value=\"title\">Title</option>
<option value=\"author\">Author</option>
</select>

<span class=\"inText\">in</span>
<select name=\"in\" class=\"in\">
<option value=\"0\">All Courses</option>
" . $menu . "</select>

<span class=\"sortText\">and sort by</span>
<select name=\"sort\" class=\"input-medium sort\">
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
	$arts = FFI\BE\Book_Courses::getAL();
	$artsListing = "";
	
	foreach($arts as $art) {
		$artsListing .= "<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $art->CourseID . "/icon_048.png") . ")\">
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

//Display the Science, Engineering, and Mathematics
	$sciences = FFI\BE\Book_Courses::getSEM();
	$sciencesListing = "";
	
	foreach($sciences as $science) {
		$sciencesListing .= "<li style=\"background-image: url(" . $essentials->dataURL("tiles/" . $science->CourseID . "/icon_048.png") . ")\">
<a href=\"" . $essentials->friendlyURL("browse/". $science->URL) . "\">
<h3>" . $science->Name . "</h3>
<p>" . $science->Total . " " . ($science->Total == 1 ? "Book" : "Books") . " Avaliable</p>
</a>
</li>
";
	}

	echo "<section class=\"content courses science-mathematics\">
<h2>Science, Engineering &amp; Mathematics</h2>

<ul>
" . $sciencesListing . "</ul>
</section>
</section>";
