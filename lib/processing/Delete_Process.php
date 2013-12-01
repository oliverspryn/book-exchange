<?php
/**
 * Delete Book Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the delete book
 *    form.
 *  - Validate all incoming data.
 *  - Delete the book.
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

require_once(dirname(dirname(__FILE__)) . "/APIs/IndexDen.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");
require_once(dirname(dirname(__FILE__)) . "/processing/Processor_Base.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/HttpException.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Delete_Process extends Processor_Base {
/**
 * Hold the ID of the sale to restore.
 *
 * @access private
 * @type   string
*/

	private $saleID;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the delete book
 *    form.
 *  - Validate all incoming data.
 *  - Delete the book.
 * 
 * @access public
 * @return void
 * @since  3.0
*/

	public function __construct() {
		parent::__construct();
	
	//Check to see if the user has submitted the form
		if ($this->userSubmittedForm()) {
			$this->validateAndRetain();
			$this->delete();
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
			isset($_POST['ID']) &&
			is_numeric($_POST['ID'])) {
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
		
	//Check to see if the user is logged in
		if (!$this->loggedIn) {
			throw new Validation_Failed("You are not logged in");
		}
		
	//Get the book data
		$data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_sale` WHERE `SaleID` = %d", $_POST['ID']));
		
	//Check to see if the book already exists
		if (!count($data)) {
			throw new Validation_Failed("This book does not exist");
		}
		
	//Check to see if the user actually owns this book
		$this->retainUserInfo();
		
		if ($data[0]->MerchantID != $this->user->ID) {
			throw new Validation_Failed("You do not own this book");
		}
	
		$this->saleID = $_POST['ID'];
	}
	
/**
 * Use the values validated and retained in memory by the 
 * validateAndRetain() method to delete an existing entry in the 
 * database. The book will also be deleted from IndexDen.
 * 
 * @access private
 * @return void
 * @since  3.0
 * @throws Indextank_Exception_HttpException [Bubbled up] Thrown in the event of an IndexDen communication error
*/
	
	private function delete() {
		global $wpdb;
		
		IndexDen::delete($this->saleID);
		
		$wpdb->delete("ffi_be_sale", array (
			"SaleID" => $this->saleID
		), array (
			"%d"
		));
	}
}
?>