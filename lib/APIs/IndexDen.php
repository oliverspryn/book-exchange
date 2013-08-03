<?php
/**
 * IndexDen API class
 *
 * This class is designed to interact with the IndexDen
 * indexing service in every way that that application
 * will require. Some of its functionality includes:
 *  - adding entries to the index
 *  - updating index entries
 *  - remove index entries
 *  - refresh the entire index
 *  - search the index
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.APIs
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/indextank.php");
require_once(dirname(__FILE__) . "/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Indexden_Syntax_Error.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Invalid_Indexden_Results.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Invalid_Search_Parameter.php");

class IndexDen {
/**
 * Delete a document from the IndexDen index by its document ID. The
 * document ID is the same as the ID of a book in the ffi_be_sale 
 * table.
 * 
 * @access public
 * @param  int                               $ID The ID of the document to remove from the IndexDen index
 * @return void
 * @throws Indextank_Exception_HttpException     [Bubbled up] Thrown in the event there is an IndexDen communication or processing error
 * @since  3.0
 * @static
*/
	
	public static function delete($ID) {
		global $wpdb;

	//Gather data about the index
		$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_new_apis`");
		$URL = $APIData[0]->IndexDenUsername . ":" . $APIData[0]->IndexDenPassword . "@" . $APIData[0]->IndexDenURL;
		$indexName = $APIData[0]->IndexDenIndex;
		
	//Use a third-party library to send the delete request
		$API = new \Indextank_Api($URL);
		$index = $API->get_index($indexName);
		$index->delete_document($ID);
	}
	
/**
 * Search the IndexDen index and combine the results with data
 * from the local database to generate a fully functional search
 * engine.
 *
 * Query input can use the formatting as described in the IndexDen
 * documentation at: http://www.indexden.com/documentation/query-syntax
 * It cannot, however, contain the words "title:" or "author:" or
 * end with the words "and ", "or ", or "not ".
 *
 * Results from this method will be a JSON encoded array of books
 * containing the ID, title, author, price, condition, and absolute
 * URL of the book cover image.
 * 
 * @access public
 * @param  string                   $query The query to perform on the IndexDen service
 * @param  string                   $by    The set of data to search by, either "author" or "title"
 * @param  int                      $in    The course ID to search in, "0" means all courses
 * @param  int                      $page  The page number from which the results should begin
 * @param  int                      $limit The maximum number of results to return
 * @return string                          A JSON encoded array of search results, containing the title, author, etc...
 * @throws Invalid_Search_Parameter        Thrown when a parameter does not pass validation
 * @throws Indexden_Syntax_Error           Thrown when IndexDen determines that the search query is invalid
 * @throws Invalid_Indexden_Results        Thrown when IndexDen returns invalid data or a server communication error occurs
 * @since  3.0
 * @static
*/
	public static function search($query, $by, $in, $sort, $page = 1, $limit = 25) {
		global $wpdb;
		
	//Validate the query
		if ($query == "" || $query == NULL) {
			throw new Invalid_Search_Parameter("The search query is empty");
		}
		
	//Validate the search by type information
		$searchBy = array("title", "author");
	
		if (!in_array(strtolower($by), $searchBy)) {
			throw new Invalid_Search_Parameter("Searches can only be performed by title or author");
		}
		
	//Validate the search in course information
		$courses = $wpdb->get_col("SELECT `CourseID` FROM `ffi_be_new_courses`");
		array_push($courses, 0); //Course ID of "0" means "all courses", in this case
		
		if (!in_array($in, $courses)) {
			throw new Invalid_Search_Parameter("The specified course does not exist");
		}
		
	//Validate the sorting criteria
		$sorting = array(
			"relevance"   => "",
			"title-asc"   => " ORDER BY `Title` ASC, `Price` ASC ",
			"title-desc"  => " ORDER BY `Title` DESC, `Price` ASC ",
			"price-asc"   => " ORDER BY `Price` ASC, `Title` ASC ",
			"price-desc"  => " ORDER BY `Price` DESC, `Title` ASC ",
			"author-asc"  => " ORDER BY `Author` ASC, `Title` ASC ",
			"author-desc" => " ORDER BY `Author` DESC, `Title` ASC "
		);
		
		if (!array_key_exists($sort, $sorting)) {
			throw new Invalid_Search_Parameter("Search results cannot be sorted on the specified criteria");
		}
		
	//Validate the page number and query results limit
		if ($page <= 0) {
			throw new Invalid_Search_Parameter("Search results begin on page 1");
		}
		
		if ($limit <= 0) {
			throw new Invalid_Search_Parameter("Search results must return at least 1 result");
		}
		
	//Run the search query on the indexing service
		$index = $wpdb->get_results("SELECT `IndexDenURL`, `IndexDenIndex` FROM `ffi_be_new_apis`");
		$URL = $index[0]->IndexDenURL . "/v1/indexes/" . $index[0]->IndexDenIndex . "/search?q=" . strtolower($by) . ":" . urlencode($query) . "&snippet=" . strtolower($by);
		
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, $URL);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
		$search = json_decode(curl_exec($handle));
		curl_close($handle);
		
	//Validate the data returned from the server
		if (!is_object($search)) {
		//User entered an invalid IndexDen search query
			if ($search == "Query failed") {
				throw new Indexden_Syntax_Error("Your search query may not contain the words \"title:\" or \"author:\" (with the colon on the end), or end with the words \"and \", \"or \", or \"not \" (with a space on the end). Please try your search again."); //"title:" or "author:" is already added by the server, see documentation: http://www.indexden.com/documentation/query-syntax
		//Another IndexDen error type was encountered
			} else {
				$error = "";
				
			//Capture the var_dump() without echoing
				ob_start();
				var_dump($search);
				$error = ob_get_contents();
				ob_end_clean();
			
				throw new Invalid_Indexden_Results($error);
			}
		}
		
	//Use the above information from IndexDen to generate a list of IDs to fetch from the local database
		$IDs = "";
		
		foreach($search->results as $item) {
			$IDs .= "'" . esc_sql($item->docid) . "', ";
		}

	//Narrow down the results by course
		$course = ($in != 0) ? " AND `Course` = '" . $in . "' " : " ";

	//Execute the local SQL query		
		$SQL = "SELECT * FROM (SELECT * FROM `ffi_be_new_sale` LEFT JOIN (SELECT `BookID` AS `BID`, `ISBN10`, `ISBN13`, `Title`, `Author`, `ImageID` FROM `ffi_be_new_books`) `books` ON ffi_be_new_sale.BookID = books.BID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_new_sale.Merchant = users.ID LEFT JOIN (SELECT `SaleID` AS `SID`, `Course` FROM `ffi_be_new_bookcourses`) `courses` ON ffi_be_new_sale.SaleID = courses.SID WHERE `SaleID` IN(" . rtrim($IDs, ", ") . ") AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0'" . $course . "ORDER BY FIELD(`SaleID`, " . rtrim($IDs, ", ") . ")) `q` GROUP BY `SaleID`" . $sorting[$sort] . "LIMIT " . (($page - 1) * $limit) . ", " . $limit;
		$results = $wpdb->get_results($SQL);
		
	//Build the return output
		$counter = 0;
		$return = array();
		
		foreach($results as $item) {
		//The values from the database are out of order with the results from IndexDen, do not use
			//$title = strtolower($by) == "title" ? $search->results[$counter++]->snippet_Title : $item->Title; //Get the title with highlighted keywords
			//$author = strtolower($by) == "author" ? $search->results[$counter++]->snippet_Author : $item->Author; //Get the author with highlighted keywords
			
			array_push($return, array(
				"author" => $item->Author,
				"condition" => $item->Condition,
				"imageURL" => Cloudinary::coverPreview($item->ImageID),
				"ID" => $item->SaleID,
				"merchant" => $item->Name,
				"price" => $item->Price,
				"title" => $item->Title
			));
		}
		
		return json_encode($return);
	}
}
?>
