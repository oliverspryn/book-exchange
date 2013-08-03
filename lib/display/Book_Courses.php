<?php
/**
 * Book courses information class
 *
 * This class is used to fetch data from the MySQL database for 
 * information regarding avaliable book courses. It offers a
 * variety of interfaces which can be used to acess various types
 * and aspects of course-related data.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

class Book_Courses {
	public static function getCourses() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT * FROM `ffi_be_new_courses` ORDER BY `Name` ASC");
	}
	
	public static function getAL() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_new_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_new_courses` LEFT JOIN (SELECT ffi_be_new_courses.*, COUNT(*) AS `Total` FROM (SELECT ffi_be_new_bookcourses.Course AS `CourseID` FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID WHERE DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0' GROUP BY ffi_be_new_bookcourses.SaleID) `info` LEFT JOIN `ffi_be_new_courses` ON info.CourseID = ffi_be_new_courses.CourseID GROUP BY info.CourseID) AS `info` ON ffi_be_new_courses.CourseID = info.CourseID WHERE ffi_be_new_courses.Type = 'Arts' ORDER BY ffi_be_new_courses.Name ASC");
	}
	
	public static function getSEM() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_new_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_new_courses` LEFT JOIN (SELECT ffi_be_new_courses.*, COUNT(*) AS `Total` FROM (SELECT ffi_be_new_bookcourses.Course AS `CourseID` FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID WHERE DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0' GROUP BY ffi_be_new_bookcourses.SaleID) `info` LEFT JOIN `ffi_be_new_courses` ON info.CourseID = ffi_be_new_courses.CourseID GROUP BY info.CourseID) AS `info` ON ffi_be_new_courses.CourseID = info.CourseID WHERE ffi_be_new_courses.Type = 'Science' ORDER BY ffi_be_new_courses.Name ASC");
	}
	
	public static function getCourseInfo($courseURL, $failRedirect) {
		global $wpdb;
		$info = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_courses` WHERE `URL` = %s", $courseURL));
		
	//Check if any data was returned
		if (!count($info)) {
			wp_redirect($failRedirect);
			exit;
		}
		
		return $info[0];
	}
	
	public static function getBooksInCourseSection($courseURL, $number, $section, $failRedirect) {
		global $wpdb;
		$books = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_bookcourses.Course = ffi_be_new_courses.CourseID LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_new_sale.Merchant = users.ID WHERE `URL` = %s AND `Number` = %s AND `Section` = %s AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0' ORDER BY `Title` ASC, `Price` ASC", $courseURL, $number, $section));
		
	//Check if any data was returned
		if (!count($books)) {
			wp_redirect($failRedirect);
			exit;
		}
		
		return $books;
	}
}
?>