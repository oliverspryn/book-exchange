<?php
/**
 * Proxy class
 *
 * This class is designed to serve as a generic wrapper for the
 * cURL library. At its most basic functionality, it can serve as
 * a proxy to go and fetch the contents of URL, but it can also 
 * leverage all of the functionality provided by the cURL library
 * such as sending POST data, adjusting the header MIME type, etc...
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.processing
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(dirname(__FILE__)) . "/exceptions/Network_Connection_Error.php");

class Proxy {
/**
 * Hold the MIME type of the data being transferred.
 *
 * @access public
 * @type   string
*/

	public $contentType = "";
	
/**
 * Hold an array of additional cURL options
 *
 * @access public
 * @type   array<string>
*/
	
	private $options = array();
	
/**
 * Set whether or not this request is sending POST data.
 *
 * @access public
 * @type   string
*/
	
	public $POST = false;
	
/**
 * If $POST is set to true, then hold a string of POST data.
 *
 * @access public
 * @type   string
*/
	
	public $POSTData = "";
	
/**
 * Hold the request URL.
 *
 * @access private
 * @type   string
*/
	
	private $URL;
	
/**
 * Hold an array of additional cURL values.
 *
 * @access public
 * @type   string
*/
	
	private $values = array();
	
/**
 * CONSTRUCTOR
 *
 * This method will set the URL of the request which will later be 
 * sent.
 * 
 * @access public
 * @return void
 * @since  3.0.0
*/
	
	public function __construct($URL) {
		$this->URL = $URL;
	}
	
/**
 * Add an additional option to the cURL request.
 * 
 * @access public
 * @param  int    $option A constant which resembles the cURL option to configure <http://php.net/manual/en/function.curl-setopt.php>
 * @param  mixed  $value  The value to be paired with the option
 * @return void
 * @since  3.0.0
*/
	
	public function addCURLOption($option, $value) {
		array_push($this->options, $option);
		array_push($this->values, $value);
	}
	
/**
 * Send the request to the desired URL. Before calling this function,
 * the request MIME type can be set with the $contentType variable, 
 * POST data can be sent by setting $POST to true, and $POSTData to the
 * POST data of interest.
 * 
 * @access public
 * @return string The results returned from the specified URL
 * @since  3.0.0
*/
	
	public function fetch() {
	//Open a cURL session for making the call
		$curl = curl_init($this->URL);

		curl_setopt($curl, CURLOPT_HEADER, false);
		
	//Set the sending MIME type
		if ($this->contentType != "") {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: " . $this->contentType));
		}
		
	//Set the POST data
		if ($this->POST) {
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->POSTData);
		}
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
	//Add any additional user-defined options
		if (count($this->options)) {
			for ($i = 0; $i < count($this->options); ++$i) {
				curl_setopt($curl, $this->options[$i], $this->values[$i]);
			}
		}

		$response = curl_exec($curl);
		$errorNumber = curl_errno($curl);
		$error = curl_error($curl);
		curl_close($curl);
		
	//Check for any network errors
		if ($errorNumber) {
			throw new Network_Connection_Error("A network connection to " . $this->URL . " has failed. cURL error details: " . $error);
		}
		
		return $response;
	}
}
?>