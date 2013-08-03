<?php
/**
 * Cloudinary API class
 *
 * This class is designed to interact with the Cloudinary
 * content delivery service. Some of this classes abilities
 * include:
 *  - obtain the name of the Cloudinary cloud name from the 
 *    API table in the database
 *  - generate links to various styles of book covers
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

class Cloudinary {
/**
 * Hold the Cloudinary API cloud name.
 *
 * @access protected
 * @static
 * @type   boolean|string
*/

	protected static $cloudName = false;
	
/**
 * Fetch the Cloudinary API cloud name
 * 
 * @access protected
 * @return void
 * @static
 * @since  3.0
*/

	protected static function getCloudName() {
		global $wpdb;
		
		if (!self::$cloudName) {
			$APIs = $wpdb->get_results("SELECT `CloudinaryCloudName` FROM `ffi_be_apis`");
			self::$cloudName = $APIs[0]->CloudinaryCloudName;
		}
	}
	
/**
 * Generate the URL of the small book cover background image for display
 * in the quick link boxes, which are used in the sidebars
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @see                       includes.display.Book.quickLink()
 * @static
 * @since  3.0
*/

	public static function backgroundSmall($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_300,h_100,c_fill,g_north,e_vibrance:100/" . $imageKey;
	}
	
/**
 * Generate the URL of the large book cover background image for the book 
 * details page splash image
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @static
 * @since  3.0
*/

	public static function backgroundLarge($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_1500,h_350,c_fill,g_north,e_blur:800/e_vibrance:100/" . $imageKey;
	}
	
/**
 * Generate the URL of the scaled book cover image for display on the
 * book details page
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @static
 * @since  3.0
*/

	public static function cover($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_275,h_355,c_pad,e_vibrance:100/" . $imageKey;
	}
	
/**
 * Generate the URL of the scaled book cover image for display in search 
 * results and book browsing pages
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @see                       includes.display.Book.quickView()
 * @static
 * @since  3.0
*/

	public static function coverPreview($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_200,h_300,c_pad,e_vibrance:100/" . $imageKey;
	}
}
?>
