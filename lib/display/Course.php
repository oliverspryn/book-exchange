<?php
/**
 * Courses Information class
 *
 * ----------------------------------------------------------------
 *
 * DIFFERENCES FROM THE "BOOK" CLASS TO ALLEVIATE CONFUSION:
 *
 * - Provides information about courses.
 * - Information about a whole set of books from a particular
 *   course is returned.
 * - Data about books are analyzed in batches by placing them into
 *   groups.
 * - Data about books is gathered by how they relate to a
 *   particular course or group.
 *
 * ----------------------------------------------------------------
 *
 * This class is used to fetch data from the MySQL database for 
 * all information regarding avaliable courses. Some of its
 * features include fetching:
 *  - a listing of all courses
 *  - a listing Arts and Letters courses
 *  - a listing Science, Engineering & Mathematics courses
 *  - all available information about a particular course
 *  - a listing of books available within a course section
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.display
 * @since     3.0
*/

namespace FFI\BE;

class Course {
/**
 * Obtain a listing of all available courses.
 * 
 * @access public
 * @return object<mixed> A listing of all available courses
 * @since  3.0
 * @static
*/

	public static function getCourses() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT * FROM `ffi_be_courses` ORDER BY `Name` ASC");
	}

/**
 * Obtain a listing of all Arts and Letters courses, along with the
 * number of available books in each course.
 * 
 * @access public
 * @return object<mixed> A listing of all Arts and Letters courses
 * @since  3.0
 * @static
*/
	
	public static function getAL() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.Type = 'Arts' ORDER BY ffi_be_courses.Name ASC");
	}

/**
 * Obtain a listing of all Science, Engineering & Mathematics courses,
 * along with the number of available books in each course.
 * 
 * @access public
 * @return object<mixed> A listing of all Science, Engineering & Mathematics courses
 * @since  3.0
 * @static
*/
	
	public static function getSEM() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.Type = 'Science' ORDER BY ffi_be_courses.Name ASC");
	}

/**
 * Obtain all available information about a particular course.
 * 
 * @access public
 * @param  string        $courseURL The URL of the course of interest
 * @return object<mixed>            Data regarding the name of the course, the type, code, and book tally
 * @since  3.0
 * @static
*/
	
	public static function getCourseInfo($courseURL) {
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.URL = %s ORDER BY ffi_be_courses.Name ASC", $courseURL));
	}

/**
 * Obtain a listing of books for a particular course section.
 * 
 * @access public
 * @param  string        $courseURL The URL of the course of interest
 * @param  int           $number    The course number of interest
 * @param  char          $section   The course section letter of interest
 * @return object<mixed>            A listing of books for a particular course section
 * @since  3.0
 * @static
*/
	
	public static function getBooksInCourseSection($courseURL, $number, $section) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_bookcourses` LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_bookcourses.Course = ffi_be_new_courses.CourseID LEFT JOIN `ffi_be_new_sale` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_new_sale.Merchant = users.ID WHERE `URL` = %s AND `Number` = %s AND `Section` = %s AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND ffi_be_new_sale.Sold = '0' ORDER BY `Title` ASC, `Price` ASC", $courseURL, $number, $section));
	}
}
?>
