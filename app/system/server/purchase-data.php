<?php
//Include the system's core
	define("WP_USE_THEMES", false);
	require_once("../../../../../../wp-blog-header.php");
	
//Is the user logged in?
	if (!is_user_logged_in()) {
		echo "logged out";
	}
	
//Fetch all relevant data from the database
	if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
		$now = strtotime("now");
		$datagrabber = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_books.ISBN, ffi_be_books.title, ffi_be_books.author, ffi_be_books.edition, GROUP_CONCAT(ffi_be_bookcategories.name) AS name, GROUP_CONCAT(ffi_be_books.number) AS number, GROUP_CONCAT(ffi_be_books.section) AS section, GROUP_CONCAT(ffi_be_bookcategories.id) AS classID, ffi_be_books.price, ffi_be_books.condition, ffi_be_books.written, ffi_be_books.comments, ffi_be_books.imageURL, wp_users.display_name, wp_users.user_email FROM `ffi_be_books` RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.linkID = (SELECT linkID FROM ffi_be_books WHERE id = '{$_GET['id']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} LIMIT 1)");
		
		if (count($datagrabber)) {
			$data = $datagrabber[0];
			
			$overview = array();
			$overview['ISBN'] = stripslashes($data->ISBN);
			$overview['title'] = stripslashes($data->title);
			$overview['author'] = stripslashes($data->author);
			$overview['edition'] = stripslashes($data->edition);
			$overview['name'] = stripslashes($data->name);
			$overview['number'] = stripslashes($data->number);
			$overview['section'] = stripslashes($data->section);
			$overview['classID'] = stripslashes($data->classID);
			$overview['price'] = stripslashes($data->price);
			$overview['condition'] = stripslashes($data->condition);
			$overview['written'] = stripslashes($data->written);
			$overview['comments'] = stripslashes($data->comments);
			$overview['imageURL'] = stripslashes($data->imageURL);
			$overview['displayName'] = stripslashes($data->display_name);
			$overview['userEmail'] = stripslashes($data->user_email);		
						
			echo json_encode($overview);
		} else {
			echo "failed";
		}
	} else {
		echo "failed";
	}
?>