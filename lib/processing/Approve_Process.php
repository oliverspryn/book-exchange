<?php
/**
 * Book Cover Approval Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the book cover
 *    approval form.
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

require_once(dirname(dirname(__FILE__)) . "/APIs/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Login_Failed.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");
require_once(dirname(dirname(__FILE__)) . "/processing/Processor_Base.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Approve_Process extends Processor_Base {
/**
 * Hold the ID of the book to approve.
 *
 * @access private
 * @type   int
*/
	
	private $ID;
	
/**
 * Hold the URL of the book cover image which is pending approval.
 *
 * @access private
 * @type   int
*/
	
	private $imageURL;

/**
 * Hold the approval state of the book.
 *
 * @access private
 * @type   string
*/
	
	private $state;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the book cover
 *    approval form.
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
 * @return void
 * @since  3.0
 * @throws Login_Failed Thrown if the user does not have sufficent privileges to update the settings
*/
	
	private function hasPrivileges() {	
		if ($this->isAdmin) {
			//Nice!
		} else {
			throw new Login_Failed("You are not logged in with administrator privileges");
		}
	}
	
/**
 * Determine whether or not the user has submitted the form by
 * checking to see if all required data is present (but not
 * necessarily valid).
 *
 * @access private
 * @return bool    Whether or not the user has submitted the form
 * @since  3.0
*/
	
	private function userSubmittedForm() {
		if (is_array($_POST) && count($_POST) &&
			isset($_POST['ID']) && isset($_POST['state']) &&
			is_numeric($_POST['ID']) && !empty($_POST['state'])) {
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
		global $wpdb;
	
	//Validate and retain the book ID and image URL
		$this->ID = $_POST['ID'];
		$books = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_books` WHERE `BookID` = %d AND `ImageState` = 'PENDING_APPROVAL'", $this->ID));
	
		if (!count($books)) {
			throw new Validation_Failed("This book does not exist");
		}
		
		$this->imageURL = $books[0]->ImageID;
	
	//Validate and retain the book state
		$stateTranslator = array (
			"approved"      => "APPROVED",
			"inappropriate" => "INAPPROPRIATE",
			"unavailable"   => "UNAVAILABLE"
		);
		
		$this->state = $_POST['state'];
		
		if (!array_key_exists($this->state, $stateTranslator)) {
			throw new Validation_Failed("The book cover cannot exist in this invalid state");
		}
		
		$this->state = $stateTranslator[$this->state];
		
	//Was alternative URL supplied?
		if ($this->state == "APPROVED" && isset($_POST['URL']) && !empty($_POST['URL'])) {
			$this->imageURL = $_POST['URL'];
		}
	}
	
/**
 * Update the book's cover state and upload the new cover off
 * to Cloudinary, if it was approved.
 *
 * @access private
 * @return void
 * @since  3.0
 * @throws Exception                [Bubbled up] Thrown when there is an error communicating with or uploading to Cloudinary
 * @throws InvalidArgumentException [Bubbled up] Thrown when the uploader script is not supplied with the necessary information
*/

	private function update() {
		global $wpdb;
		
	//Upload the cover to Cloudinary
		if ($this->state == "APPROVED") {
			$imageData = Cloudinary::upload($this->imageURL);
			$imageID = $imageData['public_id'] . "." . $imageData['format'];
		} else {
			$imageID = "NULL_ID_" . substr(md5(rand()), 0, 12);
		}
		
	//Log the results in the database
		$wpdb->update("ffi_be_books", array (
			"ImageID"    => $imageID,
			"ImageState" => $this->state
		), array (
			"BookID" => $this->ID
		), array (
			"%s", "%s"
		), array (
			"%d"
		));
	}
}
?>