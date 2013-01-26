<?php
/**
 * Book Exchange page essentials class
 *
 * This class is used at the top of every Book Exchange script.
 * Its abilities are central to the structing and security of
 * each page. Its abilities include:
 *  - User access control
 *  - Importing necessary PHP scripts
 *  - Setting the page title
 *  - Including CSS or JS files
 * 
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @package   includes
 * @since     v2.0 Dev
*/

class FFI_BE_Essentials {
/**
 * CONSTRUCTOR
 *
 * There is nothing to do here... just live!
 * 
 * @access public
 * @return void
 * @since  v2.0 Dev
*/

	public function __construct() {
		//Nothing to do!
	}
	
/**
 * Check if the user is logged in. If so, then grant access to 
 * this page, otherwise, redirect to the login page.
 *
 * All users will have access to the features of the Book Exchange,
 * so there is no reason to check for certain privileges.
 * 
 * @access public
 * @return void
 * @since  v2.0 Dev
*/

	public function requireLogin() {
		if (!is_user_logged_in()) {
			wp_redirect(get_site_url() . "/wp-login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI']));
		}
	}
}
?>