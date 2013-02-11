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
	define("FFI_BE_REAL_ADDR", get_site_url() . "/wp-content/plugins/book-exchange/");
	define("FFI_BE_FAKE_ADDR", get_site_url() . "/book-exchange/");
	
//Add a new user role to the system
	add_role("book_exchange_user", "Book Exchange User", array(
		"read" => true
	));

//Require the Book Exchange Initialization and Essentials classes, if we are not in the administration interface
	if (!is_admin()) {
	//Plugin essentials
		require_once(FFI_BE_PATH . "/includes/FFI_BE_Essentials.php");
		$essentials = new FFI_BE_Essentials();
		
	//Initialization
		require_once(FFI_BE_PATH . "/includes/FFI_BE_Interception_Manager.php");
		new FFI_BE_Interception_Manager();
	}
?>