<?php
/**
 * Sell Book Processing class
 *
 * This class is used to:
 *  - Determine whether or not a user has sumbitted the sell book
 *    form.
 *  - Validate all incoming data.
 *  - Either insert the data into a database or update existing data.
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
require_once(dirname(dirname(__FILE__)) . "/display/Book.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Validation_Failed.php");
require_once(dirname(dirname(__FILE__)) . "/processing/Processor_Base.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Indextank/Exception/HttpException.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Isbn.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-includes/link-template.php");

class Sell_Book_Process extends Processor_Base {
/**
 * Hold the author of the book.
 *
 * @access private
 * @type   string
*/
	
	private $author;
	
/**
 * Hold the user's comments.
 *
 * @access private
 * @type   string
*/
	
	private $comments;
	
/**
 * Hold the condition of the book.
 *
 * @access private
 * @type   int
*/
	
	private $condition;
	
/**
 * Hold the list of course names for which this book was
 * used.
 *
 * @access private
 * @type   array<string>
*/
	
	private $courses;

/**
 * Hold the cover image of the book.
 *
 * @access private
 * @type   string
*/
	
	private $cover;
	
/**
 * Hold the edition of the book.
 *
 * @access private
 * @type   string
*/
	
	private $edition;

/**
 * Hold the ISBN 10 of the book.
 *
 * @access private
 * @type   string
*/
	
	private $ISBN10;

/**
 * Hold the ISBN 13 of the book.
 *
 * @access private
 * @type   string
*/
	
	private $ISBN13;
	
/**
 * Hold the ID of the merchant.
 *
 * @access private
 * @type   int
*/
	
	private $merchant;

/**
 * Hold the current date and time, formatted for MySQL.
 *
 * @access private
 * @type   string
*/

	private $now;
	
/**
 * Hold the list of course numbers for which this book was
 * used.
 *
 * @access private
 * @type   array<int>
*/
	
	private $numbers; //Next is Deuteronomy :D
	
/**
 * Hold the price of the book.
 *
 * @access private
 * @type   int
*/
	
	private $price;

/**
 * Hold the list of course sections for which this book was
 * used.
 *
 * @access private
 * @type   array<char>
*/
	
	private $sections;
	
/**
 * Hold the title of the book.
 *
 * @access private
 * @type   string
*/
	
	private $title;

/**
 * Hold whether the book has any markings.
 *
 * @access private
 * @type   int
*/
	
	private $written;

/**
 * CONSTRUCTOR
 *
 * This method will call helper methods to:
 *  - Determine whether or not a user has sumbitted the sell books
 *    form.
 *  - Validate all incoming data.
 *  - Either insert the data into a database or update existing data.
 * 
 * @access public
 * @param  int                               $ID The sale ID of the book to update. $ID = 0 means there is no entry to update (i.e. insert a book).
 * @return void
 * @since  3.0
 * @throws Indextank_Exception_HttpException     [Bubbled up] Thrown in the event of an IndexDen communication error
*/

	public function __construct($ID) {
		parent::__construct();
	
	//Check to see if the user has submitted the form
		if ($this->userSubmittedForm($ID)) {
			$this->validateAndRetain($ID);
			
			if (intval($ID)) {
				$this->update($ID);
			} else {
				$this->insert();
			}
		}
	}

