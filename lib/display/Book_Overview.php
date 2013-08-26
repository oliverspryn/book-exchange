<?php
/**
 * Book overview information class
 *
 * This class is used to fetch data from the MySQL database for 
 * fetching information regarding the collection of books which
 * are on hand. This class can retrieve information regarding
 * the total number avaliable, which class sections and numbers
 * books are avaliable, and also the listing of books avaliable
 * based on given criteria.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(__FILE__)) . "/APIs/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Isbn.php");

class Book_Overview {
	public static function getTotal() {
		global $wpdb;
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_new_sale` WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
	
	
	
	
	
	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);          //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);               //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
	
	public static function getBookByISBN($ISBN) {
		global $wpdb;
		
	//Validate the ISBN and fetch the associated book's information and list of courses in which it has been used
		if (\Isbn::validate10($ISBN)) {
			$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_books` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_books.BookID = ffi_be_new_sale.BookID LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_courses.CourseID = ffi_be_new_bookcourses.Course WHERE `ISBN10` = %s GROUP BY ffi_be_new_courses.Name, ffi_be_new_bookcourses.Number, ffi_be_new_bookcourses.Section ORDER BY ffi_be_new_courses.Name ASC, ffi_be_new_bookcourses.Number ASC, ffi_be_new_bookcourses.Section ASC", $ISBN));
		} else if (\Isbn::validate13($ISBN)) {
			$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_books` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_books.BookID = ffi_be_new_sale.BookID LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_courses.CourseID = ffi_be_new_bookcourses.Course WHERE `ISBN13` = %s GROUP BY ffi_be_new_courses.Name, ffi_be_new_bookcourses.Number, ffi_be_new_bookcourses.Section ORDER BY ffi_be_new_courses.Name ASC, ffi_be_new_bookcourses.Number ASC, ffi_be_new_bookcourses.Section ASC", $ISBN));
		} else {
			return false;
		}
		
	//Check and see if any data was returned
		if (!count($data)) {
			return false;
		}
		
	//Construct a JSON encoded object containing the book's information and list of courses in which it has been used
		$return = array(
			"ISBN10" => $data[0]->ISBN10,
			"ISBN13" => $data[0]->ISBN13,
			"title" => $data[0]->Title,
			"author" => $data[0]->Author,
			"edition" => $data[0]->Edition,
			"courses" => array()
		);
		
		foreach($data as $course) {
			array_push($return['courses'], array(
				"ID" => $course->CourseID,
				"course" => $course->Name,
				"number" => $course->Number,
				"section" => $course->Section,
				"color" => $course->Color
			));
		}
		
		return json_encode($return);
	}
}
?>
