<?php
/**
 * Processor Base class
 *
 * This is an abstract class which is designed to provide a basic
 * set of methods which is commonly useful when building a processor
 * class. Its abilities include:
 *  - Checking and storing the user's login status.
 *  - Checking if the user is logged in with Administrator privileges.
 *  - Fetching and storing the plugin's settings from the database.
 *  - Validating if the value of an integer lies between two values.
 *  - Log the user into his or her account. This procedure will
 *    automatically store the user's account information.
 *  - Retain the user's identifying information.
 *  - Purify a string for use in a URL.
 *
 * @abstract
 * @author     Oliver Spryn
 * @copyright  Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license    MIT
 * @namespace  FFI\BE
 * @package    lib.processing
 * @since      1.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/exceptions/Login_Failed.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-includes/pluggable.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-includes/user.php");

abstract class Processor_Base {
/**
 * Hold the administrative status.
 *
 * @access protected
 * @type   bool
*/
	
	protected $isAdmin = false;

/**
 * Hold the user's login status.
 *
 * @access protected
 * @type   bool
*/
	
	protected $loggedIn = false;
	
/**
 * Hold the plugin settings fetched from a database.
 *
 * @access protected
 * @type   object
*/

	protected $settings;
	
/**
 * Hold the data about the user.
 *
 * @access protected
 * @type   WP_User
*/
	
	protected $user;
	
/**
 * CONSTRUCTOR
 *
 * Determine whether or not the user is logged in and store the result.
 * 
 * @access protected
 * @return void
 * @since  1.0
*/
	
	protected function __construct() {
		$this->loggedIn = is_user_logged_in();
		$this->isAdmin = $this->logggedIn && current_user_can("update_core");
	}
	
/**
 * Fetch the plugin settings from the database and make the data
 * available to the rest of the class.
 *
 * @access protected
 * @param  string    $tableName The name of the settings table in the database
 * @return void
 * @since  1.0
*/

	protected function fetchSettings($tableName = "settings") {
		global $wpdb;

		$this->settings = $wpdb->get_results("SELECT * FROM `" . $tableName . "`");
	}
	
/**
 * Check to see if a particular integer value falls between a specified
 * range, including the extrema values.
 * 
 * @access protected
 * @param  int      $value The integer value to check
 * @param  int      $min   The minimum value the integer may equal
 * @param  int      $max   The maximum value the integer may equal
 * @return bool            Whether or not the integer falls within the specified range
 * @since  1.0
*/
	
	protected function intBetween($value, $min, $max) {
		if (!is_numeric($value)) {
			return false;
		}
		
		$value = intval($value);
		
	//Check the integer extrema
		return ($value >= $min && $value <= $max);
	}
	
/**
 * Log in the user. The user's account data will be automatically
 * retained, even if they were previously logged in.
 *
 * If this function is called, it will expect the credentials either 
 * from two $_POST arguments, like this:
 *
 *     - Username value: $_POST['username']
 *     - Password value: $_POST['password']
 *
 * ... or, the username and password can be passed into this method
 * as parameters, like:
 *  
 *     $this->login($username, $password)
 *
 * This function will log the user as if they had NOT checked the 
 * "Remember Me" checkbox.
 *
 * @access protected
 * @param  string    $username The user's username
 * @param  string    $password The user's plain text password
 * @return void
 * @since  1.0
 * @throws Login_Failed        Thrown if a user's login credentials are invalid
*/
	
	protected function login() {
		if (!$this->loggedIn) {
			$args = func_get_args();
			
		//Was the username and password passed as arguments, or should we expect them in $_POST?
			if (count($args) == 2) {
				$credentials = array (
					"remember"      => false,
					"user_login"    => func_get_arg(0),
					"user_password" => func_get_arg(1)
				);
			} else {
				$credentials = array (
					"remember"      => false,
					"user_login"    => $_POST['username'],
					"user_password" => $_POST['password'],
				);
			}
			
		//Log the user in and retain the account information
			$this->user = wp_signon($credentials, false);
			
			if (is_wp_error($this->user)) {
				throw new Login_Failed("Your username or password is invalid");
			}
		} else {
			$this->user = wp_get_current_user();
		}
	}
	
/**
 * Retain the user's identifying information.
 * 
 * @access protected
 * @return void
 * @since  1.0
 * @static
 * @throws Login_Failed        Thrown if a user's login credentials are invalid
*/

	protected function retainUserInfo() {
		if (!$this->loggedIn) {
			throw new Login_Failed("Your username or password is invalid");
		} else {
			$this->user = wp_get_current_user();
		}
	}
	
/**
 * This function will take a string and prepare it for use in a
 * URL by removing any spaces and special characters, and then 
 * making all characters lower case, which is this plugin's
 * convention when placing strings in a URL.
 * 
 * @access protected
 * @param  string $name The name of a state
 * @return string       The URL purified version of the string
 * @since  1.0
 * @static
*/

	protected function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s\-]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);            //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);                 //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>