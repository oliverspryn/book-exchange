<?php
/**
 * Book Exchange Administration Display class
 *
 * This class is responsible for fetching data for the main display of 
 * the Book Exchange administration in the Wordpress Administration
 * section of the site. Some of its duties includes:
 *  - fetch all of the APIs which the plug uses
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

class Admin {
	public static function APIData() {
		global $wpdb;
		
		$data = $wpdb->get_results("SELECT * FROM `ffi_be_new_apis` WHERE `ID` = '1'");
		return $data[0];
	}
	
	public static function Settings() {
		global $wpdb;
		
		$data = $wpdb->get_results("SELECT * FROM `ffi_be_new_settings` WHERE `ID` = '1'");
		return $data[0];
	}
}
?>
