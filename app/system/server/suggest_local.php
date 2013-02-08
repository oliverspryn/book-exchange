<?php
//Include the system's core
	define("WP_USE_THEMES", false);
	require_once("../../../../../../wp-blog-header.php");
	
//Check and see if a given ISBN number exists in the database
	if (isset($_GET['ISBN']) && strlen($_GET['ISBN']) == 10) {
		$classes = array();
		$returnArray = array();
		$counter = 0;
		$ISBN = $_GET['ISBN'];
		$ISBNGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_bookcategories.name, ffi_be_bookcategories.course AS courseID, ffi_be_bookcategories.color1 FROM `ffi_be_books` RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id WHERE ffi_be_books.ISBN = '{$ISBN}' ORDER BY ffi_be_bookcategories.course, ffi_be_books.number, ffi_be_books.section ASC");
		
		for($i = 0; $i < count($ISBNGrabber); $i++) {
			$ISBNData = $ISBNGrabber[$i];
			
		//We only need this info once
			if (++$counter == 1) {
				$returnArray['ISBN'] = stripslashes($ISBNData->ISBN);
				$returnArray['title'] = stripslashes($ISBNData->title);
				$returnArray['author'] = stripslashes($ISBNData->author);
				$returnArray['edition'] = stripslashes($ISBNData->edition);
				$returnArray['imageID'] = stripslashes($ISBNData->imageID);
				
			//Suggest the REAL cover image, even if it hasn't been approved yet
				if (!empty($ISBNData->awaitingImage)) {
					$returnArray['imageURL'] = stripslashes($ISBNData->awaitingImage);
				} else {
					$returnArray['imageURL'] = stripslashes($ISBNData->imageURL);
				}
			}
			
		//Collect all of the classes in which this book is used
			array_push($classes, array("id" => stripslashes($ISBNData->course), "name" => stripslashes($ISBNData->name), "collegeCID" => stripslashes($ISBNData->courseID), "classNum" => stripslashes($ISBNData->number), "section" => stripslashes($ISBNData->section), "color" => stripslashes($ISBNData->color1)));
		}
		
	//Was any data collected about for this ISBN?
		if ($classes && sizeof($classes) > 0) {	
		//Remove duplicate entries from classes array. array_unique() will not work with multideminsional arrays
			$classes = array_intersect_key($classes, array_unique(array_map('serialize', $classes)));
		
			$returnArray['classes'] = array_merge($classes);
			echo json_encode($returnArray);
		} else {
			echo "failure";
		}
	} else {
		echo "failure";
	}
?>