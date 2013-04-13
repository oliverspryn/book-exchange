<?php
/**
 * Book details display class
 *
 * This class is used to fetch data from the MySQL database for the 
 * book details display page. If data is returned from the database, 
 * then the respective values are made available to the rest of the
 * page. If data is returned, then the user will be redirect out of
 * the page.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

class Book_Details {
/**
 * Hold the results of the SQL query.
 *
 * @access public
 * @type   object<mixed>
*/
	
	public $data;
	
/**
 * CONSTRUCTOR
 *
 * Grab the data from the database, see if any data was returned, 
 * and if not, redirect to the URL indicated by $failRedirect.
 * 
 * @access public
 * @param  int      $ID           The ID of the book to fetch from the database
 * @param  string   $failRedirect The URL to redirect to if the SQL query returns zero tuples (i.e. an invalid $ID is given)
 * @return void
 * @since  3.0
*/

	public function __construct($ID, $failRedirect) {
		global $wpdb;
		
		if ($ID) {
			$this->data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_sale.SaleID = ffi_be_new_bookcourses.SaleID LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_bookcourses.Course = ffi_be_new_courses.CourseID WHERE ffi_be_new_sale.SaleID = %d", $ID));
			
		//SQL returned 0 tuples, "Leave me!" - http://johnnoble.net/img/photos/denethor_a.jpg
			if (!count($this->data)) {
				wp_redirect($failRedirect);
				exit;
			}
		} else {
			wp_redirect($failRedirect);
			exit;
		}
	}
}
?>