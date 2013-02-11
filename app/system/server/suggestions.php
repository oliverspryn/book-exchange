<?php
//Include the system's core
	define("WP_USE_THEMES", false);
	require_once("../../../../../../wp-blog-header.php");
	require_once("Validate.php");
	
//Perform a search operation on the database
	if (isset($_GET['term']) && $_GET['term'] != "") {
		$query = mysql_real_escape_string(urldecode($_GET['term']));
		$category = mysql_real_escape_string(urldecode(Validate::numeric($_GET['category'])));
		$searchBy = mysql_real_escape_string(urldecode(Validate::required($_GET['searchBy'], array("title", "author", "ISBN", "course", "seller"))));
		
	//Search by a specific category
		if ($category != 0) {
			$category = " AND ffi_be_books.course = '" . $category . "'";
		} else {
			$category = "";
		}		
		
	//Different search methods will vary the query that is executed on the database
		$now = strtotime("now");
		
		switch($searchBy) {
			case "title" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT ffi_be_books.linkID) AS total, MIN(ffi_be_books.price) AS price FROM ffi_be_books RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY title ORDER BY score DESC, ffi_be_books.title ASC LIMIT 5");
				break;
				
			case "author" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT ffi_be_books.linkID) AS total, MIN(ffi_be_books.price) AS price FROM ffi_be_books RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY author ORDER BY score DESC, ffi_be_books.title ASC LIMIT 5");
				break;
				
			case "ISBN" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, COUNT(DISTINCT ffi_be_books.linkID) AS total, MIN(ffi_be_books.price) AS price FROM ffi_be_books RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ISBN = '{$query}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY ISBN ORDER BY ffi_be_books.title ASC LIMIT 5");
				break;
				
			case "course" : 
				$number = substr($query, strlen($query) - 5, strlen($query) - 2);
				$section = substr($query, strlen($query) - 1, strlen($query));
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_bookcategories.name, ffi_be_exchangesettings.expires, COUNT(DISTINCT ffi_be_books.linkID) AS total, MIN(ffi_be_books.price) AS price FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE number = '{$number}' AND section = '{$section}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY ffi_be_books.course, ffi_be_books.number, ffi_be_books.section ORDER BY ffi_be_books.title ASC LIMIT 5");
				break;
				
			case "seller" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, wp_users.*, ffi_be_exchangesettings.expires, MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT ffi_be_books.linkID) AS total, MIN(ffi_be_books.price) AS price FROM wp_users RIGHT JOIN(ffi_be_books) ON wp_users.ID = ffi_be_books.userID RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY wp_users.display_name ORDER BY score DESC, ffi_be_books.title ASC LIMIT 5");
				break;
				
			default : 
				exit;
				break;
		}
	} else {
		exit;
	}

//Display the results of the search
	$output = array();
	
	foreach($searchGrabber as $search) {
		switch($searchBy) {
			case "title" : 
				array_push($output, array("label" => stripslashes($search->title), "byLine" => "Author: " . stripslashes($search->author), "image" => stripslashes($search->imageURL), "total" => stripslashes($search->total), "price" => stripslashes($search->price)));
				break;
				
			case "author" : 
				$image = $essentials->normalizeURL("system/images/icons/author.png");
				array_push($output, array("label" => stripslashes($search->author), "byLine" => "", "image" => $image, "total" => stripslashes($search->total), "price" => stripslashes($search->price)));
				break;
				
			case "ISBN" : 
				array_push($output, array("label" => stripslashes($search->ISBN), "byLine" => "Title: " . stripslashes($search->title), "image" => stripslashes($search->imageURL), "total" => stripslashes($search->total), "price" => stripslashes($search->price)));
				break;
				
			case "course" : 
				$image = $essentials->normalizeURL("system/images/categories/" . $search->course . "/icon_032.png");
				array_push($output, array("ID" => stripslashes($search->course), "label" => stripslashes($search->number) . " " . stripslashes($search->section), "byLine" => "", "image" => $image, "total" => stripslashes($search->total), "price" => stripslashes($search->price)));
				break;
				
			case "seller" : 
				$image = $essentials->normalizeURL("system/images/icons/seller.png");
				array_push($output, array("label" => stripslashes($search->display_name), "byLine" => "", "image" => $image, "total" => stripslashes($search->total), "price" => stripslashes($search->price)));
				break;
		}
			
	}
	
	echo json_encode($output);
	exit;
?>