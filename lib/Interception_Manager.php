<?php
/**
 * Plugin content interception class
 *
 * This class is used to initialize the current plugin. When
 * Wordpress processes each page, this class will listen for any
 * requests structured like this:
 *
 *    http://<wordpress-site>/<plugin-name>/...
 *
 * and will include the appropraite file from within the 
 * wp-includes/plugins/<plugin-name>/app folder to replace the 
 * content of the page.
 *
 * Much of this plugin will involve rewriting the content of 404
 * error pages into an application page, assuming the application
 * has a page which matches the URL.
 *
 * This class also has the ability to highlight specified link on 
 * the main navigation bar, which can be useful for highlighting 
 * active links for the plugin when Wordpress is unable to. This
 * feature works only with themese designed by ForwardFour
 * Innovations.
 * 
 * This class will also listen for any specially crafted URLs as
 * defined by the paramters given to the addException() method and will
 * use that information to handle special cases where SEO-friendly
 * URLs are requested and must be rewritten to the actual page which
 * will process the request, much like a PHP-version of mod_rewrite.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes
 * @since     3.0
*/

namespace FFI\BE;

class Interception_Manager {
/**
 * Hold the generated page content until Wordpress is ready to place the
 * content.
 *
 * @access private
 * @type   string 
*/

	private $content = "";
	
/**
 * Hold a reference to the URL of the page which should be highlighted
 * on the main navigation bar.
 *
 * @access private
 * @type   string 
*/

	private $navLink = "";
	
/**
 * If a page URL exception is encountered, then store the parsed parameters
 * here.
 *
 * @access private
 * @type   array<string> 
*/

	private $params = array();
	
/**
 * Whether or not this plugin has been determined to be active.
 *
 * @access private
 * @type   boolean 
*/
	
	private $pluginActive = false;
	
/**
 * Hold the address current page without the protocol and installation
 * address of Wordpress.
 *
 * @access private
 * @type   string 
*/

	private $requestedURL = "";
	
/**
 * Hold the address of the actual script which replace the body of the
 * page.
 *
 * @access private
 * @type   string 
*/
	
	private $scriptURL = "";
	
/**
 * CONSTRUCTOR
 *
 * This method will activate this plugin on URLs structure like this:
 * http://<wordpress-site>/<plugin-name>/...
 * 
 * @access public
 * @return void
 * @since  3.0
*/
	
	public function __construct() {
		$this->URLNoRoot();
		$this->activatePlugin();
	}
	
/**
 * This method is like a PHP implementation of mod_rewrite for 
 * Wordpress plugins.
 *
 * Using this function will interrupt the normal fetch-execute cycle
 * this class goes through when intercepting a page. If a special case
 * of a URL is encountered, this method will force this class to heed
 * an exception (not to be confused with throwing an exception) for that
 * URL which is defined by the parameters of this method. If said page 
 * is visited without being registered as an exception, a 404 error is
 * likely to occur.
 *
 * Here is how this method is can be called and what goes on. Let us 
 * assume that visiting a page like this: http://<wordpress-site>/
 * <plugin-name>/<page>/ would cause this class to bring the user to the
 * desired page without having to register an exception. On the contrary,
 * assume this page: http://<wordpress-site>/<plugin-name>/<page>/options/
 * is an SEO-friendly URL which would normally be handled by mod_rewrite
 * to direct the request to the appropriate page for processing. A URL
 * such as the previous one would be a case where using registerException()
 * would be necessary.
 *
 * This method takes an arbirtary number of parameters:
 *  - parameter 1: the string the URL should START with in order to 
 *                 activate the exception, with respect to the "app" 
 *                 folder. (i.e. the URL without "http://<wordpress-site>/
 *                 <plugin-name>/", note the trailing "/")
 *  - parameter 2: the URL of the file this class should request when 
 *                 this exception is encountered, with respect to the 
 *                 "app" folder
 *  - parameter n: this method will explode the URL from the name of the 
 *                 plugin to the end of the URL into an array using the
 *                 "/" as the delimiter. Using the earlier example
 *                 http://<wordpress-site>/<plugin-name>/<page>/options/
 *                 would be split into: Array([0] => <plugin-name>, [1] =>
 *                 <page>, [2] => options). Thus, each of these n parameters
 *                 would correspond to the index elements of this array for 
 *                 which this method should store for later use. For example
 *                 passing parameters 1 and 2 would store "<page>" and 
 *                 "options", respectively, for later use.
 *
 * The extracted array elements are then again stored as an array and passed
 * to the Essentials class. The page which is fetched from parameter 2 will
 * then be able to access the extracted information using $essentials->params.
 * For example, using the case presented in the comments for parameter n,
 * $essentials->params[0] would fetch "<page>" and $essentials->params[1] would
 * fetch "options".
 * 
 * @access public
 * @param  string   $activateURL The string the URL should START with in order to activate the exception, with respect to the "app" folder
 * @param  string   $redirectURL The URL of the file this class should request when this exception is encountered, with respect to the "app" folder
 * @param  int      ...$indexes  The index elements of the array holding the exploded page URL for which this method should store for later use
 * @return void
 * @since  3.0
*/
	
