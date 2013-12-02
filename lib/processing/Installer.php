<?php
/**
 * Book Exchange Installer class
 *
 * This class will install the Book Exchange by creating 
 * several tables in the database and populating some of them
 * with default values.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.processing
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . "/wp-blog-header.php");

class Installer {
/**
 * CONSTRUCTOR
 *
 * This constructor bootstraps the functionality of the class. It will do so
 * by calling a set of helper functions to build and populate the database.
 * 
 * @access public
 * @return void
 * @since  3.0.0
*/

	public function __construct() {
		$this->createRelations();
		$this->establishFKs();
		$this->populateDefaults();
	}
	
/**
 * This method will set up the database by creating the following
 * relations:
 *  - ffi_be_apis
 *  - ffi_be_bookcourses
 *  - ffi_be_books
 *  - ffi_be_courses
 *  - ffi_be_indexdata
 *  - ffi_be_purchases
 *  - ffi_be_sale
 *  - ffi_be_settings
 * 
 * @access private
 * @return void
 * @since  3.0.0
*/
	
	private function createRelations() {
		global $wpdb;
		
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_apis` (
						`ID` int(1) NOT NULL,
						`CloudinaryCloudName` varchar(25) NOT NULL,
						`CloudinaryAPIKey` varchar(25) NOT NULL,
						`CloudinaryAPISecret` varchar(50) NOT NULL,
						`IndexDenURL` varchar(50) NOT NULL,
						`IndexDenIndex` varchar(25) NOT NULL,
						`IndexDenUsername` varchar(25) NOT NULL,
						`IndexDenPassword` varchar(25) NOT NULL,
						`InvisibleHandAppID` varchar(15) NOT NULL,
						`InvisibleHandAppKey` varchar(50) NOT NULL,
						`MandrillKey` varchar(50) NOT NULL,
						PRIMARY KEY (`ID`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_bookcourses` (
						`SaleID` int(11) NOT NULL,
						`Course` char(4) COLLATE utf8_unicode_ci NOT NULL,
						`Number` char(3) COLLATE utf8_unicode_ci NOT NULL,
						`Section` char(1) COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`SaleID`,`Course`,`Number`,`Section`),
						KEY `FFI_BE_BOOK_COURSES_REFERENCES_COURSES_idx` (`Course`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_books` (
						`BookID` int(11) NOT NULL AUTO_INCREMENT,
						`ISBN10` char(10) COLLATE utf8_unicode_ci NOT NULL,
						`ISBN13` char(13) COLLATE utf8_unicode_ci NOT NULL,
						`Title` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
						`Author` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
						`Edition` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
						`ImageID` varchar(800) COLLATE utf8_unicode_ci NOT NULL,
						`ImageState` enum('APPROVED','PENDING_APPROVAL','INAPPROPRIATE','UNAVAILABLE') COLLATE utf8_unicode_ci DEFAULT 'PENDING_APPROVAL',
						PRIMARY KEY (`BookID`),
						UNIQUE KEY `ISBN10` (`ISBN10`),
						UNIQUE KEY `ISBN13` (`ISBN13`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_courses` (
						`CourseID` int(11) NOT NULL AUTO_INCREMENT,
						`Name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
						`URL` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
						`Code` char(4) COLLATE utf8_unicode_ci NOT NULL,
						`Type` enum('Arts','Science') COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`CourseID`),
						UNIQUE KEY `Name` (`Name`),
						UNIQUE KEY `URL` (`URL`),
						UNIQUE KEY `Code` (`Code`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_indexdata` (
						`SaleID` int(11) NOT NULL,
						`Title` varchar(512) NOT NULL,
						`Author` varchar(512) NOT NULL,
						`Indexed` tinyint(1) DEFAULT '0',
						PRIMARY KEY (`SaleID`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_purchases` (
						`PurchaseID` int(11) NOT NULL AUTO_INCREMENT,
						`BookID` int(11) NOT NULL,
						`Price` int(3) NOT NULL,
						`BuyerID` bigint(20) unsigned NOT NULL,
						`MerchantID` bigint(20) unsigned NOT NULL,
						`Time` datetime NOT NULL,
						PRIMARY KEY (`PurchaseID`),
						KEY `FFI_BE_PURCHASES_REFERENCES_BOOKS_idx` (`BookID`),
						KEY `FFI_BE_PURCHASES_REFERENCES_BUYER_idx` (`BuyerID`),
						KEY `FFI_BE_PURCHASES_REFERENCES_MERCHANT_idx` (`MerchantID`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_sale` (
						`SaleID` int(11) NOT NULL AUTO_INCREMENT,
						`BookID` int(11) NOT NULL,
						`MerchantID` bigint(20) unsigned NOT NULL,
						`Upload` datetime NOT NULL,
						`Sold` tinyint(1) DEFAULT '0',
						`Price` int(3) NOT NULL,
						`Condition` enum('1','2','3','4','5') COLLATE utf8_unicode_ci DEFAULT '4',
						`Written` tinyint(1) DEFAULT '0',
						`Comments` longtext COLLATE utf8_unicode_ci NOT NULL,
						PRIMARY KEY (`SaleID`),
						KEY `FFI_BE_SALE_REFERENCES_MERCHANT_idx` (`MerchantID`),
						KEY `FFI_BE_SALE_REFERENCES_BOOKS_idx` (`BookID`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
					
		$wpdb->query("CREATE TABLE IF NOT EXISTS `ffi_be_settings` (
						`ID` int(1) NOT NULL,
						`BookExpireMonths` int(2) DEFAULT '6',
						`EmailName` varchar(50) NOT NULL,
						`EmailAddress` varchar(50) NOT NULL,
						`TimeZone` enum('Pacific/Honolulu','America/Anchorage','America/Los_Angeles','America/Denver','America/Chicago','America/New_York') DEFAULT 'America/New_York',
						PRIMARY KEY (`ID`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1");
	}
	
/**
 * This method will continue to set up the database by establishing
 * foreign key relations between the tables created thus far, and also
 * with other Wordpress tables. The tables establshing foregin key
 * relationships include:
 *  - ffi_be_bookcourses
 *  - ffi_be_indexdata
 *  - ffi_be_purchases
 *  - ffi_be_sale
 * 
 * @access private
 * @return void
 * @since  3.0.0
*/
	
	private function establishFKs() {
		global $wpdb;
		
		$wpdb->query("ALTER TABLE `ffi_be_bookcourses`
	ADD CONSTRAINT `FFI_BE_BOOK_COURSES_REFERENCES_SALE` FOREIGN KEY (`SaleID`) REFERENCES `ffi_be_sale` (`SaleID`) ON DELETE CASCADE ON UPDATE NO ACTION,
	ADD CONSTRAINT `FFI_BE_BOOK_COURSES_REFERENCES_COURSES` FOREIGN KEY (`Course`) REFERENCES `ffi_be_courses` (`Code`) ON DELETE NO ACTION ON UPDATE NO ACTION");
		
		$wpdb->query("ALTER TABLE `ffi_be_indexdata`
	ADD CONSTRAINT `FFI_BE_INDEXDATA_REFERENCES_SALE` FOREIGN KEY (`SaleID`) REFERENCES `ffi_be_sale` (`SaleID`) ON DELETE NO ACTION ON UPDATE NO ACTION");
						  
		$wpdb->query("ALTER TABLE `ffi_be_purchases`
	ADD CONSTRAINT `FFI_BE_PURCHASES_MERCHANT_REFERENCES_USER` FOREIGN KEY (`MerchantID`) REFERENCES `wp_users` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `FFI_BE_PURCHASES_BUYER_REFERENCES_USER` FOREIGN KEY (`BuyerID`) REFERENCES `wp_users` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `FFI_BE_PURCHASES_REFERENCES_BOOKS` FOREIGN KEY (`BookID`) REFERENCES `ffi_be_books` (`BookID`) ON DELETE NO ACTION ON UPDATE NO ACTION");
	
		$wpdb->query("ALTER TABLE `ffi_be_sale`
	ADD CONSTRAINT `FFI_BE_SALE_REFERENCES_USER` FOREIGN KEY (`MerchantID`) REFERENCES `wp_users` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION,
	ADD CONSTRAINT `FFI_BE_SALE_REFERENCES_BOOKS` FOREIGN KEY (`BookID`) REFERENCES `ffi_be_books` (`BookID`) ON DELETE NO ACTION ON UPDATE NO ACTION");
	}
	
/**
 * This method will continue to set up the database by populating the
 * following tables with a default set of values, if they are not already
 * populated by a previous installation:
 *  - ffi_be_apis
 *  - ffi_be_courses
 *  - ffi_be_settings
 * 
 * @access private
 * @return void
 * @since  3.0.0
*/
	
	private function populateDefaults() {
		global $wpdb;
		
		if (!count($wpdb->get_results("SELECT * FROM `ffi_be_apis`"))) {
			$wpdb->query("INSERT INTO `ffi_be_apis` (`ID`, `CloudinaryCloudName`, `CloudinaryAPIKey`, `CloudinaryAPISecret`, `IndexDenURL`, `IndexDenIndex`, `IndexDenUsername`, `IndexDenPassword`, `InvisibleHandAppID`, `InvisibleHandAppKey`, `MandrillKey`) VALUES (1, '', '', '', '', '', '', '', '', '', '')");
		}
		
		if (!count($wpdb->get_results("SELECT * FROM `ffi_be_courses`"))) {
			$wpdb->query("INSERT INTO `ffi_be_courses` (`CourseID`, `Name`, `URL`, `Code`, `Type`) VALUES
							(1, 'Accounting', 'accounting', 'ACCT', 'Arts'),
							(2, 'Art', 'art', 'ART', 'Arts'),
							(3, 'Astronomy', 'astronomy', 'ASTR', 'Science'),
							(4, 'Biology', 'biology', 'BIO', 'Science'),
							(5, 'Business', 'business', 'BUSS', 'Arts'),
							(6, 'Chemistry', 'chemistry', 'CHEM', 'Science'),
							(7, 'Chinese', 'chinese', 'CHIN', 'Arts'),
							(8, 'Communications', 'communications', 'COMM', 'Arts'),
							(9, 'Computer Science', 'computer-science', 'COMP', 'Science'),
							(10, 'Economics', 'economics', 'ECON', 'Arts'),
							(11, 'Education', 'education', 'EDUC', 'Arts'),
							(12, 'Electrical Engineering', 'electrical-engineering', 'EENG', 'Science'),
							(13, 'Engineering', 'engineering', 'ENGR', 'Science'),
							(14, 'English', 'english', 'ENGL', 'Arts'),
							(15, 'Entreprenuership', 'entreprenuership', 'ENTR', 'Arts'),
							(16, 'Exercise Science', 'exercise-science', 'ESCI', 'Science'),
							(17, 'French', 'french', 'FREN', 'Arts'),
							(18, 'General Science', 'general-science', 'GSCI', 'Science'),
							(19, 'Geology', 'geology', 'GEOL', 'Science'),
							(20, 'German', 'german', 'GERM', 'Arts'),
							(21, 'Global Studies', 'global-studies', 'GLOB', 'Arts'),
							(22, 'Greek', 'greek', 'GREK', 'Arts'),
							(23, 'Hebrew', 'hebrew', 'HEBR', 'Arts'),
							(24, 'History', 'history', 'HIST', 'Arts'),
							(25, 'Humanities', 'humanities', 'HUMA', 'Arts'),
							(26, 'Japanese', 'japanese', 'JAPN', 'Arts'),
							(27, 'Mathematics', 'mathematics', 'MATH', 'Science'),
							(28, 'Mechanical Engineering', 'mechanical-engineering', 'MECH', 'Science'),
							(29, 'Music', 'music', 'MUSC', 'Arts'),
							(30, 'Philosophy', 'philosophy', 'PHIL', 'Arts'),
							(31, 'Physics', 'physics', 'PHYS', 'Science'),
							(32, 'Political Science', 'political-science', 'POLY', 'Arts'),
							(33, 'Psychology', 'psychology', 'PYCH', 'Arts'),
							(34, 'Religion', 'religion', 'RELI', 'Arts'),
							(35, 'Science Faith & Tech', 'science-faith-tech', 'SSFT', 'Arts'),
							(36, 'Sociology', 'sociology', 'SOCI', 'Arts'),
							(37, 'Spanish', 'spanish', 'SPAN', 'Arts'),
							(38, 'Special Education', 'special-education', 'SEDU', 'Arts'),
							(39, 'Theater', 'theater', 'THEA', 'Arts')");
		}
		
		if (!count($wpdb->get_results("SELECT * FROM `ffi_be_settings`"))) {
			$wpdb->query("INSERT INTO `ffi_be_settings` (`ID`, `BookExpireMonths`, `EmailName`, `EmailAddress`, `TimeZone`) VALUES (1, 6, 'No Reply', 'example@changeme.com', 'America/New_York')");
		}
	}
}
?>