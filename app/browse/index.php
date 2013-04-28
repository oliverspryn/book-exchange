<?php
//This page must have a course name in the URL
	if (!$essentials->params) {
		wp_redirect($essentials->friendlyURL(""));
		exit;
	}
	
//Include the necessary scripts
	$essentials->includeCSS("styles/course.css");
	$essentials->includePluginClass("display/Book_Courses");
	$essentials->includePluginClass("display/Book_Overview");

//Get information regarding the current course
	$course = $essentials->params[0];
	$failRedirect = $essentials->friendlyURL("");
	$info = FFI\BE\Book_Courses::getCourseInfo($course, $failRedirect);
	
//Set the page title
	$essentials->setTitle($info->Name);
	
//Display the page
	echo "<h1>" . $info->Name . "</h1>
	
";
	
//Display the welcome splash section
	$arts = array("bookshelf.jpg", "brighton-pier.jpg", "louis-armstrong.jpg", "romeo-and-juliet.jpg");
	$science = array("brooklyn-bridge.jpg", "fibonacci-sequence.jpg", "php.jpg", "tuscarora-mountain-tunnel.jpg");
	$rand = mt_rand(0, 3);
	$background = ($info->Type == "Arts") ? $arts[$rand] : $science[$rand];
		
	echo "<section id=\"splash\">
<div class=\"ad-container\" style=\"background-image:url(" . $essentials->normalizeURL("images/course-backgrounds/" . $background) . ")\">
<div class=\"ad-contents\">
<h2>" . $info->Name . "</h2>
</div>
</div>
</section>

";

//Display a listing of courses which currently have books
	$currentNumber = 0;
	$numbers = FFI\BE\Book_Overview::getNumbersWithBooks($course);
	
	echo "<article class=\"numbers\">
";
	
	foreach($numbers as $item) {
	//This is the beginning of a new course number, e.g. from HUMA 101 to HUMA 102
		if ($currentNumber != $item->Number) {
			echo ($currentNumber != 0) ? "</ul>\n\n<h3>" . $info->Code . "</h3>\n<ul>\n" : "<h3>" . $info->Code . "</h3>\n<ul>\n";
			
			$currentNumber = $item->Number;
		}
		
		echo "<li>" . $item->Section . " " . $item->SectionTotal . "</li>
";
	}

	echo "</ul>
</article>

";
?>