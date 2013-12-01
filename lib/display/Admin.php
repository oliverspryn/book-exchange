<?php
/**
 * Book Exchange Administration Display class
 *
 * This class is responsible for fetching data for the main display of 
 * the Book Exchange Assistant administration in the Wordpress Administration
 * section of the site. Some of its duties includes:
 *  - Fetch all of the APIs which the plugin uses.
 *  - Fetch all of the book covers which require approval.
 *  - Fetch all of the plugin's settings.
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

class Admin {
/**
 * Fetch the API keys from the database.
 * 
 * @access public
 * @return object An object containing a set of values from the API table in the database
 * @since  3.0
 * @static 
*/

	public static function APIData() {
		global $wpdb;
		
		$data = $wpdb->get_results("SELECT * FROM `ffi_be_apis` WHERE `ID` = '1'");
		return $data[0];
	}
	
/**
 * Fetch all of the book covers which require approval.
 * 
 * @access public
 * @return object A list of book whose covers require approval
 * @since  3.0
 * @static 
*/
	
	public static function covers() {
		global $wpdb;
		
		return $wpdb->get_results("SELECT * FROM `ffi_be_books` WHERE `ImageState` = 'PENDING_APPROVAL' ORDER BY `BookID` ASC");
	}

/**
 * Fetch the settings from the database.
 * 
 * @access public
 * @return object An object containing the plugin's settings from the settings table in the database
 * @since  3.0
 * @static 
*/
	
	public static function settings() {
		global $wpdb;
		
		$data = $wpdb->get_results("SELECT * FROM `ffi_be_settings` WHERE `ID` = '1'");
		return $data[0];
	}
}
?>