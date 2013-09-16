<?php
/**
 * Book Information class
 *
 * This class is used to fetch all available information regarding
 * books. Some of this classes abilities include:
 *  - fetch the total number of available books
 *  - fetch the details of a book by its ID
 *  - fetch the details of a book by its ISBN
 *  - generate a book quick view object
 * 
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(__FILE__)) . "/APIs/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/display/Course.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Isbn.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/Network_Connection_Error.php");
require_once(dirname(dirname(__FILE__)) . "/exceptions/No_Data_Returned.php");

class Book {
/**
 * Count the number of available books
 *
 * @access public
 * @return int    The number of available books
 * @since  3.0
 * @static
*/

	public static function total() {
		global $wpdb;
		
		$total = $wpdb->get_results("SELECT COUNT(*) AS `Total` FROM `ffi_be_sale` WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND `Sold` = '0'");
		
		return $total[0]->Total;
	}
	
/**
 * Count the number of available books in a particular course.
 *
 * @access public
 * @return int    The number of available books in a course
 * @since  3.0
 * @static
*/

	public static function totalInCourse($courseURL) {
		global $wpdb;
		
		$total = $wpdb->get_results($wpdb->prepare("SELECT `Total` FROM `ffi_be_courses` LEFT JOIN(SELECT *, COUNT(`Course`) AS `Total` FROM `ffi_be_courses` RIGHT JOIN (SELECT `Course` FROM `ffi_be_bookcourses` LEFT JOIN `ffi_be_sale` ON ffi_be_bookcourses.SaleID = ffi_be_sale.SaleID WHERE DATE_ADD(ffi_be_sale.Upload, INTERVAL(SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND ffi_be_sale.Sold = '0'GROUP BY ffi_be_bookcourses.SaleID) AS `q1` ON ffi_be_courses.Code = q1.Course GROUP BY q1.Course) AS `q2` ON ffi_be_courses.Code = q2.Code WHERE ffi_be_courses.URL = %s ORDER BY ffi_be_courses.Name ASC", $courseURL));
		
		return (int)$total[0]->Total;
	}
	
	public static function generateArborJSInit($courseName, $courseURL) {
		global $essentials;
		
		$completedNum = array();
		$courseListing = Course::getNumbersWithBooks($courseURL);
		$JS = "\$(function(){var graph={nodes:{'" . $courseName . "':{alpha:1,color:'#0044CC',shape:'dot'}";

	//List the course numbers, without duplicates
		foreach($courseListing as $courseInfo) {
			if (!in_array($courseInfo->Number, $completedNum)) {
				$JS .= ",'" . $courseInfo->Number . "':{alpha:1,color:'#A7AF00',shape:'dot'}";
				array_push($completedNum, $courseInfo->Number);
			}
		}

	//List the course sections
		foreach($courseListing as $courseInfo) {
			$JS .= ",'" . $courseInfo->Number . " " . $courseInfo->Section . "':{alpha:0,color:'orange',link:'" . $essentials->friendlyURL("browse/" . $courseURL . "/" . $courseInfo->Number . "/" . $courseInfo->Section) . "',shape:'rect'}";
		}

		$JS .= "},edges:{'" . $courseName . "':{";

	//Connect the course numbers to the primary node
		foreach($courseListing as $courseInfo) {
			$JS .= "'" . $courseInfo->Number . "':{length:0.8},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "},";

	//Connect the course sections to the course numbers
		foreach($completedNum as $courseNum) {
			$JS .= "'" . $courseNum . "':{";

			foreach($courseListing as $courseInfo) {
				$JS .= $courseInfo->Number == $courseNum ? ("'" . $courseInfo->Number . " " . $courseInfo->Section . "':{},") : "";
			}

			$JS = rtrim($JS, ",");
			$JS .= "},";
		}

		$JS = rtrim($JS, ",");

		$JS .= "}};var sys=arbor.ParticleSystem();sys.parameters({dt:0.015,gravity:true,repulsion:5000,stiffness:900});sys.renderer=Renderer('#explorer');sys.graft(graph);})";
		
		return $JS;
	}

