<?php
/**
 * Update Plugin Settings Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the plugin settings
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's settings.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Processor_Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.processing
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/processing/Processor_Base.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Settings_Process extends Processor_Base {
/**
 * Hold the automated email from address.
 *
 * @access private
 * @type   string
*/
	
	private $address;

/**
 * Hold the book expiration duration.
 *
 * @access private
 * @type   int
*/
	
	private $expire;
	
/**
 * Hold the automated email from name.
 *
 * @access private
 * @type   string
*/
	
	private $name;
	
/**
 * Hold the plugin's time zone.
 *
 * @access private
 * @type   string
*/
	
	private $timeZone;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the plugin settings
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's settings.
 * 
 * @access public
 * @return void
 * @since  3.0
*/
	
	public function __construct() {
		parent::__construct();
		$this->hasPrivileges();
		
	//Check to see if the user has submitted the form
		if ($this->userSubmittedForm()) {
			$this->validateAndRetain();
			$this->update();
		}
	}
	
/**
 * Ensure the user is logged in with administrative privileges.
 *
 * @access private
 * @return bool              Whether or not the user is logged in as the administrator
 * @throws Validation_Failed Thrown if the user does not have sufficent privileges to update the settings
 * @since  3.0
*/
	
	private function hasPrivileges() {	
		if ($this->isAdmin) {
			//Nice!
		} else {
			throw new Validation_Failed("You are not logged in with administrator privileges");
		}
	}
	
/**
 * Determine whether or not the user has submitted the form by
 * checking to see if all required data is present (but not
 * necessarily valid).
 *
 * @access private
 * @return bool     Whether or not the user has submitted the form
 * @since  3.0
*/
	
	private function userSubmittedForm() {
		if (is_array($_POST) && count($_POST) &&
			isset($_POST['expire']) && isset($_POST['email-name']) && isset($_POST['email-address']) && isset($_POST['timezone']) &&
			is_numeric($_POST['expire']) && !empty($_POST['email-name']) && !empty($_POST['email-address']) && !empty($_POST['timezone'])) {
			return true;
		}
		
		return false;
	}
	
/**
 * Determine whether or not all of the required information has been
 * submitted and is completely valid. If validation has succeeded, then
 * store the data within the class for later database entry.
 *
 * @access private
 * @return void
 * @since  3.0
 * @throws Validation_Failed Thrown when ANY portion of the validation process fails
*/

	private function validateAndRetain() {
	//Validate and retain the book's expiration in months
		if (!$this->intBetween($_POST['expire'], 1, 25)) {
			throw new Validation_Failed("The book's expiration month is out of range");
		}
	
		$this->expire = $_POST['expire'];
		
	//Retain the automated email from name
		$this->name = $_POST['email-name'];
		
	//Validate and retain the automated email from address
		if (!filter_var($_POST['email-address'], FILTER_VALIDATE_EMAIL)) {
			throw new Validation_Failed("The plugin's email address is invalid");
		}
		
		$this->address = $_POST['email-address'];
		
	//Validate and retain the plugin's time zone
		$zones = array (
			"America/New_York",
			"America/Chicago",
			"America/Denver",
			"Amercia/Los_Angeles",
			"America/Anchorage",
			"Pacific/Honolulu"
		);
		
		if (!in_array($_POST['timezone'], $zones)) {
			throw new Validation_Failed("The plugin's time zone is invalid");
		}
		
		$this->timeZone = $_POST['timezone'];
	}
	
/**
 * Update the plugin's settings.
 *
 * @access private
 * @return void
 * @since  3.0
*/

	private function update() {
		global $wpdb;
		
		$wpdb->update("ffi_be_settings", array (
			"BookExpireMonths" => $this->expire,
			"EmailName"        => $this->name,
			"EmailAddress"     => $this->address,
			"TimeZone"         => $this->timeZone
		), array (
			"SettingsID" => 1
		), array (
			"%d", "%s", "%s", "%s"
		), array (
			"%d"
		));
		
		wp_redirect(admin_url() . "admin.php?page=book-exchange/admin/settings.php&updated=1");
		exit;
	}
}
?>