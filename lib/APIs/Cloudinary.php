<?php
/**
 * Cloudinary API class
 *
 * This class is designed to interact with the Cloudinary
 * content delivery service. Some of this classes abilities
 * include:
 *  - Generate links to various styles of book covers.
 *  - Check the status of the image in the database, and 
 *    provide a placeholder image, if necessary.
 *  - Obtain the name of the Cloudinary cloud name from the 
 *    API table in the database.
 *  - Upload an image to Cloudinary and return the data from
 *    the upload process.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.APIs
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/third-party/Cloudinary/Cloudinary.php");
require_once(dirname(dirname(__FILE__)) . "/third-party/Cloudinary/Uploader.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");
require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-includes/link-template.php");

class Cloudinary {
/**
 * Hold the Cloudinary API cloud name.
 *
 * @access private
 * @static
 * @type   bool|string
*/

	private static $cloudName = false;
	
/**
 * Generate the URL of the large book cover background image for the book 
 * details page splash image
 * 
 * @access public
 * @param  string $imageKey The key of the image to fetch from Cloudinary
 * @return string           The URL of the image with the supplied key
 * @static
 * @since  3.0
*/

	public static function background($imageKey) {
		self::getCloudName();
		$cover = self::checkStatus($imageKey);
		
		return $cover ? ($cover . "background.jpg") : ("//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/c_fill,e_blur:800,h_385,w_2000/e_vibrance:100/" . $imageKey);
	}
	
/**
 * Check the status of the image in the database. If a temporary
 * book cover is necessary, then the beginning of the URL of the
 * correct cover will be returned depending on the state, like so:
 *  - Pending approval: <url to images folder>/pending-
 *  - Inappropriate:    <url to images folder>/inappropriate-
 *  - Unavailable:      <url to images folder>/unavailable-
 * 
 * Each of the functions calling this method can concatenate the 
 * end of the URL onto the return string, as each calling function
 * needs.
 *
 * If the function returns false, then the cover has been approved,
 * and no temporary image is necessary.
 *
 * @access private
 * @param  string      $image The unique ID or URL of the image in the "ffi_be_books" table
 * @return bool|string        A string with the beginning of the URL to the temporary cover, or "false" if one is not necessary
 * @since  3.0
 * @static
*/

	private static function checkStatus($image) {
		global $wpdb;
		
		$baseURL = get_site_url();
		$state = $wpdb->get_results($wpdb->prepare("SELECT `ImageState` FROM `ffi_be_books` WHERE `ImageID` = %s", $image));
		$images = array(
			"APPROVED"         => false,
			"PENDING_APPROVAL" => $baseURL . "/wp-content/plugins/book-exchange/app/system/images/book-covers/pending-",
			"INAPPROPRIATE"    => $baseURL . "/wp-content/plugins/book-exchange/app/system/images/book-covers/inappropriate-",
			"UNAVAILABLE"      => $baseURL . "/wp-content/plugins/book-exchange/app/system/images/book-covers/unavailable-"
		);

		return $images[$state[0]->ImageState];
	}
	
/**
 * Generate the URL of the scaled book cover image for display on the
 * book details page.
 * 
 * @access public
 * @param  string $imageKey The key of the image to fetch from Cloudinary
 * @return string           The URL of the image with the supplied key
 * @since  3.0
 * @static
*/

	public static function cover($imageKey) {
		self::getCloudName();
		$cover = self::checkStatus($imageKey);
		
		return $cover ? ($cover . "cover.jpg") : ("//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/c_pad,e_vibrance:100,h_355,w_275/" . $imageKey);
	}
	
/**
 * Generate the URL of the scaled book cover image for display in search 
 * results and book browsing pages.
 * 
 * @access public
 * @param  string $imageKey The key of the image to fetch from Cloudinary
 * @return string           The URL of the image with the supplied key
 * @see                     lib.display.Book.quickView()
 * @since  3.0
 * @static
*/

	public static function coverPreview($imageKey) {
		self::getCloudName();
		$cover = self::checkStatus($imageKey);
		
		return $cover ? ($cover . "preview.jpg") : ("//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/c_pad,e_vibrance:100,h_300,w_200/" . $imageKey);
	}
	
/**
 * Fetch the Cloudinary API cloud name.
 * 
 * @access private
 * @return void
 * @since  3.0
 * @static
*/

	private static function getCloudName() {
		global $wpdb;
		
		if (!self::$cloudName) {
			$APIs = $wpdb->get_results("SELECT `CloudinaryCloudName` FROM `ffi_be_apis`");
			self::$cloudName = $APIs[0]->CloudinaryCloudName;
		}
	}
	
/**
 * Upload an image to Cloudinary.
 * 
 * @access public
 * @param  string                   $imageURL The URL of the image to fetch and upload
 * @return array<mixed>                       A set of data returned from Cloudinary regarding the details of the uploaded image
 * @since  3.0
 * @static
 * @throws Exception                          [Bubbled up] Thrown when there is an error communicating with or uploading to Cloudinary
 * @throws InvalidArgumentException           [Bubbled up] Thrown when the uploader script is not supplied with the necessary information
*/

	public static function upload($imageURL) {
		global $wpdb;
		$APIData = $wpdb->get_results("SELECT * FROM `ffi_be_apis`");
		
	//Configure the API for uploading
		\Cloudinary::config(array (
			"api_key"    => $APIData[0]->CloudinaryAPIKey,
			"api_secret" => $APIData[0]->CloudinaryAPISecret,
			"cloud_name" => $APIData[0]->CloudinaryCloudName
		));
		
	//Do the uploadz!!!
		return \Cloudinary\Uploader::upload($imageURL);
	}
}
?>