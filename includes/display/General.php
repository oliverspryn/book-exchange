<?php
/**
 * General display class
 *
 * This class is used to fetch or generate data which is commonly used
 * through out this plugin, such images for books from the Cloudinary
 * service.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.display
 * @since     3.0
*/

namespace FFI\BE;

class General {
/**
 * Hold the Cloudinary API cloud name.
 *
 * @access private
 * @static
 * @type   boolean|string
*/

	private static $cloudName = false;
	
/**
 * Fetch the Cloudinary API cloud name
 * 
 * @access private
 * @return void
 * @static
 * @since  3.0
*/

	private static function getCloudName() {
		global $wpdb;
		
		if (!self::$cloudName) {
			$APIs = $wpdb->get_results("SELECT `Cloudinary` FROM `ffi_be_new_apis`");
			self::$cloudName = $APIs[0]->Cloudinary;
		}
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

	public static function bookBackgroundLarge($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_1500,h_350,c_fill,e_blur:800/e_vibrance:100/" . $imageKey;
	}
	
/**
 * Generate the URL of the small book cover background image for display
 * in the "What's New" and "What's Hot" sections
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @static
 * @since  3.0
*/

	public static function bookBackgroundSmall($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_300,h_100,c_fill,g_north/" . $imageKey;
	}
	
/**
 * Generate the URL of the scaled book cover image for display in search 
 * results and book browsing pages
 * 
 * @access public
 * @param  string   $imageKey The key of the image to fetch from Cloudinary
 * @return string             The URL of the image with the supplied key
 * @static
 * @since  3.0
*/

	public static function bookCoverPreview($imageKey) {
		self::getCloudName();
		
		return "//cloudinary-a.akamaihd.net/" . self::$cloudName . "/image/upload/w_125,c_pad,e_vibrance:100/" . $imageKey;
	}
}
?>