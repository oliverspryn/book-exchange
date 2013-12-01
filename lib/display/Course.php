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
 *  - Generate JavaScript to initialize Arbor JS on course overview 
 *    pages.
 *  - A listing Arts and Letters courses.
 *  - A listing of books available within a course section.
 *  - Obtain information about a particular course.
 *  - A listing of all courses.
 *  - Number of available books by course number and section.
 *  - A listing Science, Engineering & Mathematics courses.
 *  - All available information about a particular course.
 *  - Generate a list of recent, available books in a course.
 *  - Fetch the total number of available books in the system.
 *  - Fetch the total number of available books in a particular
 *    course.
 *  - Purify a string for use in a URL.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.display
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Course {
/**
 * Generate a minified version of the JavaScript required to 
 * initialize the Arbor JS interactive graph and all of its 
 * nodes for the course numbers and sections which have at 
 * least one available book.
 *
 * @access public
 * @param  string $courseName The name of the course for which the graph is being generated
 * @param  string $courseURL  The URL version of the course of interest
 * @return string             Minified JavaScript whcih is used to initialize Arbor JS
 * @since  3.0
 * @static
*/
	
	public static function generateArborJSInit($courseName, $courseURL) {
		global $essentials;
		
		$completedNum = array();
		$courseListing = Course::getNumbersWithBooks($courseURL);
		$JS = "\$(function(){var graph={nodes:{'" . $courseName . "':{alpha:1,color:'#0044CC',shape:'dot'}";

	//List the course numbers, without duplicates
		foreach($courseListing as $courseInfo) {
			if (!in_array($courseInfo->Number, $completedNum)) {
				$JS .= ",'" . $courseInfo->Number . "':{alpha:1,color:'#A7AF00',shape:'dot'}";
				array_push($completedNum, $courseInfo->Number);
			}
		}

	//List the course sections
		foreach($courseListing as $courseInfo) {
			$JS .= ",'" . $courseInfo->Number . " " . $courseInfo->Section . "':{alpha:0,color:'orange',link:'" . $essentials->friendlyURL("browse/" . $courseURL . "/" . $courseInfo->Number . "/" . $courseInfo->Section) . "',shape:'rect'}";
		}

		$JS .= "},edges:{'" . $courseName . "':{";

	//Connect the course numbers to the primary node
		foreach($courseListing as $courseInfo) {
			$JS .= "'" . $courseInfo->Number . "':{length:0.8},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "},";

	//Connect the course sections to the course numbers
		foreach($completedNum as $courseNum) {
			$JS .= "'" . $courseNum . "':{";

			foreach($courseListing as $courseInfo) {
				$JS .= $courseInfo->Number == $courseNum ? ("'" . $courseInfo->Number . " " . $courseInfo->Section . "':{},") : "";
			}

			$JS = rtrim($JS, ",");
			$JS .= "},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "}};var sys=arbor.ParticleSystem();sys.parameters({dt:0.015,gravity:true,repulsion:5000,stiffness:900});sys.renderer=Renderer('#explorer');sys.graft(graph);})";
		
		return $JS;
	}

/**
 * Obtain a listing of all Arts and Letters courses, along with the
 * number of available books in each course.
 * 
 * @access public
 * @return object A listing of all Arts and Letters courses
 * @since  3.0
 * @static
*/
	
	public static function getAL() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.Type = 'Arts' ORDER BY ffi_be_courses.Name ASC");
	}
	
/**
 * Obtain a listing of books for a particular course section.
 * 
 * @access public
 * @param  string $courseURL The URL of the course of interest
 * @param  int    $number    The course number of interest
 * @param  char   $section   The course section letter of interest
 * @return object            A listing of books for a particular course section
 * @since  3.0
 * @static
*/
	
	public static function getBooksInCourseSection($courseURL, $number, $section) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_courses` ON ffi_be_bookcourses.Course = ffi_be_courses.Code LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Name` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_sale.MerchantID = users.ID WHERE `URL` = %s AND `Number` = %s AND `Section` = %s AND DATE_ADD(ffi_be_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0' ORDER BY `Title` ASC, `Price` ASC", $courseURL, $number, $section));
	}
	
/**
 * Obtain all available information about a particular course.
 * 
 * @access public
 * @param  string $courseURL The URL of the course of interest
 * @return object            Data regarding the name of the course, the type, code, and book tally
 * @since  3.0
 * @static
*/
	
	public static function getCourseInfo($courseURL) {
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.URL = %s ORDER BY ffi_be_courses.Name ASC", $courseURL));
	}

/**
 * Obtain a listing of all available courses.
 * 
 * @access public
 * @return object A listing of all available courses
 * @since  3.0
 * @static
*/

	public static function getCourses() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT * FROM `ffi_be_courses` ORDER BY `Name` ASC");
	}
	
