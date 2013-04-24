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
		
		return $wpdb->get_results("SELECT ffi_be_new_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_new_courses` LEFT JOIN (SELECT ffi_be_new_courses.*, COUNT(*) AS `Total` FROM (SELECT ffi_be_new_bookcourses.Course AS `CourseID` FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID WHERE DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) < CURDATE() AND ffi_be_new_sale.Sold = '0' GROUP BY ffi_be_new_bookcourses.SaleID) `info` LEFT JOIN `ffi_be_new_courses` ON info.CourseID = ffi_be_new_courses.CourseID GROUP BY info.CourseID) AS `info` ON ffi_be_new_courses.CourseID = info.CourseID WHERE ffi_be_new_courses.Type = 'Arts' ORDER BY ffi_be_new_courses.Name ASC");
	}
	
	public static function getSEM() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_new_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_new_courses` LEFT JOIN (SELECT ffi_be_new_courses.*, COUNT(*) AS `Total` FROM (SELECT ffi_be_new_bookcourses.Course AS `CourseID` FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID WHERE DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) < CURDATE() AND ffi_be_new_sale.Sold = '0' GROUP BY ffi_be_new_bookcourses.SaleID) `info` LEFT JOIN `ffi_be_new_courses` ON info.CourseID = ffi_be_new_courses.CourseID GROUP BY info.CourseID) AS `info` ON ffi_be_new_courses.CourseID = info.CourseID WHERE ffi_be_new_courses.Type = 'Science' ORDER BY ffi_be_new_courses.Name ASC");
	}
	
	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);          //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);               //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>