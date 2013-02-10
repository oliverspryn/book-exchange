<?php
/**
 * Book Exchange interception class
 *
 * This class is used to initialize the Book Exchange plugin. When
 * Wordpress processes each page, this class will listen for any
 * requests structured like this:
 *
 *    http://<wordpress-site>/book-exchange/...
 *
 * and will include the appropraite file from within the 
 * wp-includes/plugins/book-exchange/app folder to replace the 
 * content of the page.
 *
 * Much of this plugin will involve rewriting the content of 404
 * error pages into an application page, assuming the application
 * has a page which matches the URL.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @package   includes
 * @since     v2.0 Dev
*/

class FFI_BE_Interception_Manager {
/**
 * Hold the address current page without the protocol and installation
 * address of Wordpress.
 *
 * @access private
 * @type   string 
*/

	private $requestedURL;
	
/**
 * Hold the address of the actual script which replace the body of the
 * page.
 *
 * @access private
 * @type   string 
*/
	
	private $scriptURL;
	
/**
 * Hold the generated page content until Wordpress is ready to place the
 * content.
 *
 * @access private
 * @type   string 
*/

	private $content;
	
/**
 * CONSTRUCTOR
 *
 * This method will:
 *  - Activate on URLs strucutre like this: 
 *    http://<wordpress-site>/book-exchange/...
 *  - Parse the requested URL into an address which can be used to
 *    fetch correct script from the "app" directory
 *  - Include the "pluggable" function library from Wordpress
 *  - Include requests for the appropriate application files
 *  - Utilize the FFI_BE_Page_Info_Manager class to give the page an
 *    appropriate title and load necessary stylesheets and scripts
 *  - Replace the content of the page with that from the loaded page
 * 
 * @access public
 * @return void
 * @since  v2.0 Dev
*/
	
	public function __construct() {
	//Globalize a few necessary variables
		global $essentials;
		global $wpdb;
		
	//Check if the plugin should be activated
		$this->URLNoRoot();
	
		if ($this->activatePlugin()) {
		//Generate the URL to fetch the appropriate script
			$this->generateURL();
			
		/**
		 * The application will need to perform two types of interceptions:
		 *  - A "regular" one where the contents of the page is replaced
		 *    with the application's contents
		 *  - A 404 version where the entire page is generated, since its
		 *    contents cannot be replaced
		 *
		 * Both are necessary since a link to the Book Exchange will likely
		 * exist on the main navigtaion bar, so users can access it. However,
		 * for links farther inside of the plugin, such as book-exchange/sell,
		 * Wordpress will see this as a request for a page which does not 
		 * exist. Therefore, listening for the 404 action enables us to 
		 * completely replace the contents of the 404 page with the correct
		 * contents of the application, if one exists.
		*/
		
		//We need several methods from this function library
			require_once(ABSPATH . "wp-includes/pluggable.php");
			
		//Run the required script first, so if any modifications should be made to header
			$path = FFI_BE_PATH . "app" . $this->scriptURL;
		
			if (file_exists($path)) {
				ob_start();
				require_once($path);
				$this->content = ob_get_contents();
				ob_end_clean();
			}
			
		//Request application scripts just after the header is called
			add_filter("the_content", array($this, "intercept"));
			add_action("404_template", array($this, "intercept404"));
		}
	}
	
/**
 * Get the URL of the current page without the protocol and installation
 * address of Wordpress.
 *
 * @access private
 * @return void
 * @since  v2.0 Dev
*/
	
	private function URLNoRoot() {
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$request = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		$this->requestedURL = str_ireplace(get_site_url(), "", $request);
	}
	
/**
 * Parse the address to see if the plugin should become active. This
 * method also creates a global constant FFI_BE_ACTIVE, if the method
 * has determined that this URL will display book exchange related 
 * content.
 *
 * @access private
 * @return boolean
 * @since  v2.0 Dev
*/
	
	private function activatePlugin() {
		$URL = parse_url($this->requestedURL);
		
		if (stristr($URL['path'], "book-exchange")) {
			define("FFI_BE_ACTIVE", TRUE);
			return true;
		}
		
		return false;
	}
	
/**
 * Generate the URL to fetch the appropriate script from the requested
 * URL.
 *
 * @access private
 * @return void
 * @since  v2.0 Dev
*/
	
	private function generateURL() {
		$URL =  parse_url(str_ireplace("/book-exchange", "", $this->requestedURL));
		$return = $URL['path'];
		
	/**
	 * Now that we have the path the script, do we need to include index.php, 
	 * if a request was made like this: /book-exchange/
	 *
	 * If the path does not end with ".php" then include "index.php" on the
	 * end to indicate the physical URL of the required script.
	*/
		if (substr($return, -4) != ".php") {
			if (substr($return, -1) == "/") {
				$return .= "index.php";
			} else {
				$return .= "/index.php";
			}
		}
		
		$this->scriptURL = $return;
	}
	
/**
 * Replace the body of the page with the contents of the requested
 * script.
 *
 * @access public
 * @return void
 * @since  v2.0 Dev
*/
	public function intercept() {
		echo $this->content;
	}
	
/**
 * Replace the 404 error page with the appropriate page from the 
 * application.
 *
 * @access public
 * @return void
 * @since  v2.0 Dev
*/
	
	public function intercept404() {		
	//Check to see if the user is really requesting a page that exists
		if (!empty($this->content)) {
			get_header();
			echo $this->content;
			get_footer();
			exit;
		} else {
			//No, really, show a 404
		}
	}
}
?>