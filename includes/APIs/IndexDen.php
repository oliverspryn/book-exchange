<?php
namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(__FILE__)) . "/display/General.php");

class IndexDen {
	public static function search($query, $by, $in, $sort, $page = 1, $limit = 25) {
		global $wpdb;
		
	//Validate the query
		if ($query == "" || $query == NULL) {
			return false;
		}
		
	//Validate the search by type information
		$searchBy = array("title", "author");
	
		if (!in_array(strtolower($by), $searchBy)) {
			return false;
		}
		
	//Validate the search in course information
		$courses = $wpdb->get_col("SELECT `CourseID` FROM `ffi_be_new_courses`");
		array_push($courses, 0);
		
		if (!in_array($in, $courses)) {
			return false;
		}
		
	//Validate the sorting criteria
		$sorting = array("title-asc", "title-desc", "price-asc", "price-desc", "author-asc", "author-desc");
		
		if (!in_array($sort, $sorting)) {
			return false;
		}
		
	//Validate the page number and query results limit
		if ($page <= 0 || $limit <= 0) {
			return false;
		}
		
	//Run the search query on the indexing service
		$indexURL = $wpdb->get_results("SELECT * FROM `ffi_be_new_apis`");
		$URL = $indexURL[0]->IndexDenURL . "/v1/indexes/" . $indexURL[0]->IndexDenIndex . "/search?q=" . $by . ":" . urlencode($query) . "&snippet=" . $by;
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $URL);
		curl_setopt($handle, CURLOPT_PROXY, "proxy.gcc.edu:8080"); //Grrr.... GCC proxy
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		$search = json_decode(curl_exec($handle));
		curl_close($handle);
		
	//Use the aabove information to build a local query
		$IDs = "";
		
		foreach($search->results as $item) {
			$IDs .= "`SaleID` = '" . esc_sql($item->docid) . "' OR ";
		}
		
		$SQL = "SELECT * FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_new_sale.Merchant = users.ID WHERE " . rtrim($IDs, " OR ");
		$results = $wpdb->get_results($SQL);
		
	//Build the return output
		$counter = 0;
		$return = array();
		
		foreach($results as $item) {
			$title = strtolower($by) == "title" ? $search->results[$counter++]->snippet_Title : $item->Title; //Get the title with highlighted keywords
			$author = strtolower($by) == "author" ? $search->results[$counter++]->snippet_Author : $item->Author; //Get the author with highlighted keywords
			
			array_push($return, array(
				"author" => $author,
				"condition" => $item->Condition,
				"imageURL" => General::bookCoverPreview($item->ImageID),
				"ID" => $item->BookID,
				"merchant" => $item->Name,
				"price" => $item->Price,
				"title" => $title
			));
		}
		
		return json_encode($return);
	}
}
?>