/**
 * Determine whether or not the user has submitted the form by
 * checking to see if all required data is present (but not 
 * necessarily valid).
 * 
 * @access private
 * @param  int      $ID The sale ID of the book to update
 * @return bool         Whether or not the user has submitted the form
 * @since  3.0
*/
	
	private function userSubmittedForm($ID) {
		if (is_array($_POST) && count($_POST) && 
			((!intval($ID) && isset($_POST['ISBN10']) && isset($_POST['ISBN13'])) || (intval($ID) && !isset($_POST['ISBN10']) && !isset($_POST['ISBN13']))) && isset($_POST['title'])  && isset($_POST['author']) && isset($_POST['imageURL']) && isset($_POST['course']) && isset($_POST['number']) && isset($_POST['section']) && isset($_POST['price']) && isset($_POST['condition']) && isset($_POST['written']) &&
			((!intval($ID) && !empty($_POST['ISBN10']) && !empty($_POST['ISBN13'])) || intval($ID)) && !empty($_POST['title']) && !empty($_POST['author']) && !empty($_POST['imageURL']) && is_array($_POST['course']) && count($_POST['course']) && is_array($_POST['number']) && count($_POST['number']) && is_array($_POST['section']) && count($_POST['section']) && is_numeric($_POST['price']) && is_numeric($_POST['condition']) && is_numeric($_POST['written'])) {
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
	
	private function validateAndRetain($ID) {
		global $wpdb;

	//Retain the merchant ID, an earlier script will already have ensured the user is logged in
		$this->retainUserInfo();
		$this->merchant = $this->user->ID;

	//Retain the current time
		$info = $wpdb->get_results("SELECT * FROM `ffi_be_settings`");
		$timezone = new \DateTimeZone($info[0]->TimeZone);
		$dateFormatter = new \DateTime("now", $timezone);
		$this->now = $dateFormatter->format("Y-m-d H:i:s");

	//Validate and retain the ISBN10
		if (!intval($ID)) {
			if (\Isbn::validate10($_POST['ISBN10'])) {
				$this->ISBN10 = \Isbn::clean($_POST['ISBN10']);
			} else {
				throw new Validation_Failed("The ISBN10 is invalid");
			}
		}

	//Validate and retain the ISBN13
		if (!intval($ID)) {
			if (\Isbn::validate13($_POST['ISBN13'])) {
				$this->ISBN13 = \Isbn::clean($_POST['ISBN13']);
			} else {
				throw new Validation_Failed("The ISBN13 is invalid");
			}
		
			if (\Isbn::to13($_POST['ISBN10']) != \Isbn::clean($_POST['ISBN13'])) {
				throw new Validation_Failed("The ISBN10 and ISBN13 values are incompatible");
			}
		} else {
			$ISBN = $wpdb->get_col($wpdb->prepare("SELECT `ISBN13` FROM ffi_be_books LEFT JOIN `ffi_be_sale` ON ffi_be_books.BookID = ffi_be_sale.BookID WHERE SaleID = %d", $ID));
			$this->ISBN13 = $ISBN[0];
		}

	//Retain the title
		$this->title = $_POST['title'];

	//Retain the author
		$this->author = $_POST['author'];

	//Retain the edition
		$this->edition = $_POST['edition'];
		
	//Retain the book cover
		$this->cover = $_POST['imageURL'];

	//Validate an retain the course name, number, and section
		$courses = $wpdb->get_col("SELECT `Code` FROM `ffi_be_courses`");
		$sections = range("A", "Z");

		if (count($_POST['course']) == count($_POST['number']) && count($_POST['course']) == count($_POST['section'])) {
			for ($i = 0; $i < count($_POST['course']); ++$i) {
				if (!in_array($_POST['course'][$i], $courses)) {
					throw new Validation_Failed("The submitted course does not exist");
				}

				if (!$this->intBetween($_POST['number'][$i], 101, 499)) {
					throw new Validation_Failed("A submitted course number does not exist");
				}

				if (!in_array($_POST['section'][$i], $sections)) {
					throw new Validation_Failed("A submitted course section does not exist");
				}
			}

			$this->courses = $_POST['course'];
			$this->numbers = $_POST['number'];
			$this->sections = $_POST['section'];
		} else {
			throw new Validation_Failed("The number of submitted courses, course numbers, and course sections do not match");
		}

	//Validate and retain the price
		if ($this->intBetween($_POST['price'], 0, 999)) {
			$this->price = $_POST['price'];
		} else {
			throw new Validation_Failed("The book's price is invalid");
		}

	//Validate and retain the book's condition
		if ($this->intBetween($_POST['condition'], 1, 5)) {
			$this->condition = $_POST['condition'];
		} else {
			throw new Validation_Failed("The book's condition is invalid");
		}

	//Validate and retain whether the book has any writing or markings
		if ($this->intBetween($_POST['written'], 0, 1)) {
			$this->written = $_POST['written'];
		} else {
			throw new Validation_Failed("The book's writing or marking information is invalid");
		}

	//Retain the user's comments
		$this->comments = $_POST['comments'];
	}
	
/**
 * Use the values validated and retained in memory by the 
 * validateAndRetain() method to insert a new entry into the database.
 * Also, update the IndexDen index to reflect all of the changes which
 * were made to the local database.
 * 
 * @access private
 * @return void
 * @since  3.0
 * @throws Indextank_Exception_HttpException [Bubbled up] Thrown in the event of an IndexDen communication error
*/
	
	private function insert() {
		global $wpdb;
		
	//Add the book information to the database, if needed
		$bookID = 0;
		$existing = false;
	
		try {
			$book = Book::getBookByISBN($this->ISBN13, false);
			$bookID = $book['BookID'];
			$existing = true;

		//Update the book's title, author, or edition in case the existing entry could be improved by the user's change
			$wpdb->update("ffi_be_books", array (
				"Title"   => $this->title,
				"Author"  => $this->author,
				"Edition" => $this->edition
			), array (
				"ISBN13" => $this->ISBN13
			), array (
				"%s", "%s", "%s"
			), array (
				"%s"
			));
		} catch (No_Data_Returned $e) {
			$wpdb->insert("ffi_be_books", array (
				"BookID"     => NULL,
				"ISBN10"     => $this->ISBN10,
				"ISBN13"     => $this->ISBN13,
				"Title"      => $this->title,
				"Author"     => $this->author,
				"Edition"    => $this->edition,
				"ImageID"    => $this->cover,
				"ImageState" => "PENDING_APPROVAL"
			), array (
				"%d", "%s", "%s", "%s", "%s", "%s", "%s", "%s"
			));
			
			$bookID = $wpdb->insert_id;
		}
		
	//Insert the sale information
		$wpdb->insert("ffi_be_sale", array (
			"SaleID"     => NULL,
			"BookID"     => $bookID,
			"MerchantID" => $this->merchant,
			"Upload"     => $this->now,
			"Sold"       => "0",
			"Price"      => $this->price,
			"Condition"  => $this->condition,
			"Written"    => $this->written,
			"Comments"   => $this->comments
		), array (
			"%s", "%d", "%d", "%s", "%d", "%d", "%d", "%d", "%s"
		));

		$saleID = $wpdb->insert_id;

	//Insert the listing of related courses
		for ($i = 0; $i < count($this->courses); ++$i) {
			$wpdb->insert("ffi_be_bookcourses", array (
				"SaleID"  => $saleID,
				"Course"  => $this->courses[$i],
				"Number"  => $this->numbers[$i],
				"Section" => $this->sections[$i]
			), array (
				"%d", "%s", "%d", "%s"
			));
		}

	//Share the book data with IndexDen
		if ($existing) {
			IndexDen::updateByISBN($this->ISBN13, $this->title, $this->author);
		} else {
			IndexDen::add($saleID, $this->title, $this->author);
		}

	//Redirect to the book
		wp_redirect(get_site_url() . "/book-exchange/book/" . $saleID . "/" . $this->URLPurify($this->title));
		exit;
	}
	
/**
 * Use the values validated and retained in memory by the 
 * validateAndRetain() method to update an existing entry in the 
 * database. Also, update the IndexDen index to reflect all of the
 * changes which were made to the local database.
 * 
 * @access private
 * @param  int                               $ID The sale ID of the book to update
 * @return void
 * @since  3.0
 * @throws Indextank_Exception_HttpException     [Bubbled up] Thrown in the event of an IndexDen communication error
*/
	
	private function update($ID) {
		global $wpdb;
		
	//Update the book's title, author, or edition in case the existing entry could be improved by the user's change
		$wpdb->update("ffi_be_books", array (
			"Title"   => $this->title,
			"Author"  => $this->author,
			"Edition" => $this->edition
		), array (
			"ISBN13" => $this->ISBN13
		), array (
			"%s", "%s", "%s"
		), array (
			"%s"
		));

	//Delete the old listing of related courses associated with this book
		$wpdb->delete("ffi_be_bookcourses", array (
			"SaleID" => $ID
		), array (
			"%d"
		));

	//Insert an updated listing of related courses
		for ($i = 0; $i < count($this->courses); ++$i) {
			$wpdb->insert("ffi_be_bookcourses", array (
				"SaleID"  => $ID,
				"Course"  => $this->courses[$i],
				"Number"  => $this->numbers[$i],
				"Section" => $this->sections[$i]
			), array (
				"%d", "%s", "%d", "%s"
			));
		}

	//Update the sale information
		$wpdb->update("ffi_be_sale", array (
			"Upload"    => $this->now,
			"Price"     => $this->price,
			"Condition" => $this->condition,
			"Written"   => $this->written,
			"Comments"  => $this->comments
		), array (
			"SaleID" => $ID
		), array(
			"%s", "%d", "%d", "%s"
		));

	//Share the updated book data with IndexDen
		IndexDen::updateByISBN($this->ISBN13, $this->title, $this->author);

	//Redirect to the book
		wp_redirect(get_site_url() . "/book-exchange/book/" . $ID . "/" . $this->URLPurify($this->title));
		exit;
	}
}
?>