	public function registerException() {
		$params = func_get_args();
		
	//Run this function if the plugin and excpetion has been determined to be active
		if ($this->pluginActive && $this->activateException($params[0])) {
			$replace = $params[1]; //The URL of the page this exception should actually fetch
			$URL = array_filter(explode("/", ltrim($this->requestedURL, "/")));
			$URLIndexes = count($URL) - 1;
			
		//Go through each of the n parameters
			for($i = 2; $i < count($params); ++$i) {
				if ($params[$i] <= $URLIndexes) {
					array_push($this->params, $URL[$params[$i]]);
				} else {
					return;
				}
			}
			
		//Share the URL this exception case should fetch with the rest of the class
			$this->scriptURL = $replace;
		}
	}
	
/**
 * This method will:
 *  - Parse the requested URL into an address which can be used to
 *    fetch correct script from the "app" directory
 *  - Include the "pluggable" function library from Wordpress
 *  - Register the FFI\BE\ACTIVE and FFI\PLUGIN_PAGE constants
 *    indicating that this specific plugin is active on the current
 *    page and that a more general ForwardFour Innovations plugin
 *    is active on the current page, respectively. The latter is 
 *    useful in cases where ForwardFour Innovations themes may need
 *    to adjust page rendering in the case that the current page 
 *    is displaying content from an active plugin.
 *  - Include requests for the appropriate application files
 *  - Utilize the Essentials class to give the page an appropriate 
 *    title and load necessary stylesheets and scripts
 *  - Include the run the global plugin file
 *  - Replace the content of the page with that from the loaded page
 * 
 * @access public
 * @return void
 * @since  3.0
*/
	
	public function go() {
		global $essentials;
		global $wpdb;
		
		if ($this->pluginActive) {
		//Generate the URL to fetch the appropriate script
			$this->generateURL();
			
		/**
		 * The application will need to perform two types of interceptions:
		 *  - A "regular" one where the contents of the page is replaced
		 *    with the application's contents
		 *  - A 404 version where the entire page is generated, since its
		 *    contents cannot be replaced
		 *
		 * Both are necessary since a link to this plugin's main page will likely
		 * exist on the main navigtaion bar, so users can access it. However,
		 * for links farther inside of the plugin, such as <plugin-name>/subpage,
		 * Wordpress will see this as a request for a page which does not 
		 * exist. Therefore, listening for the 404 action enables us to 
		 * completely replace the contents of the 404 page with the correct
		 * contents of the application, if one exists.
		*/
		
		//We need several methods from this function library
			require_once(ABSPATH . "wp-includes/pluggable.php");
			
		//Register several constants indicating the activated state of this plugin
			define("FFI\BE\ACTIVE", true);
			define("FFI\PLUGIN_PAGE", true);
			
		//Run the required script first, so the included file can make any modifications to the header
			$path = PATH . "app" . $this->scriptURL;
			
			if (file_exists($path)) {
			//Plugin essentials
				require_once(PATH . "/includes/Essentials.php");
				$construct = count($this->params) ? $this->params : false;
				$essentials = new Essentials($construct);
				
			//Run the global plugin file
				require_once(PATH . "/global.php");
				
			//Generate the content of the page
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
 * This method is intended to highlight the given URL on the main
 * navigation bar. The URL should be given with respect to the "app"
 * folder of this plugin. So, a parameter such as "my-plugin", will
 * highlight the menu item with the URL of:
 * http://<wordpress-site>/my-plugin
 *
 * NOTE: This feature will ONLY work with Wordpress themes designed by 
 * ForwardFour Innovations
 *
 * @access public
 * @param  string   $address The URL with respect to the "app" folder
 * @since  3.0
*/
	
	public function highlightNavLink($address) {
		$this->navLink = get_site_url() . "/" . $address;
	}
	
/**
 * Get the URL of the current page without the protocol and installation
 * address of Wordpress.
 *
 * @access private
 * @return void
 * @since  3.0
*/
	
	private function URLNoRoot() {
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$request = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		
		$this->requestedURL = str_ireplace(get_site_url(), "", $request);
	}
	
/**
 * Parse the address to see if the plugin should become active
 *
 * @access private
 * @return void
 * @since  3.0
*/
	
	private function activatePlugin() {
		$URL = parse_url($this->requestedURL);
		$this->pluginActive = !strncasecmp($URL['path'], "/" . URL_ACTIVATE, strlen("/" . URL_ACTIVATE));
	}
	
/**
 * Parse the address to see if a given exception case should become
 * active
 *
 * @access private
 * @param  string   $exception The string the URL should START with in order to activate the exception, with respect to the "app" folder
 * @return boolean             Whether or not the requested exception should be activated
 * @since  3.0
*/
	
	private function activateException($exception) {
		$URL = parse_url($this->requestedURL);
		$exception = "/" . URL_ACTIVATE . "/" . $exception;
		
		return !strncasecmp($URL['path'], $exception, strlen($exception));
	}
	
/**
 * Generate the URL to fetch the appropriate script from the requested
 * URL.
 *
 * @access private
 * @return void
 * @since  3.0
*/
	
	private function generateURL() {
	//If an exception has already generated the URL, then there is little left to do
		if ($this->scriptURL != "") {
			$this->scriptURL = "/" . $this->scriptURL;
			return;
		}
		
		$URL =  parse_url(str_ireplace("/" . URL_ACTIVATE, "", $this->requestedURL));
		$return = $URL['path'];
		
	/**
	 * Now that we have the path the script, do we need to include index.php, 
	 * if a request was made like this: /<plugin-name>/
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
 * @since  3.0
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
 * @since  3.0
*/
	
	public function intercept404() {
		global $wp_query;
				
	//Check to see if the user is really requesting a page that exists
		if (!empty($this->content)) {
		//Override the 404 header sent by Wordpress
			status_header(200);
			$wp_query->is_404 = false;
			
		//Make the URL of the highlighted navigation link avaliable to the theme's header file
			$GLOBALS['highlight'] = $this->navLink;
			
		//Build the page content
			get_header();
			unset($GLOBALS['highlight']); //Well... that was evil, DESTROY IT!
			echo $this->content;
			get_footer();
			exit;
		} else {
			//No, really, show a 404
		}
	}
}
?>