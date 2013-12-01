<?php
/**
 * User Information class
 *
 * This class is designed to gather information about a particular 
 * user. Its capibilities include:
 *  - Generate a series of statistics based on a user's information.
 *  - Obtain a list of books the user has for sale.
 *  - Fetching the list of purchased books.
 *  - Fetching the list of sold books. 
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
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-includes/pluggable.php");

class User {
/**
 * Fetch an overview of the user's spending and selling history. The
 * returned data will include:
 *  - An incrementing total number of books the user purchased, in an
 *    array listed by [Date] => Total purchased up to that date.
 *  - The total number of books ever purchased by this user.
 *  - An incrementing total number of books the user sold, in an array
 *    listed by [Date] => Total sold up to that date.
 *  - The total number of books ever sold by this user.
 *  - An array of dates where either a purchase or sell took place.
 * 
 * @access public
 * @return array<mixed> An array containing various bits of statistical information
 * @since  3.0
 * @static 
*/
	
	public static function fetchMonetaryStats() {
		global $current_user;
		global $wpdb;
		
		$dates = array();
		$purchased = array();
		$sold = array();
		
		$purchaseTotal = 0;
		$soldTotal = 0;
		
		if (is_user_logged_in()) {
			get_currentuserinfo();
			
			$stats = $wpdb->get_results($wpdb->prepare("SELECT * FROM ((SELECT UNIX_TIMESTAMP(`Time`) AS `Timestamp`, DATE_FORMAT(`Time`, '%%m-%%d-%%Y') AS `Date`, SUM(`Price`) AS `Total`, 'Purchase' AS `Type` FROM `ffi_be_purchases` WHERE `BuyerID` = %d GROUP BY `Date` ORDER BY `Time` ASC) UNION (SELECT UNIX_TIMESTAMP(`Time`) AS `Timestamp`, DATE_FORMAT(`Time`, '%%m-%%d-%%Y') AS `Date`, SUM(`Price`) AS `Total`, 'Sold' AS `Type` FROM `ffi_be_purchases` WHERE `MerchantID` = %d GROUP BY `Date` ORDER BY `Time` ASC)) `q` ORDER BY `Timestamp` ASC", $current_user->ID, $current_user->ID));
			
			foreach($stats as $stat) {
				array_push($dates, "'" . $stat->Date . "'");
				
				if ($stat->Type == "Purchase") {
					$purchaseTotal += $stat->Total;
				} else {
					$soldTotal += $stat->Total;
				}
				
				$purchased[$stat->Date] = $purchaseTotal;
				$sold[$stat->Date] = $soldTotal;
			}
			
			return array (
				"Purchases"         => $purchased,
				"Purchase Total"    => $purchaseTotal,
				"Sold"              => $sold,
				"Sold Total"        => $soldTotal,
				"Transaction Dates" => $dates
			);
		}
		
		return false;
	}
	
/**
 * Fetch information regarding books the user currently has for
 * sale. In addition to fetching generic information about these
 * books, this function will also return the following:
 *  - Books for sale
 *  - Number of books for sale
 *  - Expired books
 *  - Number of expired books
 *  - Books expiring soon (within one week)
 *  - Number of books expiring soon
 *  - Total number of books which have not been sold or expired
 *  - Book which have been sold
 *  - Number of books which have been sold
 * 
 * @access public
 * @return array<mixed> An array containing various bits of information on books for sale
 * @since  3.0
 * @static 
*/
	
	public static function getBooksForSale() {
		global $current_user;
		global $wpdb;
		
		if (is_user_logged_in()) {
			get_currentuserinfo();
			
		//Create a formatter object
			$formatter = new \DateTime();
			$info = $wpdb->get_results("SELECT * FROM `ffi_be_settings`");
			$timezone = new \DateTimeZone($info[0]->TimeZone);
			
		//Get the time 1 week from now, to check for books which will soon expire
			$now = new \DateTime("now", $timezone);
			$nowTimestamp = $now->getTimestamp();
			$future = $now->modify("+1 week");
			$oneWeekTimestamp = $future->getTimestamp();
			
		//Store the IDs of books which deserve attention
			$expired = array();
			$sold = array();
			$soon = array();
			
		//Fetch the data from the database
			$books = $wpdb->get_results($wpdb->prepare("SELECT *, UNIX_TIMESTAMP(`Upload`) AS `Timestamp`, DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) AS `Expiring` FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID WHERE `MerchantID` = %d ORDER BY `Upload` DESC", $current_user->ID));
			
			foreach($books as $book) {
			//Sold...
				if ($book->Sold == 1) {
					array_push($sold, $book->SaleID);
					continue;
				}
				
				$formatter = \DateTime::createFromFormat("Y-m-d H:i:s", $book->Expiring, $timezone);
				
			//Expired...
				if ($formatter->getTimestamp() < $nowTimestamp) {
					array_push($expired, $book->SaleID);
					continue;
				}
				
			//Expiring soon...
				if ($formatter->getTimestamp() < $oneWeekTimestamp) {
					array_push($soon, $book->SaleID);
				}
			}
			
			return array (
				"Books"           => $books,
				"Books Total"     => count($books),
				"Expired"         => $expired,
				"Expired Total"   => count($expired),
				"Expiring"        => $soon,
				"Expiring Total"  => count($soon),
				"Published Total" => count($books) - count($expired) - count($sold),
				"Sold"            => $sold,
				"SoldTotal"       => count($sold)
			);
		}
	}

/**
 * Fetch all of the books the current user has purchased.
 * 
 * @access public
 * @return object An object containing a list of books the user has purchased
 * @since  3.0
 * @static 
*/

	public static function getPurchases() {
		global $current_user;
		global $wpdb;
		
		if (is_user_logged_in()) {
			get_currentuserinfo();
			
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_purchases` LEFT JOIN`ffi_be_books` ON ffi_be_purchases.BookID = ffi_be_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Merchant` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_purchases.MerchantID = users.ID WHERE `BuyerID` = %d ORDER BY `Time` ASC", $current_user->ID)); 
		}
		
		return false;
	}
	
/**
 * Fetch all of the books the current user has sold.
 * 
 * @access public
 * @return object An object containing a list of books the user has sold
 * @since  3.0
 * @static 
*/
	
	public static function getSold() {
		global $current_user;
		global $wpdb;
		
		if (is_user_logged_in()) {
			get_currentuserinfo();
			
			return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_purchases` LEFT JOIN`ffi_be_books` ON ffi_be_purchases.BookID = ffi_be_books.BookID LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Merchant` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_purchases.MerchantID = users.ID WHERE `MerchantID` = %d ORDER BY `Time` ASC", $current_user->ID)); 
		}
		
		return false;
	}
}
?>