/**
 * Obtain a listing of course numbers and sections with the number
 * of available books in each section. This function does not return
 * the course name or code, as it assumes it is already known.
 * 
 * @access public
 * @param  string $courseURL The URL version of the course of interest
 * @return object                   A listing of all available course numbers and sections with books
 * @since  3.0
 * @static
*/
	
	public static function getNumbersWithBooks($courseURL) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT `Number`, `Section`, COUNT(*) AS `SectionTotal` FROM (SELECT ffi_be_bookcourses.Number, ffi_be_bookcourses.Section FROM `ffi_be_sale` LEFT JOIN `ffi_be_bookcourses` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID LEFT JOIN ffi_be_courses ON ffi_be_courses.Code = ffi_be_bookcourses.Course WHERE `Sold` = 0 AND DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_courses.URL = %s GROUP BY ffi_be_sale.SaleID, ffi_be_bookcourses.Number ORDER BY `Number` ASC, `Section` ASC) AS `CourseBooks` GROUP BY `Number`, `Section`", $courseURL));
	}

/**
 * Obtain a listing of all Science, Engineering & Mathematics courses,
 * along with the number of available books in each course.
 * 
 * @access public
 * @return object A listing of all Science, Engineering & Mathematics courses
 * @since  3.0
 * @static
*/
	
	public static function getSEM() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT ffi_be_courses.*, COALESCE(`Total`, 0) AS `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.Type = 'Science' ORDER BY ffi_be_courses.Name ASC");
	}
	
/**
 * This function will geneate an HTML list of recent books when
 * given a course ID. The number of available books can be limited,
 * and a specific book ID can be included int he last parameter
 * to exclude it from the list the book detail page the user is
 * viewing is not also suggested in this list.
 * 
 * @access public
 * @param  int    $courseID The course ID of interest
 * @param  int    $limit    The maximum number of books to list
 * @param  int    $exclude  The ID of a book to exclude from the list, should it appear in the list
 * @return string           An HTML list of recent books
 * @since  3.0
 * @static
*/

	public static function getRecentBooksInCourse($courseID, $limit = 4, $exclude = 0) {
		global $wpdb;
		global $essentials;
		
	//Set an ID of a book to ignore
		$return = "";
		$excludeSQL = "";
	
		if ($exclude) {
			$excludeSQL = " AND ffi_be_sale.SaleID != '" . esc_sql($exclude) . "'";
		}
		
	//Fetch the newest books
		$books = $wpdb->get_results($wpdb->prepare("SELECT ffi_be_sale.SaleID, ffi_be_books.Title, ffi_be_books.Author, ffi_be_sale.Price, ffi_be_books.ImageID, ffi_be_courses.Name AS CourseName, ffi_be_courses.URL AS CourseURL FROM ffi_be_sale LEFT JOIN ffi_be_books ON ffi_be_sale.BookID = ffi_be_books.BookID LEFT JOIN ffi_be_bookcourses ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID LEFT JOIN ffi_be_courses ON ffi_be_courses.Code = ffi_be_bookcourses.Course WHERE ffi_be_courses.CourseID = %d AND DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'" . $excludeSQL . " GROUP BY ffi_be_sale.SaleID ORDER BY ffi_be_sale.Upload DESC LIMIT %d", $courseID, $limit));
		
	//Write out the HTML for each of the newest books
		if (count($books)) {
			$return = "<ul class=\"new\">
";
		
			foreach($books as $book) {
				$return .= "<li>
<a href=\"" . $essentials->friendlyURL("book/" . $book->SaleID . "/" . self::URLPurify($book->Title)) . "\">
<img alt=\"" . htmlentities($book->Title) . " Cover\" src=\"" . Cloudinary::coverPreview($book->ImageID) . "\">
<h3>" . $book->Title . "</h3>
<h4>by " . $book->Author . "</h4>
<p class=\"price\">\$" . $book->Price . ".00</p>
</a>
</li>
";
			}
		
			$return .= "</ul>";
		}
		
		return $return;
	}
	
/**
 * Count the number of available books.
 *
 * @access public
 * @return int    The number of available books
 * @since  3.0
 * @static
*/

	public static function total() {
		global $wpdb;
		
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_sale` WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
	
/**
 * Count the number of available books in a particular course.
 *
 * @access public
 * @param  string $courseURL The URL-version of the course of interest
 * @return int               The number of available books in a course
 * @since  3.0
 * @static
*/

	public static function totalInCourse($courseURL) {
		global $wpdb;
		
		$total = $wpdb->get_results($wpdb->prepare("SELECT `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.URL = %s ORDER BY ffi_be_courses.Name ASC", $courseURL));
		
		return (int)$total[0]->Total;
	}

/**
 * This function will take a string and prepare it for use in a
 * URL by removing any spaces and special characters, and then 
 * making all characters lower case, which is this plugin's
 * convention when placing strings in a URL.
 * 
 * @access public
 * @param  string $name The name of a state
 * @return string       The URL purified version of the string
 * @since  3.0
 * @static
*/

	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s\-]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);            //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);                 //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>