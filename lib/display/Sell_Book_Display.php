<?php
/**
 * Sell Books Display class
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
 * @package   lib.display
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Course.php");
require_once(dirname(dirname(__FILE__)) . "/APIs/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/No_Data_Returned.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Sell_Book_Display {	
/**
 * Hold the results of the SQL query.
 *
 * @access private
 * @type   object
*/
	
	private $data;

/**
 * Hold the first row of results from the SQL query.
 *
 * @access private
 * @type   object
*/
	
	private $details;

/**
 * CONSTRUCTOR
 *
 * Grab the data from the database, see if any data was returned, 
 * and if not, redirect to the URL indicated by $failRedirect.
 * 
 * @access public
 * @param  int              $ID     The ID of the book to fetch from the database
 * @param  int              $userID The ID of the user requesting this page
 * @return void
 * @throws No_Data_Returned         Thrown when no data is returned from the database
 * @since  3.0.0
*/
	
	public function __construct($ID, $userID) {
		global $wpdb;
		
	//Don't continue if the ID is zero
        if ($ID == 0) {
			$this->data = false;
			return;
		}
		
	//Try to fetch the data
		$this->data = $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID LEFT JOIN `ffi_be_bookcourses` ON ffi_be_sale.SaleID = ffi_be_bookcourses.SaleID WHERE ffi_be_sale.SaleID = %d AND ffi_be_sale.MerchantID = %d AND `Sold` = '0'", $ID, $userID));
			
	//SQL returned 0 tuples, "Leave me!" - http://johnnoble.net/img/photos/denethor_a.jpg
		if (!count($this->data)) {
			throw new No_Data_Returned("No book information exists for the given ID");
		}
		
		$this->details = &$this->data[0];
	}
	
/**
 * Output a prefilled form element containing the "ISBN10" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getISBN10() {
		if ($this->data) {
			return "<input autocomplete=\"off\" class=\"validate[required,custom[ISBN10]]\" disabled id=\"ISBN10\" name=\"ISBN10\" type=\"text\" value=\"" . htmlentities($this->details->ISBN10) . "\">";
		} else {
			return "<input autocomplete=\"off\" class=\"validate[required,custom[ISBN10]]\" id=\"ISBN10\" name=\"ISBN10\" type=\"text\" value=\"\">";
		}
	}
	
/**
 * Output a prefilled form element containing the "ISBN13" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getISBN13() {
		if ($this->data) {
			return "<input autocomplete=\"off\" class=\"validate[required,custom[ISBN13]]\" disabled id=\"ISBN13\" name=\"ISBN13\" type=\"text\" value=\"" . htmlentities($this->details->ISBN13) . "\">";
		} else {
			return "<input autocomplete=\"off\" class=\"validate[required,custom[ISBN13]]\" id=\"ISBN13\" name=\"ISBN13\" type=\"text\" value=\"\">";
		}
	}
	
/**
 * Output a prefilled form element containing the "Title" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getTitle() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"title\" name=\"title\" type=\"text\" value=\"" . htmlentities($this->data ? $this->details->Title : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Author(s)" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getAuthors() {
		return "<input autocomplete=\"off\" class=\"validate[required]\" id=\"author\" name=\"author\" type=\"text\" value=\"" . htmlentities($this->data ? $this->details->Author : "") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Edition" form element 
 * for section one of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getEdition() {
		return "<input autocomplete=\"off\" id=\"edition\" name=\"edition\" type=\"text\" value=\"" . htmlentities($this->data ? $this->details->Edition : "") . "\">";
	}

/**
 * Output a prefilled form element containing the "Book Cover" form section 
 * for section two of this form.
 * 
 * @access public
 * @return string   A form section prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getCover() {
		$return = "<input class=\"cover-input validate[required]\" id=\"imageURL\" name=\"imageURL\" type=\"text\" value=\"" . htmlentities($this->data ? Cloudinary::coverPreview($this->details->ImageID) : "") . "\">
<div class=\"book-cover\">
";

		if ($this->data) {
			$return .= "<ul>
<li><img class=\"selected suggestion\" src=\"" . Cloudinary::coverPreview($this->details->ImageID) . "\"></li>
</ul>
";
		}

		$return .= "</div>";

		return $return;
	}

/**
 * Output a prefilled table containing a listing of courses, numbers, and
 * sections for which this book was used.
 * 
 * @access public
 * @return string   A table prefilled with the specific classes where this book was used
 * @since  3.0.0
*/

	public function getCourses() {
		$allCourses = Course::getCourses();
		$return = "<table id=\"dependent-courses\">
<thead>
<th>Course</th>
<th>Number</th>
<th>Section</th>
<th></th>
</thead>

<tbody>";

	//Iterate through each of the returned tuples, to fetch each of the courses
		if ($this->data) {
			foreach ($this->data as $info) {
				$return .= "
<tr>
<td>
<span>Course:</span>
<select class=\"validate[required]\" name=\"course[]\">
<option value=\"\">- Select Course -</option>
";
	
			//Generate the drop down menu of courses
				foreach ($allCourses as $course) {
					$return .= "<option" . ($info->Course == $course->Code ? " selected" : "") . " value=\"" . $course->Code . "\">" . htmlentities($course->Name) . "</option>
";
				}
				
				$return .= "</select>
</td>
<td>
<span>Number:</span>
<input class=\"input-small validate[required,custom[integer],min[101],max[499]]\" max=\"499\" min=\"101\" name=\"number[]\" type=\"number\" value=\"" . $info->Number . "\">
</td>
<td>
<span>Section:</span>
<select class=\"input-small validate[required]\" name=\"section[]\">
<option value=\"\">-</option>
";
				
			//Generate the drop down menu of sections letters
				foreach(range("A", "Z") as $letter) {
					$return .= "<option" . ($info->Section == $letter ? " selected" : "") . " value=\"" . $letter . "\">" . $letter . "</option>
";
				}
					
				$return .= "</select>
</td>
<td class=\"delete\">
<span></span>
</td>
</tr>
";
			}
	//Print out a row with the default values
		} else {
			$return .= "
<tr>
<td>
<span>Course:</span>
<select class=\"validate[required]\" name=\"course[]\">
<option value=\"\">- Select Course -</option>
";
	
			//Generate the drop down menu of courses
				foreach ($allCourses as $course) {
					$return .= "<option value=\"" . $course->Code . "\">" . htmlentities($course->Name) . "</option>
";
				}
				
				$return .= "</select>
</td>
<td>
<span>Number:</span>
<input class=\"input-small validate[required,custom[integer],min[101],max[499]]\" max=\"499\" min=\"101\" name=\"number[]\" type=\"number\" value=\"\">
</td>
<td>
<span>Section:</span>
<select class=\"input-small validate[required]\" name=\"section[]\">
<option value=\"\">-</option>
";
				
			//Generate the drop down menu of sections letters
				foreach(range("A", "Z") as $letter) {
					$return .= "<option value=\"" . $letter . "\">" . $letter . "</option>
";
				}
					
				$return .= "</select>
</td>
<td class=\"delete\">
<span></span>
</td>
</tr>
";
		}
		
		$return .= "</tbody>
</table>";

		return $return;
	}
	
