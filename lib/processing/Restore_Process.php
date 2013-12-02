<?php
/**
 * Restore Book Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the restore book
 *    form.
 *  - Validate all incoming data.
 *  - Update the book's existing data.
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
require_once(dirname(dirname(__FILE__)) . "/APIs/IndexDen.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Restore_Process extends Processor_Base {
/**
 * Hold the author of the book.
 *
 * @access private
 * @type   string
*/

	private $author;
	
/**
 * Hold the ID of the sale to restore.
 *
 * @access private
 * @type   string
*/

	private $saleID;
	
/**
 * Hold the new date and time for the book.
 *
 * @access private
 * @type   string
*/

	private $time;
	
/**
 * Hold the title of the book.
 *
 * @access private
 * @type   string
*/

	private $title;
	
/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the restore book
 *    form.
 *  - Validate all incoming data.
 *  - Update the book's existing data.
 * 
 * @access public
 * @return void
 * @since  3.0.0
*/

	public function __construct() {
		parent::__construct();
	
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
 * @return bool         Whether or not the user has submitted the form
 * @since  3.0.0
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
 * @since  3.0.0
 * @throws Validation_Failed Thrown when ANY portion of the validation process fails
*/
	
	private function validateAndRetain() {
		global $current_user, $wpdb;
		
	//Check to see if the user is logged in
		if (!$this->loggedIn) {
			throw new Validation_Failed("You are not logged in");
		}
		
	//Get the book data
		$data = $wpdb->get_results($wpdb->prepare("SELECT *, DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) AS `Expiring` FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID WHERE `SaleID` = %d", $_POST['ID']));
		
	//Check to see if the book already exists
		if (!count($data)) {
			throw new Validation_Failed("This book does not exist");
		}
		
	//Check to see if the book was already sold
		if ($data[0]->Sold == 1) {
			throw new Validation_Failed("You cannot restore a book which has been sold");
		}
		
	//Check to see if the user actually owns this book
		$this->retainUserInfo();
		
		if ($data[0]->MerchantID != $this->user->ID) {
			throw new Validation_Failed("You do not own this book");
		}
		
	//Has this book expired already or will it within the next week?
		$info = $wpdb->get_results("SELECT * FROM `ffi_be_settings`");
		$timezone = new \DateTimeZone($info[0]->TimeZone);
		
		$expiring = \DateTime::createFromFormat("Y-m-d H:i:s", $data[0]->Expiring, $timezone);
		$now = new \DateTime("now", $timezone);
		$this->time = $now->format("Y-m-d H:i:s");
		$oneWeek = $now->modify("+1 week");
		
		if ($expiring->getTimestamp() > $oneWeek->getTimestamp()) {
			throw new Validation_Failed("There is plenty of time before this book expires");
		}
	
		$this->saleID = $_POST['ID'];
		$this->title = $data[0]->Title;
		$this->author = $data[0]->Author;
	}
	
/**
 * Use the values validated and retained in memory by the 
 * validateAndRetain() method to update an existing entry in the 
 * database.
 * 
 * @access private
 * @return void
 * @since  3.0.0
 * @throws Indextank_Exception_HttpException [Bubbled up] Thrown in the event of an IndexDen communication error
*/
	
	private function update() {
		global $wpdb;
		
		IndexDen::updateByID($this->saleID, $this->title, $this->author);
		
		$wpdb->update("ffi_be_sale", array (
			"Upload" => $this->time
		), array (
			"SaleID" => $this->saleID
		), array (
			"%s"
		), array (
			"%d"
		));
	}
}
?>