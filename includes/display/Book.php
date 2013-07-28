<?php
/**
 * Book information class
 *
 * This class is used to fetch all available information regarding
 * books. Some of this classes abilities include:
 *  - fetch the details of a book by its ID
 *  - fetch the total number of available books
 *  - generate a book quick view object
 *  - generate links to various styles of book covers
 * 
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

class Book {
/**
 * Fetch the details of a book by its ID
 *
 * @access public
 * @param  int           $ID           The ID of the book to fetch from the database
 * @return object<mixed>               The object returned from the SQL query containing all available book data
 * @since  3.0
 * @static
*/
	
	public static function details($ID) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_sale.SaleID = ffi_be_new_bookcourses.SaleID LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_bookcourses.Course = ffi_be_new_courses.CourseID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `MerchantName` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_new_sale.Merchant = users.ID WHERE ffi_be_new_sale.SaleID = %d ORDER BY `Number` ASC, `Section` ASC", $ID));
	}
	
	
/**
 * This function will generate the HTML for a quick view object
 * for a particular book. The final presentation of a quick view 
 * object will look like this:
 *
 *     |------------------------|
 *     |                        |
 *     |                        |
 *     |                        |
 *     |                        |
 *     |         Book           |
 *     |         Cover          |
 *     |         Image          |
 *     |         Here           |
 *     |                        |
 *     |                        |
 *     |   |----------------|   |
 *     |---|  <Book Title>  |---|
 *         |----------------|
 *
 * The object will transition into an expanded view when the user
 * rolls his or her mouse over it to show the author, merchant, 
 * condition, and price.
 *
 * @access public
 * @param  string $title     The title of the book
 * @param  string $author    The author of the book
 * @param  string $merchant  The name of the merchant selling this book
 * @param  int    $condition A numerical value (1 - 5) indicating the book's condition, 5 being excellent
 * @param  int    $price     The price of the book, rounded to the dollar
 * @param  string $imageID   The ID of the image of the book
 * @return void
 * @since  3.0
 * @static
*/

	public static function quickView($title, $author, $merchant, $condition, $price, $imageID) {
		
	}
}
?>