/**
 * Output a prefilled form element containing the "Price" form element 
 * for section four of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getPrice() {
		return "<input autocomplete=\"off\" class=\"input-mini validate[required,custom[integer],min[0],max[999]]\" id=\"price\" max=\"999\" min=\"0\" name=\"price\" type=\"number\" value=\"" . htmlentities($this->data ? $this->details->Price : "5") . "\">";
	}
	
/**
 * Output a prefilled form element containing the "Writing or
 * Markings" form element for section four of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getWriting() {
		$checkedYes = $this->details->Written == "1";
		$checkedNo = $this->details->Written == "0" || !$this->data;
	
	//Return the generated output
		return "<div class=\"btn-group\" data-toggle=\"buttons-radio\">
<input autocomplete=\"off\"" . ($checkedYes ? " checked" : "") . " data-toggle=\"button\" id=\"written-yes\" name=\"written\" type=\"radio\" value=\"1\">
<label class=\"btn\" for=\"written-yes\" id=\"written-yes-label\">Yes</label>
<input autocomplete=\"off\"" . ($checkedNo ? " checked" : "") . " data-toggle=\"button\" id=\"written-no\" name=\"written\" type=\"radio\" value=\"0\">
<label class=\"btn\" for=\"written-no\" id=\"written-no-label\">No</label>
</div>";
	}
	
/**
 * Output a prefilled form element containing the "Condition" form 
 * element for section four of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getCondition() {
		$poor = $this->details->Condition == "1";
		$fair = $this->details->Condition == "2";
		$good = $this->details->Condition == "3";
		$veryGood = $this->details->Condition == "4" || !$this->data;
		$excellent = $this->details->Condition == "5";
	
	//Return the generated output
		return "<div class=\"btn-group\" data-toggle=\"buttons-radio\">
<input autocomplete=\"off\"" . ($poor ? " checked" : "") . " data-toggle=\"button\" id=\"poor\" name=\"condition\" type=\"radio\" value=\"1\">
<label class=\"btn\" for=\"poor\" id=\"poor-label\"><span class=\"large\">Poor</span><span class=\"small\">1</span></label>
<input autocomplete=\"off\"" . ($fair ? " checked" : "") . " data-toggle=\"button\" id=\"fair\" name=\"condition\" type=\"radio\" value=\"2\">
<label class=\"btn\" for=\"fair\" id=\"fair-label\"><span class=\"large\">Fair</span><span class=\"small\">2</span></label>
<input autocomplete=\"off\"" . ($good ? " checked" : "") . " data-toggle=\"button\" id=\"good\" name=\"condition\" type=\"radio\" value=\"3\">
<label class=\"btn\" for=\"good\" id=\"good-label\"><span class=\"large\">Good</span><span class=\"small\">3</span></label>
<input autocomplete=\"off\"" . ($veryGood ? " checked" : "") . " data-toggle=\"button\" id=\"very-good\" name=\"condition\" type=\"radio\" value=\"4\">
<label class=\"btn\" for=\"very-good\" id=\"very-good-label\"><span class=\"large\">Very Good</span><span class=\"small\">4</span></label>
<input autocomplete=\"off\"" . ($excellent ? " checked" : "") . " data-toggle=\"button\" id=\"excellent\" name=\"condition\" type=\"radio\" value=\"5\">
<label class=\"btn\" for=\"excellent\" id=\"excellent-label\"><span class=\"large\">Excellent</span><span class=\"small\">5</span></label>
</div>";
	}
	
/**
 * Output a prefilled form element containing the "Comments" form element 
 * for section five of this form.
 * 
 * @access public
 * @return string   A form item prefilled with a value from either the database or a default value
 * @since  3.0.0
*/
	
	public function getComments() {
		return "<textarea id=\"comments\" name=\"comments\">" . ($this->data ? $this->details->Comments : "") . "</textarea>";
	}
}
?>