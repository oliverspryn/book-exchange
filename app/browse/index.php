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
	$essentials->includeCSS("course.min.css");
	$essentials->includePluginClass("display/Book");
	$essentials->includePluginClass("display/Course");

	if (FFI\BE\DISPLAY_MODE == "courses") {
		$essentials->includeJS("arbor/arbor.js");
	} else {
		$essentials->includeJS("buy.min.js");
		$essentials->includeJS("//tinymce.cachefly.net/4/tinymce.min.js");
		$essentials->includeHeadHTML("<script>\$(function(){\$(document).FFI_BE_Buy(" . (is_user_logged_in() ? "{'showLogin':false}" : "") . ")})</script>");
	}
	
//Get information regarding the current course
	$course = $essentials->params[0];
	$info = FFI\BE\Course::getCourseInfo($course);
	
	if (!count($info)) {
		wp_redirect($essentials->friendlyURL(""));
		exit;
	}

//Generate the arbor.js initialization script
	if (FFI\BE\DISPLAY_MODE == "courses") {
		$essentials->includeHeadHTML("\n<script>" . FFI\BE\Course::generateArborJSInit($info->Name, $course) . "</script>");
	}
	
//Set the page title
	$title = FFI\BE\DISPLAY_MODE == "courses" ? $info->Name : $info->Name . " " . $essentials->params[2] . " " . strtoupper($essentials->params[3]);
	
	$essentials->setTitle($title);
	
//Display the page
	echo "<h1>" . $title . "</h1>
	
";
		
//Display the welcome splash section
	$arts = array("bookshelf.jpg", "parthenon.jpg", "phantom-of-the-opera.jpg", "piano.jpg");
	$science = array("brooklyn-bridge.jpg", "electronic-board.jpg", "higgs-boson.jpg", "php.jpg");
	$rand = mt_rand(0, 3);
	$background = ($info->Type == "Arts") ? $arts[$rand] : $science[$rand];
		
	echo "<section id=\"splash\">
<ul class=\"welcome-minor\">
<li><img alt=\"" . $title . " Icon\" src=\"" . $essentials->dataURL("tiles/" . $info->CourseID . "/small.png") . "\"></li>
<li>" . $title . "</li>
</ul>

<div class=\"ad-container\" style=\"background-image:url(" . $essentials->normalizeURL("system/images/course-backgrounds/" . $background) . ")\">
<div class=\"ad-contents\">
<h2" . (FFI\BE\DISPLAY_MODE == "courses" ? "" : " class=\"force-show\"") . ">" . $title . "</h2>
</div>
</div>
";

	if (FFI\BE\DISPLAY_MODE == "courses") {
		echo "
<div class=\"course-welcome\">
<img alt=\"" . $title . " Icon\" src=\"" . $essentials->dataURL("tiles/" . $info->CourseID . "/large.png") . "\">
</div>
";
	}
	
	echo "</section>

";

//This content must ONLY be displayed when the DISPLAY_MODE is set to "courses"
	if (FFI\BE\DISPLAY_MODE == "courses") {
		$total = FFI\BE\Course::totalInCourse($course);
		$sections = FFI\BE\Course::getNumbersWithBooks($course);
		
		echo "<section class=\"container\">
<h2>" . $info->Name . " and Avaliable Books</h2>
		
<div class=\"row\">
<aside class=\"supplement\">
<h2>" . $title . "</h2>
<h3>" . $total . " " . ($total == 1 ? "Book" : "Books") . " Available</h3>

<hr>

<ul class=\"navigation\">
<li class=\"more\"><a href=\"" . $essentials->friendlyURL("") . "\">See More Courses</a></li>
<li class=\"sell\"><a href=\"" . $essentials->friendlyURL("sell-books") . "\">Sell a Book</a></li>
</ul>

" . FFI\BE\Course::getRecentBooksInCourse($info->CourseID) . "
</aside>

<section class=\"details\">		
";

	if ($total) {
		echo "<section class=\"content directions\">
<h2>" . $title . "</h2>
<p>A listing of all available books within the " . $title . " course can be explored by course section <span class=\"desktop\">in the graph below. Each of the green atoms represents a course number. Move your mouse near one of the green atoms to see a pop out of course sections with available books.</span><span class=\"mobile\">in the list below.</span></p>
</section>

<canvas id=\"explorer\" height=\"640\" width=\"950\"></canvas>";
	} else {
		echo "<section class=\"content center no-border no-data\">
<h2>Nothing Available</h2>
<p>We do not currently have any books available for " . $info->Name . ". Sorry about that. :-(</p>
<p class=\"center\"><a href=\"" . $essentials->friendlyURL("sell-books") . "\" class=\"btn btn-primary\">Sell a Book</a></p>
</section>";
	}

	echo "

<section class=\"content mobile no-border\">
<h2>" . $info->Name . " Course Sections with Books</h2>

<ul>";

	foreach($sections as $section) {
		echo "
<li><a href=\"" . $essentials->friendlyURL("browse/" . $course . "/" . $section->Number . "/" . strtoupper($section->Section)) . "\"><h3>" . $info->Name . " " . $section->Number . " " . $section->Section . "</h3><span>" . $section->SectionTotal . " " . ($section->SectionTotal == 1 ? "Book" : "Books") . " Available</span></a></li>";
	}

	echo "
</ul>
</section>
</section>
</div>
</section>";
//This content must ONLY be displayed when the DISPLAY_MODE is set to "books"
	} else {
		$books = FFI\BE\Course::getBooksInCourseSection($course, $essentials->params[2], strtolower($essentials->params[3]));
		
	//Are there any books available for this section?
		if (!count($books)) {
			wp_redirect($essentials->friendlyURL("browse/" . $course));
			exit;
		}
		
		echo "<section class=\"content\">
<h2>" . $title . "</h2>
<p>A listing of all available books for " . $title . " can be seen below. <span class=\"desktop\">Move your mouse over top each of the books to see more details.</span> <span class=\"desktop\">Clicking</span><span class=\"mobile\">Touching</span> the blue &quot;Buy&quot; button will initiate a purchase request.</p>
</section>

<section class=\"book-list content no-border\">
<ul class=\"book-list\">";
	
		foreach($books as $book) {
			echo FFI\BE\Book::quickView($book->SaleID, $book->Title, $book->Author, $book->Condition, $book->Price, $book->ImageID);
		}
	
		echo "</ul>
</section>";
	}
?>