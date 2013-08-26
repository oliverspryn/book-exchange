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
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includeCSS("styles/course.css");
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includePluginClass("display/Book");
	$essentials->includePluginClass("display/Course");

	if (FFI\BE\DISPLAY_MODE == "courses") {
		$essentials->includeJS("scripts/arbor/arbor.js");
	} else {
		$essentials->includeJS("scripts/buy.min.js");
		$essentials->includeJS("//tinymce.cachefly.net/4/tinymce.min.js");
		$essentials->includeHeadHTML("<script>(function(\$){\$(function(){\$(document).FFI_BE_Buy(" . (is_user_logged_in() ? "{'showLogin':false}" : "") . ")})})(jQuery);</script>");
	}

//Get information regarding the current course
	$course = $essentials->params[0];
	$failRedirect = $essentials->friendlyURL("");
	$info = FFI\BE\Course::getCourseInfo($course, $failRedirect);

//Generate the arbor.js initialization script
	if (FFI\BE\DISPLAY_MODE == "courses") {
		$completedNum = array();
		$courseListing = FFI\BE\Course::getNumbersWithBooks($course);
		$JS = "\$(function(){var graph={nodes:{'" . $info->Name . "':{alpha:1,color:'#0044CC',shape:'dot'}";

	//List the course numbers, without duplicates
		foreach($courseListing as $courseInfo) {
			if (!in_array($courseInfo->Number, $completedNum)) {
				$JS .= ",'" . $courseInfo->Number . "':{alpha:1,color:'#A7AF00',shape:'dot'}";
				array_push($completedNum, $courseInfo->Number);
			}
		}

	//List the course sections
		foreach($courseListing as $courseInfo) {
			$JS .= ",'" . $courseInfo->Number . " " . $courseInfo->Section . "':{alpha:0,color:'orange',link:'" . $essentials->friendlyURL("browse/" . $course . "/" . $courseInfo->Number . "/" . $courseInfo->Section) . "'}";
		}

		$JS .= "},edges:{'" . $info->Name . "':{";

	//Connect the course numbers to the primary node
		foreach($courseListing as $courseInfo) {
			$JS .= "'" . $courseInfo->Number . "':{length:0.8},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "},";

	//Connect the course sections to the course numbers
		foreach($completedNum as $courseNum) {
			$JS .= "'" . $courseNum . "':{";

			foreach($courseListing as $courseInfo) {
				$JS .= $courseInfo->Number == $courseNum ? ("'" . $courseInfo->Number . " " . $courseInfo->Section . "':{},") : "";
			}

			$JS = rtrim($JS, ",");
			$JS .= "},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "}};var sys=arbor.ParticleSystem();sys.parameters({dt:0.015,gravity:true,repulsion:2000,stiffness:900});sys.renderer=Renderer('#explorer');sys.graft(graph);})";

		$essentials->includeHeadHTML("\n<script>" . $JS . "</script>");
	}
	
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

<div class=\"course-welcome\">
<img alt=\"" . $title . " Icon\" src=\"" . $essentials->dataURL("tiles/" . $info->CourseID . "/large.png") . "\">
</div>
</section>

<section class=\"container\">
<h2>" . $info->Name . " Courses and Avaliable Books</h2>

<div class=\"row\">
";

	//Display the sidebar
	echo "<aside class=\"supplement\">
<h2>" . $title . "</h2>
<h3>5 Books Available</h3>

<hr>

<ul class=\"navigation\">
<li class=\"more\"><a href=\"" . $essentials->friendlyURL("") . "\">See More Courses</a></li>
<li class=\"sell\"><a href=\"" . $essentials->friendlyURL("sell-books") . "\">Sell a Book</a></li>
</ul>

" . FFI\BE\Course::getRecentBooksInCourse($info->CourseID) . "
</aside>

<section class=\"details\">
";

//This content must ONLY be displayed when the DISPLAY_MODE is set to "courses"
	if (FFI\BE\DISPLAY_MODE == "courses") {
		echo "<section class=\"content\">
<h2>" . $title . "</h2>
<p>A listing of all available books within the " . $title . " course can be explored by course section in the graph below. Each of the green atoms represents a course number. <span class=\"desktop\">Move your mouse near</span><span class=\"mobile\">Touch</span> one of the green atoms to see a pop out of course sections with available books.</p>
</section>

<canvas id=\"explorer\" height=\"640\" width=\"950\"></canvas>";
//This content must ONLY be displayed when the DISPLAY_MODE is set to "books"
	} else {
		$failRedirect = $essentials->friendlyURL("browse/" . $essentials->params[0]);
		$books = FFI\BE\Course::getBooksInCourseSection($course, $essentials->params[2], strtolower($essentials->params[3]), $failRedirect);
		
		echo "<section class=\"content\">
<h2>" . $title . "</h2>
<p>A listing of all available books for " . $title . " can be seen below. <span class=\"desktop\">Move your mouse over top each of the books to see more details.</span> Clicking the blue &quot;Buy&quot; button will initiate a purchase request.</p>
</section>

<section class=\"book-list content no-border\">
<ul class=\"book-list\">";
	
		foreach($books as $book) {
			echo FFI\BE\Book::quickView($book->SaleID, $book->Title, $book->Author, $book->Condition, $book->Price, $book->ImageID);
		}
	
		echo "</ul>
</section>";
	}

	echo "
</section>
</div>
</section>";
?>
