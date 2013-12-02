<?php
/**
 * Book Information class
 *
 * ----------------------------------------------------------------
 *
 * DIFFERENCES FROM THE "COURSE" CLASS TO ALLEVIATE CONFUSION:
 *
 * - Provides detailed information about a particular book.
 * - Information supplied by this class partains to a particular
 *   book.
 * - Suggests information for NEW books being added to the system.
 *
 * ----------------------------------------------------------------
 *
 * This class is used to fetch all available information regarding
 * books. Some of this classes abilities include:
 *  - Fetch the details of a book by its ID.
 *  - Fetch the details of a book by its ISBN10 or ISBN13.
 *  - Generate a book quick view object.
 *  - Suggest a cover for a new book.
 *  - Purify a string for use in a URL.
 * 
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.display
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/APIs/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Isbn.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Network_Connection_Error.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/No_Data_Returned.php");
require_once(dirname(dirname(__FILE__)) . "/processing/Proxy.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Book {
/**
 * Fetch the details of a book by its ID.
 *
 * @access public
 * @param  int    $ID The ID of the book to fetch from the database
 * @return object     The object returned from the SQL query containing all available book data
 * @since  3.0.0
 * @static
*/
	
	public static function details($ID) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID LEFT JOIN `ffi_be_bookcourses` ON ffi_be_sale.SaleID = ffi_be_bookcourses.SaleID LEFT JOIN `ffi_be_courses` ON ffi_be_bookcourses.Course = ffi_be_courses.Code LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Merchant` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_sale.MerchantID = users.ID WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND `Sold` = '0' AND ffi_be_sale.SaleID = %d ORDER BY `Number` ASC, `Section` ASC", $ID));
	}
	
/**
 * Get a set of information about a book by providing either
 * and ISBN10 or ISBN13 value. The returned information will
 * include the following details:
 *  - System ID
 *  - ISBN10
 *  - ISBN13
 *  - Title
 *  - Author
 *  - Edition
 *  - Cover Image
 *  - List of courses where this book has been listed
 *
 * @access public
 * @param  string            $ISBN       The ISBN10 or ISBN13 of the book of interest
 * @param  bool              $JSONEncode Whether or not the returned data should be JSON encoded
 * @return array|string                  A set of information which can be used to identify the book            
 * @since  3.0.0
 * @static
 * @throws No_Data_Returned             Thrown if the ISBN does not exist in the database
 * @throws Validation_Failed            Thrown if the ISBN is invalid
*/

	public static function getBookByISBN($ISBN, $JSONEncode = true) {
		global $wpdb;
		
	//Validate the ISBN and fetch the associated book's information and list of courses in which it has been used
		if (\Isbn::validate10($ISBN)) {
			$data = $wpdb->get_results($wpdb->prepare("SELECT ffi_be_books.BookID, `ISBN10`, `ISBN13`, `Title`, `Author`, `Edition`, `ImageID`, `CourseID`, `Name`, `Code`, `Section`, `Number` FROM `ffi_be_books` LEFT JOIN `ffi_be_sale` ON ffi_be_books.BookID = ffi_be_sale.BookID LEFT JOIN `ffi_be_bookcourses` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID LEFT JOIN `ffi_be_courses` ON ffi_be_courses.Code = ffi_be_bookcourses.Course WHERE `ISBN10` = %s GROUP BY ffi_be_courses.Name , ffi_be_bookcourses.Number , ffi_be_bookcourses.Section ORDER BY ffi_be_courses.Name ASC , ffi_be_bookcourses.Number ASC , ffi_be_bookcourses.Section ASC", $ISBN));
		} elseif (\Isbn::validate13($ISBN)) {
			$data = $wpdb->get_results($wpdb->prepare("SELECT ffi_be_books.BookID, `ISBN10`, `ISBN13`, `Title`, `Author`, `Edition`, `ImageID`, `CourseID`, `Name`, `Code`, `Section`, `Number` FROM `ffi_be_books` LEFT JOIN `ffi_be_sale` ON ffi_be_books.BookID = ffi_be_sale.BookID LEFT JOIN `ffi_be_bookcourses` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID LEFT JOIN `ffi_be_courses` ON ffi_be_courses.Code = ffi_be_bookcourses.Course WHERE `ISBN13` = %s GROUP BY ffi_be_courses.Name , ffi_be_bookcourses.Number , ffi_be_bookcourses.Section ORDER BY ffi_be_courses.Name ASC , ffi_be_bookcourses.Number ASC , ffi_be_bookcourses.Section ASC", $ISBN));
		} else {
			throw new Validation_Failed("The given value was not in ISBN10 or ISBN13 format");
		}
		
	//Check and see if any data was returned
		if (!count($data)) {
			throw new No_Data_Returned("This ISBN does not match any existing records");
		}
		
		$info = &$data[0];
		
	//Construct a JSON encoded object containing the book's information and list of courses in which it has been used
		$return = array (
			"author"   => $info->Author,
			"BookID"   => $info->BookID,
			"courses"  => array(),
			"edition"  => $info->Edition,
			"imageURL" => Cloudinary::cover($info->ImageID),
			"ISBN10"   => $info->ISBN10,
			"ISBN13"   => $info->ISBN13,
			"title"    => $info->Title,		
		);
		
		if (!is_null($info->CourseID)) {
			foreach($data as $course) {
				array_push($return['courses'], array (
					"code"    => $course->Code,
					"name"    => $course->Name,
					"number"  => $course->Number,
					"section" => $course->Section
				));
			}
		}
		
		return $JSONEncode ? json_encode($return) : $return;
	}
	
/**
 * This function will generate the HTML for a quick view object
 * for a particular book. The final presentation of a quick view 
 * object will look like this:
 *
 *     |------------------------|
 *     |                        |
 *     |                        |
 *     |                        |
 *     |                        |
 *     |         Book           |
 *     |         Cover          |
 *     |         Image          |
 *     |         Here           |
 *     |                        |
 *     |                        |
 *     |                        |
 *     |------------------------|
 *            <Book Title>
 *
 *              <Price>
 *
 * The object will transition into an expanded view when the user
 * rolls his or her mouse over it to show the author, merchant, 
 * condition, and price.
 *
 * @access public
 * @param  int    $ID        The sale ID of the book
 * @param  string $title     The title of the book
 * @param  string $author    The author of the book
 * @param  int    $condition A numerical value (1 - 5) indicating the book's condition, 5 being excellent
 * @param  int    $price     The price of the book, rounded to the dollar
 * @param  string $imageID   The ID of the image of the book
 * @return string            The HTML to generate a quick view object
 * @since  3.0.0
 * @static
*/

	public static function quickView($ID, $title, $author, $condition, $price, $imageID) {
		global $essentials;

		$classes = array("poor", "fair", "good", "very-good", "excellent");
		$image = Cloudinary::coverPreview($imageID);
		$link = $essentials->friendlyURL("book/" . $ID . "/" . self::URLPurify($title));

		return "
<li>
<a href=\"" . $link . "\">
<img src=\"" . $image . "\">
</a>

<div>
<a href=\"" . $link . "\"><h3>" . $title . "</h3></a>
<a href=\"" . $link . "\"><h4>by " . $author . "</h4></a>
<p class=\"condition " . $classes[$condition - 1] . "\"><strong>Condition:</strong></p>
<p class=\"price\">\$" . $price . ".00</p>
<button class=\"btn btn-primary purchase\" data-id=\"" . $ID . "\" data-title=\"" . htmlentities($title) . "\" data-author=\"" . htmlentities($author) . "\" data-image=\"" . $image . "\" data-price=\"" . $price . "\"><span class=\"large\">Buy for </span>$" . $price . ".00</button>
</div>
</li>
";
	}
	
/**
 * Go to the InvisibleHand API and return a list of book covers which
 * match a particular ISBN. This method does not check if the local
 * database already has a suggestion for a particular ISBN, and will
 * return a URL to a generic cover if none are available.
 *
 * @access public
 * @param  int                      $ISBN The ISBN10 or ISBN13 for which to suggest a cover
 * @return array<string>                  A list of URLs to possible covers for the ISBN
 * @since  3.0.0
 * @static
 * @throws Network_Connection_Error       [Bubbled up] Thrown in the server could not connect to the InvisibleHand API
 * @throws Validation_Failed              Thrown if the ISBN is invalid
*/
	
	public static function suggestCovers($ISBN) {
		global $wpdb;

		$maxResults = 12;

	//Validate the ISBN
		if (!\Isbn::validate($ISBN)) {
			throw new Validation_Failed("The given value was not in ISBN10 or ISBN13 format");
		}
		
	//Send the request to the InvisibleHand API server
		$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_apis`");
		$URL = "https://us.api.invisiblehand.co.uk/v1/products?query=" . \ISBN::clean($ISBN) . "&app_id=" . $APIData[0]->InvisibleHandAppID . "&app_key=" . $APIData[0]->InvisibleHandAppKey;
		$request = new Proxy($URL);
		$response = json_decode($request->fetch());
		
	//Parse the JSON response from the InvisibleHand API and return only the book cover images
		$counter = 1;
		$items = $response->results;		
		$return = array();
		$URL = "";

		foreach($items as $book) {
			if (isset($book->image_url) && $book->image_url != "") {
				$URL = $book->image_url;
					
			//Fetches the largest possible image from Amazon, by removing all image resize parameters
			//http://aaugh.com/imageabuse.html
				if (strpos($URL, "ecx.images-amazon.com") !== false) {
					$exploded = explode(".", $URL);
						
					if (count($exploded) > 4) {
						$URL = $exploded[0] . "." . $exploded[1] . "." . $exploded[2] . "." . $exploded[4];
					}
				}
					
				array_push($return, $URL);
					
				if ($counter++ >= $maxResults) {
					break;
				}
			}
		}
			
		if (!count($return)) {
			array_push($return, get_site_url() . "/wp-content/plugins/book-exchange/app/system/images/book-covers/unavailable-cover.jpg");
		}

		return json_encode($return);
	}
	
/**
 * This function will take a string and prepare it for use in a
 * URL by removing any spaces and special characters, and then 
 * making all characters lower case, which is this plugin's
 * convention when placing strings in a URL.
 * 
 * @access public
 * @param  string $name The name of a state
 * @return string       The URL purified version of the string
 * @since  3.0.0
 * @static
*/

	public static function URLPurify($name) {
		$name = preg_replace("/[^a-zA-Z0-9\s\-]/", "", $name); //Remove all non-alphanumeric characters, except for spaces
		$name = preg_replace("/[\s]/", "-", $name);            //Replace remaining spaces with a "-"
		$name = str_replace("--", "-", $name);                 //Replace "--" with "-", will occur if a something like " & " is removed
		return strtolower($name);
	}
}
?>