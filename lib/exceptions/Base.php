<?php
/**
 * Plugin Base Exception class
 *
 * This is an abstract class which is designed to provide an
 * easy interface for creating higher-level, more specific
 * exception classes as needed. This exception class *can*
 * work without any message passed into its constructor,
 * causing it to automatically generate its own message.
 *
 * @abstract
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   \Exception
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.exceptions
 * @since     3.0.0
*/

namespace FFI\BE;

abstract class Base extends \Exception {
/**
 * CONSTRUCTOR
 *
 * This method will create an exception message using the 
 * message which is given to it, or if one is not provided,
 * it will automatically generate a message to indicate what 
 * kind of exception was thrown.
 *
 * @access public
 * @param  string $message The message to be included with the exception
 * @param  int    $code    A custom error code to accompany the exception
 * @return void
 * @since  3.0.0
*/

	public function __construct($message = NULL, $code = 0) {
	//If no message is given...
		if (is_null($message)) {
			throw new $this("Unknown error encountered while catching " . get_class($this));
		}

	//... otherwise use the message provided by the user
		parent::__construct($message, $code);
	}

/**
 * This method is responsible for generating the content to
 * be returned in the event that a custom exception object
 * is echoed to the display. It will include the following
 * in the returned string:
 *  - The class name.
 *  - The generated message.
 *  - The file which threw the exception.
 *  - The line in the file which threw the exception.
 *  - A trace of the call stack.
 *
 * @access    public
 * @overrides parent::toString()
 * @return    string             A string containing nearly all relevant information about the current exception, see description
 * @since     3.0.0
*/

	public function __toString() {
		return "<strong>Class: </strong>" . get_class($this) . "<br>" . 
			"<strong>Message: </strong>" . $this->getMessage() . "<br>" . 
			"<strong>File: </strong>" . $this->getFile() . "<br>" . 
			"<strong>Line: </strong>" . $this->getLine() . "<br>" . 
			"<strong>Trace string: </strong>" . $this->getTraceAsString();
	}
}
?>