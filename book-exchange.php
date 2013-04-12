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

	namespace FFI\BE;
	
//Create plugin-specific global definitions
	define("FFI\BE\FAKE_ADDR", get_site_url() . "/book-exchange/");
	define("FFI\BE\PATH", plugin_dir_path(__FILE__));
	define("FFI\BE\REAL_ADDR", get_site_url() . "/wp-content/plugins/book-exchange/");
	define("FFI\BE\URL_ACTIVATE", "book-exchange");
	
	define("FFI\BE\ACTIVE", true);
	define("FFI\BE\NAME", "Book Exchange");
	
//Instantiate the Interception_Manager
	if(!is_admin()) {
		require_once(PATH . "includes/Interception_Manager.php");
		$intercept = new Interception_Manager();
		$intercept->registerException("sell-books", "sell-books/index.php", 2);
		$intercept->go();
	}
?>