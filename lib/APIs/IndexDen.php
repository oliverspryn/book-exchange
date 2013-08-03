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
 * @package   lib.APIs
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(__FILE__) . "/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Indexing_Error.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/indextank.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/HttpException.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/IndexAlreadyExists.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/InvalidQuery.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/TooManyIndexes.php");

class IndexDen {
/**
 * Delete a document from the IndexDen index by its document ID. The
 * document ID is the same as the ID of a book in the ffi_be_sale 
 * table.
 * 
 * @access public
 * @param  int                               $ID The ID of the document to remove from the IndexDen index
 * @return void
 * @throws Indextank_Exception_HttpException     [Bubbled up] Thrown in the event of an IndexDen communication error
 * @since  3.0
 * @static
*/
	
	public static function delete($ID) {
		global $wpdb;

	//Gather data about the index
		$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_apis`");
		$URL = $APIData[0]->IndexDenUsername . ":" . $APIData[0]->IndexDenPassword . "@" . $APIData[0]->IndexDenURL;
		$indexName = $APIData[0]->IndexDenIndex;
		
	//Use a third-party library to send the delete request
		$API = new \Indextank_Api($URL);
		$index = $API->get_index($indexName);
		$index->delete_document($ID);
	}

/**
 * Reload the contents of an IndexDen index by deleting the index
 * entirelly, creating the index again, and pushing all available
 * data from the local database to the index.
 * 
 * @access public
 * @return void
 * @throws Indexing_Error                         Thrown in the event that IndexDen cannot index the batch of documents
 * @throws Indextank_Exception_HttpException      [Bubbled up] Thrown in the event of an IndexDen communication error
 * @throws Indextank_Exception_IndexAlreadyExists [BUbbled up] Thrown if an index with the same name already exists
 * @throws Indextank_Exception_TooManyIndexes     [Bubbled up] Thrown if the IndexDen account has too many existing indexes
 * @since  3.0
 * @static
*/

	public static function reloadIndex() {
		global $wpdb;

	//Gather data about the index
		$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_apis`");
		$URL = $APIData[0]->IndexDenUsername . ":" . $APIData[0]->IndexDenPassword . "@" . $APIData[0]->IndexDenURL;
		$indexName = $APIData[0]->IndexDenIndex;

	//Completely delete the old index (it's the easiest way to purge it out)
		$oldAPI = new \Indextank_Api($URL);
		$oldIndex = $oldAPI->get_index($indexName);
		$oldIndex->delete_index();

	//Create a new index with the same name
		$newAPI = new \Indextank_Api($URL);
		$newIndex = $newAPI->create_index($indexName, true); //true is IMPORTANT!!! It allows the plugin to search the index!

	//Starting the index can take a few seconds...
		while (!$newIndex->has_started()) {
			sleep(1); //evil >:D
		}

	//Construct the new set of data
		$indexData = array();
		$data = $wpdb->get_results("SELECT ffi_be_sale.SaleID, `Title`, `Author`, `ISBN10`, `ISBN13` FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND `Sold` = '0'");

		foreach($data as $item) {
			array_push($indexData, array("docid" => $item->SaleID, "fields" => array(
				"title"  => $item->Title,
				"author" => $item->Author
			)));
		}

	//Send the new data set to the index
		try {
			$response = $newIndex->add_documents($indexData);
		} catch (InvalidArgumentException $e) {
			echo "Internal processing error: <br>" . $e->getMessage();
			exit;
		}
		
	//Check to see if any errors occured during indexing
		foreach($response as $check) {
			if (!$check->added) {
				throw new Indexing_Error("IndexDen could not index the batch of documents. Indexing stopped at: " . $check);
			}
		}
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
 * @param  string                            $query The query to perform on the IndexDen service
 * @param  string                            $by    The set of data to search by, either "author" or "title"
 * @param  int                               $in    The course ID to search in, "0" means all courses
 * @param  int                               $page  The page number from which the results should begin
 * @param  int                               $limit The maximum number of results to return
 * @return string                                   A JSON encoded array of search results, containing the title, author, etc...
 * @throws Indextank_Exception_HttpException        [Bubbled up] Thrown in the event of an IndexDen communication error
 * @throws Validation_Failed                        Thrown when a parameter does not pass validation or has an IndexDen sytax error
 * @since  3.0
 * @static
*/

	public static function search($query, $by, $in, $sort, $page = 1, $limit = 12) {
		global $wpdb;
		
	//Validate the query
		if ($query == "" || $query == NULL) {
			throw new Validation_Failed("The search query is empty");
		}
		
	//Validate the search by type information
		$searchBy = array("title", "author");
	
		if (!in_array(strtolower($by), $searchBy)) {
			throw new Validation_Failed("Searches can only be performed by title or author");
		}
		
	//Validate the search in course information
		$courses = $wpdb->get_col("SELECT `CourseID` FROM `ffi_be_courses`");
		array_push($courses, 0); //Course ID of "0" means "all courses", in this case
		
		if (!in_array($in, $courses)) {
			throw new Validation_Failed("The specified course does not exist");
		}
		
	//Validate the sorting criteria
		$sorting = array(
			"relevance"   => "",
			"title-asc"   => " ORDER BY `Title` ASC, `Price` ASC ",
			"title-desc"  => " ORDER BY `Title` DESC, `Price` ASC ",
			"price-asc"   => " ORDER BY `Price` ASC, `Title` ASC ",
			"price-desc"  => " ORDER BY `Price` DESC, `Title` ASC ",
			"author-asc"  => " ORDER BY `Author` ASC, `Title` ASC, `Price` ASC ",
			"author-desc" => " ORDER BY `Author` DESC, `Title` ASC, `Price` ASC "
		);
		
		if (!array_key_exists($sort, $sorting)) {
			throw new Validation_Failed("Search results cannot be sorted on the specified criteria");
		}
		
	//Validate the page number and query results limit
		if ($page <= 0) {
			throw new Validation_Failed("Search results begin on page 1");
		}
		
		if ($limit <= 0) {
			throw new Validation_Failed("Search results must return at least 1 result");
		}
		
	//Run the search query on the indexing service
		$APIData = $wpdb->get_results("SELECT `IndexDenURL`, `IndexDenIndex` FROM `ffi_be_apis`");
		$URL = $APIData[0]->IndexDenURL;
		$indexName = $APIData[0]->IndexDenIndex;

		try {
			$API = new \Indextank_Api($URL);
			$index = $API->get_index($indexName);
			$response = $index->search(strtolower($by) . ":" . $query, 0, 15000);
		} catch (\Indextank_Exception_InvalidQuery $e) {
			throw new Validation_Failed("Your search query may not contain the words \"title:\" or \"author:\" (with the colon on the end), or end with the words \"and \", \"or \", or \"not \" (with a space on the end). Please try your search again."); //"title:" or "author:" is already added by the server, see documentation: http://www.indexden.com/documentation/query-syntax
		}
		
	//Use the above information from IndexDen to generate a list of IDs to fetch from the local database
		$IDs = "";
		
		foreach($response->results as $item) {
			$IDs .= "'" . esc_sql($item->docid) . "', ";
		}

	//Narrow down the results by course
		$course = ($in != 0) ? " AND `Course` = '" . $in . "' " : " ";

	//Execute the local SQL query		
		$SQL = "SELECT * FROM (SELECT * FROM `ffi_be_sale` LEFT JOIN (SELECT `BookID` AS `BID`, `ISBN10`, `ISBN13`, `Title`, `Author`, `ImageID` FROM `ffi_be_books`) `books` ON ffi_be_sale.BookID = books.BID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_sale.MerchantID = users.ID LEFT JOIN (SELECT `SaleID` AS `SID`, `Course` FROM `ffi_be_bookcourses`) `courses` ON ffi_be_sale.SaleID = courses.SID WHERE `SaleID` IN(" . rtrim($IDs, ", ") . ") AND DATE_ADD(ffi_be_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'" . $course . "ORDER BY FIELD(`SaleID`, " . rtrim($IDs, ", ") . ")) `q` GROUP BY `SaleID`" . $sorting[$sort] . " LIMIT " . (($page - 1) * $limit) . ", " . $limit;
		$results = $wpdb->get_results($SQL);
		
	//Build the return output
		$counter = 0;
		$return = array();
		
		foreach($results as $item) {
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