/**
 * Fetch the details of a book by its ID
 *
 * @access public
 * @param  int           $ID           The ID of the book to fetch from the database
 * @return object<mixed>               The object returned from the SQL query containing all available book data
 * @since  3.0
 * @static
*/
	
	public static function details($ID) {
		global $wpdb;
		
		return $wpdb->get_results($wpdb->prepare("SELECT * FROM `ffi_be_sale` LEFT JOIN `ffi_be_books` ON ffi_be_sale.BookID = ffi_be_books.BookID LEFT JOIN `ffi_be_bookcourses` ON ffi_be_sale.SaleID = ffi_be_bookcourses.SaleID LEFT JOIN `ffi_be_courses` ON ffi_be_bookcourses.Course = ffi_be_courses.Code LEFT JOIN (SELECT wp_usermeta.user_id AS `ID`, CONCAT(wp_usermeta.meta_value, ' ', last.meta_value) AS `Merchant` FROM `wp_usermeta` LEFT JOIN (SELECT `meta_value`, `user_id` FROM `wp_usermeta` WHERE `meta_key` = 'last_name') AS `last` ON wp_usermeta.user_id = last.user_id WHERE `meta_key` = 'first_name') AS `users` ON ffi_be_sale.MerchantID = users.ID WHERE DATE_ADD(`Upload`, INTERVAL (SELECT `BookExpireMonths` FROM `ffi_be_settings`) MONTH) > CURDATE() AND `Sold` = '0' AND ffi_be_sale.SaleID = %d ORDER BY `Number` ASC, `Section` ASC", $ID));
	}

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
		
	//Construct a JSON encoded object containing the book's information and list of courses in which it has been used
		$return = array(
			"BookID"   => $data[0]->BookID,
			"ISBN10"   => $data[0]->ISBN10,
			"ISBN13"   => $data[0]->ISBN13,
			"title"    => $data[0]->Title,
			"author"   => $data[0]->Author,
			"edition"  => $data[0]->Edition,
			"imageURL" => Cloudinary::cover($data[0]->ImageID),
			"courses"  => array()
		);
		
		if (!is_null($data[0]->CourseID)) {
			foreach($data as $course) {
				array_push($return['courses'], array(
					"code"    => $course->Code,
					"name"    => $course->Name,
					"number"  => $course->Number,
					"section" => $course->Section
				));
			}
		}
		
		return $JSONEncode ? json_encode($return) : $return;
	}

	public static function suggestCovers($ISBN) {
		global $wpdb;

		$maxResults = 12;

	//Validate the ISBN
		if (\Isbn::validate($ISBN)) {
			$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_apis`");
			$URL = "https://us.api.invisiblehand.co.uk/v1/products?query=" . \ISBN::clean($ISBN) . "&app_id=" . $APIData[0]->InvisibleHandAppID . "&app_key=" . $APIData[0]->InvisibleHandAppKey;
			
		//Send the request to the InvisibleHand API server
			$curl = curl_init($URL);

		curl_setopt($curl, CURLOPT_PROXY, "proxy.gcc.edu:8080");
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			$response = json_decode(curl_exec($curl));
			$errorNumber = curl_errno($curl);
			$error = curl_error($curl);
			curl_close($curl);
			
		//Check for any network errors
			if ($errorNumber) {
				throw new Network_Connection_Error("A network connection to the InvisibleHand API has failed. cURL error details: " . $error);
			}
		
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
				array_push($return, get_site_url() . "/wp-content/plugins/book-exchange/app/images/book-covers/unavailable-cover.jpg");
			}

			return json_encode($return);
		} else {
			throw new Validation_Failed("The given value was not in ISBN10 or ISBN13 format");
		}
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
 * @return void
 * @since  3.0
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
 * This function will take either the title of a book and prepare it for
 * use in a URL by removing any spaces and special characters, and then 
 * making all characters lower case, which is this plugin's convention
 * when book titles in a URL.
 * 
 * @access public
 * @param  string $title The title of a book
 * @return string        The URL purified version of the book title
 * @since  3.0
 * @static
*/
	public static function URLPurify($title) {
		$title = preg_replace("/[^a-zA-Z0-9\s]/", "", $title); //Remove all non-alphanumeric characters, except for spaces
		$title = preg_replace("/[\s]/", "-", $title);          //Replace remaining spaces with a "-"
		$title = str_replace("--", "-", $title);               //Replace "--" with "-", will occur if a something like " & " is removed
		$title = trim($title, "-");
		return strtolower($title);
	}
}
?>