<?php
/*
Plugin Name: Book Exchange
Plugin URI: http://forwardfour.com/book-exchange
Description: This is a plugin for the Grove City College Student Government Association's student textbook exchange.
Version: 3.0
Author: Oliver Spryn
Author URI: http://forwardfour.com/
License: MIT
*/

	namespace FFI\BE;
	
//Create plugin-specific global definitions
	define("FFI\BE\CDN", false);
	define("FFI\BE\FAKE_ADDR", get_site_url() . "/book-exchange/");
	define("FFI\BE\PATH", plugin_dir_path(__FILE__));
	define("FFI\BE\REAL_ADDR", get_site_url() . "/wp-content/plugins/book-exchange/");
	define("FFI\BE\RESOURCE_PATH", (CDN ? "//ffistatic.appspot.com/sga" : site_url()) . "/wp-content/plugins/book-exchange/");
	define("FFI\BE\URL_ACTIVATE", "book-exchange");
	
	define("FFI\BE\ENABLED", true);
	define("FFI\BE\NAME", "Book Exchange");
	
//Instantiate the Interception_Manager
	if (!is_admin()) {
		require_once(PATH . "lib/Interception_Manager.php");
		$intercept = new Interception_Manager();
		$intercept->registerException("book", "book/index.php", 2);
		$intercept->registerException("browse", "browse/index.php", 2);
		$intercept->registerException("browse", "browse/index.php", 2, 3, 4);
		$intercept->registerException("sell-books", "sell-books/index.php", 2);
		$intercept->highlightNavLink(URL_ACTIVATE);
		$intercept->go();
	} else {
		function addMenuItems() {
   			global $submenu;
			
		//Add the desired pages to the Wordpress Administration menu
			add_menu_page("Approve Covers", "Book Exchange", "update_core", "book-exchange/admin/approve.php");
			add_submenu_page("book-exchange/admin/approve.php", "API Management", "API Management", "update_core", "book-exchange/admin/api.php");
			add_submenu_page("book-exchange/admin/approve.php", "Settings", "Settings", "update_core", "book-exchange/admin/settings.php");
			
		//Modify the name of the first sub-menu item
			$submenu['book-exchange/admin/approve.php'][0][0] = "Approve Covers";
		}
		
		function adminScripts() {
			wp_register_script("ffi-be-admin-approve-script", REAL_ADDR . "admin/scripts/FFI_BE_Approve.js", array("jquery"));
			wp_enqueue_script("ffi-be-admin-approve-script");
		
			wp_register_style("ffi-be-admin-styles", REAL_ADDR . "admin/styles/admin.css");
			wp_enqueue_style("ffi-be-admin-styles");
		}

		add_action("admin_enqueue_scripts", "FFI\\BE\\adminScripts");
		add_action("admin_menu", "FFI\\BE\\addMenuItems");
	}
?>
