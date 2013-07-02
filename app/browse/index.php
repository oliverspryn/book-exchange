<?php
//This page must have a course name in the URL
	if (!$essentials->params) {
		wp_redirect($essentials->friendlyURL(""));
		exit;
	}
	
//Identify whether this page should be showing a listing of courses with books, or a single course with books
	define("FFI\BE\DISPLAY_MODE", isset($essentials->params[2]) && isset($essentials->params[3]) ? "books" : "courses");
	
//Verify that something like Humanities 101 A is given and not just Humanities 101
	if (isset($essentials->params[2]) && !isset($essentials->params[3])) {
		wp_redirect($essentials->friendlyURL("browse/" . $essentials->params[0]));
		exit;
	}
	
//Include the necessary scripts
	$essentials->includeCSS("styles/course.css");
	$essentials->includePluginClass("display/Book_Courses");
	$essentials->includePluginClass("display/Book_Overview");
	$essentials->includePluginClass("display/General");
	$essentials->includeHeadHTML("<script>\$(function() {\$('h3.haha').tooltip()})</script>");

//Get information regarding the current course
	$course = $essentials->params[0];
	$failRedirect = $essentials->friendlyURL("");
	$info = FFI\BE\Book_Courses::getCourseInfo($course, $failRedirect);
	
//Set the page title
	$title = FFI\BE\DISPLAY_MODE == "courses" ? $info->Name : $info->Name . " " . $essentials->params[2] . " " . strtoupper($essentials->params[3]);
	
	$essentials->setTitle($title);
	
//Display the page
	echo "<h1>" . $title . "</h1>
	
";
		
//Display the welcome splash section
	$arts = array("bookshelf.jpg", "brighton-pier.jpg", "sheet-music.jpg", "romeo-and-juliet.jpg");
	$science = array("brooklyn-bridge.jpg", "fibonacci-sequence.jpg", "php.jpg", "tuscarora-mountain-tunnel.jpg");
	$rand = mt_rand(0, 3);
	$background = ($info->Type == "Arts") ? $arts[$rand] : $science[$rand];
		
	echo "<section id=\"splash\">
<div class=\"ad-container\" style=\"background-image:url(" . $essentials->normalizeURL("images/course-backgrounds/" . $background) . ")\">
<div class=\"ad-contents\">
<h2>" . $title . "</h2>
</div>
</div>
</section>

";

//This content must ONLY be displayed when the DISPLAY_MODE is set to "courses"
	if (FFI\BE\DISPLAY_MODE == "courses") {
	//Display a listing of courses which currently have books
		$currentNumber = 0;
		$sectionIteration = 1;
		$numbers = FFI\BE\Book_Overview::getNumbersWithBooks($course);

		echo "<section class=\"container\">
<h2>" . $info->Name . " Courses and Avaliable Books</h2>

<div class=\"row\">
<section class=\"details\">
";
		
		foreach($numbers as $item) {
		//This is the beginning of a new course number, e.g. from HUMA 101 to HUMA 102
			if ($currentNumber != $item->Number) {
				if ($currentNumber != 0) {
					echo "</ul>
</section>

<section class=\"content" . ($sectionIteration % 2 == 1 ? "" : " even") . "\">
<h2>" . $info->Code . " " . $item->Number . "</h2>
<ul>
";
				} else {
					echo "<section class=\"content\">
<h2>" . $info->Code . " " . $item->Number . "</h2>
<ul>
";
				}
				
				$currentNumber = $item->Number;
				$sectionIteration++;
			}
			
			echo "<li>
<a href=\"" . $essentials->friendlyURL("browse/" . $info->URL . "/" . $item->Number . "/" . strtolower($item->Section)) . "\">
<p style=\"background-color: " . $info->Color . "\">" . $item->Section . "</p>
<span>" . $item->SectionTotal . " " . ($item->SectionTotal == 1 ? "Book" : "Books") . "</span>
</a>
</li>
";
		}

		echo "</ul>
</section>
</section>

";
	//Display the sidebar
		$easterEgg = array(
			"Chemistry" => "ν = c/ƛ",
			"English" => "What's new... it's already English",
			"French" => "Quoi de neuf",
			"German" => "Was gibt es neues",
			"Greek" => "Τι νέα",
			"Hebrew" => "מה חדש",
			"Physics" => "ν = c/ƛ",
			"Spanish" => "¿Qué hay de nuevo"
		);

		echo "<aside class=\"supplement\">
<h3" . (array_key_exists($info->Name, $easterEgg) ? " class=\"haha\" data-toggle=\"tooltip\" title=\"" . htmlentities($easterEgg[$info->Name]) . "\"" : "") . ">What's New in " . $info->Name . "</h3>

" . FFI\BE\Book_Overview::getRecentBooksInCourse($info->CourseID) . "
</aside>
</div>
</section>";
//This content must ONLY be displayed when the DISPLAY_MODE is set to "books"
	} else {
	//Display a listing of books within this course section
		$condition = array("poor", "fair", "good", "very-good", "excellent");
		$failRedirect = $essentials->friendlyURL("browse/" . $essentials->params[0]);
		$books = FFI\BE\Book_Courses::getBooksInCourseSection($course, $essentials->params[2], strtolower($essentials->params[3]), $failRedirect);
		
		echo "<ul class=\"books\">
";
	
		foreach($books as $book) {
			echo "<li>
<a href=\"" . $essentials->friendlyURL("book/" . $book->SaleID . "/" . FFI\BE\Book_Overview::URLPurify($book->Title)) . "\"><img alt=\"" . htmlentities($book->Title . " Book Cover Preview") . "\" src=\"" . FFI\BE\General::bookCoverPreview($book->ImageID) . "\" /></a>
<h3><a href=\"" . $essentials->friendlyURL("book/" . $book->SaleID . "/" . FFI\BE\Book_Overview::URLPurify($book->Title)) . "\">" . $book->Title . "</a> <span>by " . $book->Author . "</span></h3>
<p class=\"merchant\"><strong>Merchant:</strong> " . $book->Name . "</p>
<p class=\"condition " . $condition[$book->Condition - 1] . "\"><strong>Condition:</strong></p>
<button class=\"btn btn-primary purchase\" data-id=\"" . $book->BookID . "\">Buy for \$" . $book->Price . ".00</button>
</a>
</li>
";
		}
	
		echo "</ul>";
	}
?>