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

class Book_Overview {
	public static function getTotal() {
		global $wpdb;
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_new_sale` WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
	
	public static function getNumbersWithBooks($courseURL) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT `Number`, `Section`, COUNT(*) AS `SectionTotal` FROM (SELECT ffi_be_new_bookcourses.Number, ffi_be_new_bookcourses.Section FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_bookcourses.SaleID = ffi_be_new_sale.SaleID LEFT JOIN ffi_be_new_courses ON ffi_be_new_courses.CourseID = ffi_be_new_bookcourses.Course WHERE `Sold` = 0 AND DATE_ADD(ffi_be_new_sale.Upload, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_new_settings`) MONTH) > CURDATE()  AND ffi_be_new_courses.URL = %s GROUP BY ffi_be_new_sale.SaleID, ffi_be_new_bookcourses.Number ORDER BY `Number` ASC, `Section` ASC) AS `CourseBooks` GROUP BY `Number`, `Section`", $courseURL));
	}
}
?>