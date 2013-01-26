<?php
/*
Plugin Name: Book Exchange
Plugin URI: http://forwardfour.com/book-exchange
Description: This is a plugin for the Grove City College Student Government Association's student textbook exchange.
Version: 2.0
Author: Oliver Spryn
Author URI: http://forwardfour.com/
License: MIT
*/

//Create plugin-specific global definitions
	define("FFI_BE_FILE", __FILE__);
	define("FFI_BE_PATH", plugin_dir_path(__FILE__));

//Require the Book Exchange initialization class
	if (!is_admin()) {
		require_once(FFI_BE_PATH . "/includes/FFI_BE_Interception_Manager.php");
		new FFI_BE_Interception_Manager();
	}
?>