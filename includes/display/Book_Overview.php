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
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_new_sale` WHERE DATE_ADD(`Upload`, INTERVAL 6 MONTH) < CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
}
?>