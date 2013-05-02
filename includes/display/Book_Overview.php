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

require_once(dirname(dirname(__FILE__)) . "/display/General.php");

class Book_Overview {
	public static function getTotal() {
		global $wpdb;
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_new_sale` WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
	
	public static function getNumbersWithBooks($courseURL) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT `Number`, `Section`, COUNT(*) AS `SectionTotal` FROM (SELECT ffi_be_new_bookcourses.Number, ffi_be_new_bookcourses.Section FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN ffi_be_new_courses ON ffi_be_new_courses.CourseID = ffi_be_new_bookcourses.Course WHERE `Sold` = 0 AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_courses.URL = %s GROUP BY ffi_be_new_sale.SaleID, ffi_be_new_bookcourses.Number ORDER BY `Number` ASC, `Section` ASC) AS `CourseBooks` GROUP BY `Number`, `Section`", $courseURL));
	}
	
	public static function getRecentBooksInCourse($courseID, $limit = 5) {
		global $wpdb;
		global $essentials;
		
	//Fetch the newest books
		$books = $wpdb->get_results($wpdb->prepare("SELECT ffi_be_new_sale.SaleID, ffi_be_new_books.Title, ffi_be_new_sale.Price, ffi_be_new_books.ImageID, ffi_be_new_courses.Name AS CourseName, ffi_be_new_courses.URL AS CourseURL FROM ffi_be_new_sale LEFT JOIN ffi_be_new_books ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN ffi_be_new_bookcourses ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN ffi_be_new_courses ON ffi_be_new_courses.CourseID = ffi_be_new_bookcourses.Course WHERE ffi_be_new_courses.CourseID = %d AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0' GROUP BY ffi_be_new_sale.SaleID ORDER BY ffi_be_new_sale.Upload DESC LIMIT %d", $courseID, $limit));
		
	//Write out the HTML for each of the newest books
		$return = "<ul>
";
	
		foreach($books as $book) {
			$return .= "<li style=\"background-image: url(" . General::bookBackgroundSmall($book->ImageID) . ")\">
<a href=\"" . $essentials->friendlyURL("book/" . $book->SaleID . "/" . self::URLPurify($book->Title)) . "\">
<h3>" . $book->Title . "</h3>
<p class=\"price\">\$" . $book->Price . ".00</p>
</a>
</li>
";
		}
		
		$return .= "</ul>";
		return $return;
	}
	
	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);          //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);               //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>