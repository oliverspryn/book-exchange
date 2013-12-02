<?php
/**
 * Update Plugin APIs Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the plugin APIs
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's APIs.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Processor_Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.processing
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Processor_Base.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Login_Failed.php");

class API_Process extends Processor_Base {
/**
 * Hold the Cloudinary API key.
 *
 * @access private
 * @type   string
*/
	
	private $cloudinaryAPIKey;
	
/**
 * Hold the Cloudinary API secret.
 *
 * @access private
 * @type   string
*/
	
	private $cloudinaryAPISecret;
	
/**
 * Hold the Cloudinary name.
 *
 * @access private
 * @type   string
*/
	
	private $cloudinaryName;
	
/**
 * Hold the IndexDen index name.
 *
 * @access private
 * @type   string
*/
	
	private $indexDenName;
	
/**
 * Hold the IndexDen password.
 *
 * @access private
 * @type   string
*/
	
	private $indexDenPassword;
	
/**
 * Hold the IndexDen URL.
 *
 * @access private
 * @type   string
*/
	
	private $indexDenURL;

/**
 * Hold the IndexDen username.
 *
 * @access private
 * @type   string
*/
	
	private $indexDenUsernane;
	
/**
 * Hold the InvisibleHand App ID.
 *
 * @access private
 * @type   string
*/
	
	private $invisibleHandAppID;
	
/**
 * Hold the InvisibleHand App key.
 *
 * @access private
 * @type   string
*/
	
	private $invisibleHandAppKey;
	
/**
 * Hold the Mandrill API Key.
 *
 * @access private
 * @type   string
*/
	
	private $mandrill;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the plugin APIs
 *    form.
 *  - Validate all incoming data.
 *  - Upadate the plugin's APIs.
 * 
 * @access public
 * @return void
 * @since  3.0.0
*/
	
	public function __construct() {
		parent::__construct();
		$this->hasAdminPrivileges();
		
	//Check to see if the user has submitted the form
		if ($this->userSubmittedForm()) {
			$this->validateAndRetain();
			$this->update();
		}
	}
	
/**
 * Determine whether or not the user has submitted the form by
 * checking to see if all required data is present (but not
 * necessarily valid).
 *
 * @access private
 * @return bool     Whether or not the user has submitted the form
 * @since  3.0.0
*/
	
	private function userSubmittedForm() {
		if (is_array($_POST) && count($_POST) &&
			isset($_POST['cloudinary-cloud-name']) && isset($_POST['cloudinary-api-key']) && isset($_POST['cloudinary-api-secret']) && isset($_POST['indexden-url']) && isset($_POST['indexden-username']) && isset($_POST['indexden-password']) && isset($_POST['indexden-name']) && isset($_POST['invisiblehand-app']) && isset($_POST['invisiblehand-key']) && isset($_POST['mandrill']) &&
			!empty($_POST['cloudinary-cloud-name']) && !empty($_POST['cloudinary-api-key']) && !empty($_POST['cloudinary-api-secret']) && !empty($_POST['indexden-url']) && !empty($_POST['indexden-username']) && !empty($_POST['indexden-password']) && !empty($_POST['indexden-name']) && !empty($_POST['invisiblehand-app']) && !empty($_POST['invisiblehand-key']) && !empty($_POST['mandrill'])) {
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
 * @since  3.0.0
*/

	private function validateAndRetain() {
	//Retain the Cloudinary cloud name
		$this->cloudinaryName = $_POST['cloudinary-cloud-name'];
		
	//Retain the Cloudinary API key
		$this->cloudinaryAPIKey = $_POST['cloudinary-api-key'];
		
	//Retain the Cloudinary API secret
		$this->cloudinaryAPISecret = $_POST['cloudinary-api-secret'];
		
	//Retain the IndexDen API URL
		$this->indexDenURL = $_POST['indexden-url'];
		
	//Retain the IndexDen username
		$this->indexDenUsername = $_POST['indexden-username'];
		
	//Retain the IndexDen password
		$this->indexDenPassword = $_POST['indexden-password'];
		
	//Retain the IndexDen index name
		$this->indexDenName = $_POST['indexden-name'];
		
	//Retain the InvisbleHand App ID
		$this->invisibleHandAppID = $_POST['invisiblehand-app'];
		
	//Retain the InvisbleHand App key
		$this->invisibleHandAppKey = $_POST['invisiblehand-key'];
		
	//Retain the Mandrill API key	
		$this->mandrill = $_POST['mandrill'];
	}
	
/**
 * Update the plugin's APIs.
 *
 * @access private
 * @return void
 * @since  3.0.0
*/

	private function update() {
		global $wpdb;
		
		$wpdb->update("ffi_be_apis", array (
			"CloudinaryCloudName"  => $this->cloudinaryName,
			"CloudinaryAPIKey"     => $this->cloudinaryAPIKey,
			"CloudinaryAPISecret"  => $this->cloudinaryAPISecret,
			"IndexDenURL"          => $this->indexDenURL,
			"IndexDenIndex"        => $this->indexDenName,
			"IndexDenUsername"     => $this->indexDenUsername,
			"IndexDenPassword"     => $this->indexDenPassword,
			"InvisibleHandAppID"   => $this->invisibleHandAppID,
			"InvisibleHandAppKey"  => $this->invisibleHandAppKey,
			"MandrillKey"          => $this->mandrill
		), array (
			"ID" => 1
		), array (
			"%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s"
		), array (
			"%d"
		));
		
		wp_redirect(admin_url() . "admin.php?page=book-exchange/admin/api.php&updated=1");
		exit;
	}
}
?>