<?php
/**
 * Sell books form display class
 *
 * This class is used to fetch data from the MySQL database for the 
 * sell books display form. If data is returned from the database, 
 * then the respective values are filled into their proper locations 
 * in the form, then returned for display in the HTML form.
 * 
 * If no values are returned, then form items with empty or default
 * values are constructed.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.form.display
 * @since     v1.0 Dev
*/

namespace FFI\BE;

class Sell_Book_Display {	
/**
 * Hold the results of the SQL query.
 *
 * @access private
 * @type   boolean|object<mixed>
*/
	
	private $data;

/**
 * CONSTRUCTOR
 *
 * Grab the data from the database, see if any data was returned, 
 * and if not, redirect to the URL indicated by $failRedirect.
 * 
 * @access public
 * @param  int      $ID           The ID of the book to fetch from the database
 * @param  int      $userID       The ID of the user requesting this page
 * @param  string   $failRedirect The URL to redirect to if the SQL query returns zero tuples (i.e. an invalid $ID or $userID is given)
 * @return void
 * @since  v1.0 Dev
*/
	
	public function __construct($ID, $userID, $failRedirect) {
		global $wpdb;
		
		if ($ID) {
			$this->data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_new_sale` LEFT JOIN `ffi_be_new_books` ON ffi_be_new_sale.BookID = ffi_be_new_books.BookID LEFT JOIN `ffi_be_new_bookcourses` ON ffi_be_new_sale.SaleID = ffi_be_new_bookcourses.SaleID LEFT JOIN `ffi_be_new_courses` ON ffi_be_new_bookcourses.Course = ffi_be_new_courses.CourseID WHERE ffi_be_new_sale.SaleID = %d AND ffi_be_new_sale.Merchant = %d", $ID, $userID));
			
		//SQL returned 0 tuples, "Leave me!" - http://johnnoble.net/img/photos/denethor_a.jpg
			if (!count($this->data)) {
				wp_redirect($failRedirect);
				exit;
			}
		} else {
			$this->data = false;
		}
	}
	
/**
 * Output a prefilled form element containing the "ISBN10" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getISBN10() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"ISBN10\" name=\"ISBN10\" type=\"text\" value=\"" . htmlentities($this->data ? $this->data[0]->ISBN10 : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "ISBN13" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getISBN13() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"ISBN13\" name=\"ISBN13\" type=\"text\" value=\"" . htmlentities($this->data ? $this->data[0]->ISBN13 : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Title" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getTitle() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"title\" name=\"title\" type=\"text\" value=\"" . htmlentities($this->data ? $this->data[0]->Title : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Author(s)" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getAuthors() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"author\" name=\"author\" type=\"text\" value=\"" . htmlentities($this->data ? $this->data[0]->Author : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Edition" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getEdition() {
		return "<input autocomplete=\"off\" id=\"edition\" name=\"edition\" type=\"text\" value=\"" . htmlentities($this->data ? $this->data[0]->Edition : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Price" form element 
 * for section three of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getPrice() {
		return "<input autocomplete=\"off\" class=\"input-mini validate[required,custom[integer],min[0],max[999]]\" id=\"price\" max=\"999\" min=\"0\" name=\"price\" type=\"number\" value=\"" . htmlentities($this->data ? $this->data[0]->Price : "5") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Writing or
 * Markings" form element for section three of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getWriting() {
		$checkedYes = $this->data[0]->Written == "1";
		$checkedNo = $this->data[0]->Written == "0";
	
	//Return the generated output
		return "<div class=\"btn-group\" data-toggle=\"buttons-radio\">
<input autocomplete=\"off\"" . ($checkedYes ? " checked" : "") . " data-toggle=\"button\" id=\"written-yes\" name=\"written\" type=\"radio\" value=\"1\">
<label class=\"btn" . ($checkedYes ? " active" : "") . "\" for=\"written-yes\" id=\"written-yes-label\">Yes</label>
<input autocomplete=\"off\"" . ($checkedNo ? " checked" : "") . " data-toggle=\"button\" id=\"written-no\" name=\"written\" type=\"radio\" value=\"0\">
<label class=\"btn" . ($checkedNo ? " active" : "") . "\" for=\"written-no\" id=\"written-no-label\">No</label>
</div>";
	}
	
/**
 * Output a prefilled form element containing the "Condition" form 
 * element for section three of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getCondition() {
		$poor = $this->data[0]->Condition == "1";
		$fair = $this->data[0]->Condition == "2";
		$good = $this->data[0]->Condition == "3";
		$veryGood = $this->data[0]->Condition == "4";
		$excellent = $this->data[0]->Condition == "5";
	
	//Return the generated output
		return "<div class=\"btn-group\" data-toggle=\"buttons-radio\">
<input autocomplete=\"off\"" . ($fair ? " checked" : "") . " data-toggle=\"button\" id=\"fair\" name=\"condition\" type=\"radio\" value=\"1\">
<label class=\"btn" . ($fair ? " active" : "") . "\" for=\"fair\" id=\"fair-label\">Fair</label>
<input autocomplete=\"off\"" . ($poor ? " checked" : "") . " data-toggle=\"button\" id=\"poor\" name=\"condition\" type=\"radio\" value=\"2\">
<label class=\"btn" . ($poor ? " active" : "") . "\" for=\"poor\" id=\"poor-label\">Poor</label>
<input autocomplete=\"off\"" . ($good ? " checked" : "") . " data-toggle=\"button\" id=\"good\" name=\"condition\" type=\"radio\" value=\"3\">
<label class=\"btn" . ($good ? " active" : "") . "\" for=\"good\" id=\"good-label\">Good</label>
<input autocomplete=\"off\"" . ($veryGood ? " checked" : "") . " data-toggle=\"button\" id=\"very-good\" name=\"condition\" type=\"radio\" value=\"4\">
<label class=\"btn" . ($veryGood ? " active" : "") . "\" for=\"very-good\" id=\"very-good-label\">Very Good</label>
<input autocomplete=\"off\"" . ($excellent ? " checked" : "") . " data-toggle=\"button\" id=\"excellent\" name=\"condition\" type=\"radio\" value=\"5\">
<label class=\"btn" . ($excellent ? " active" : "") . "\" for=\"excellent\" id=\"excellent-label\">Excellent</label>
</div>";
	}
	
/**
 * Output a prefilled form element containing the "recurrencing until" form 
 * element for section three of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getEndDate() {
		if ($this->recurring) {
			$dateFormatter = new \DateTime($this->data[0]->EndDate);
			
			return "<input autocomplete=\"off\" class=\"validate[required,custom[date]]\" id=\"until\" name=\"until\" placeholder=\"How long can you share a ride?\" type=\"text\" value=\"" . htmlentities($dateFormatter->format("m/d/Y")) . "\">";
		} else {
			return "<input autocomplete=\"off\" class=\"validate[required,custom[date],past[now]]\" disabled id=\"until\" name=\"until\" placeholder=\"How long can you share a ride?\" type=\"text\" value=\"\">";
		}
	}
	
/**
 * Output a prefilled form element containing the "Comments" form element 
 * for section four of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  v1.0 Dev
*/
	
	public function getComments() {
		return "<textarea id=\"comments\" name=\"comments\">" . ($this->data ? $this->data[0]->Comments : "") . "</textarea>";
	}
}
?>