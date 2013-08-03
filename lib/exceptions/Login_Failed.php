<?php
/**
 * User Login Failed Exception class
 *
 * This is a custom exception class intended to be thrown
 * when the application attempts to log an individual in 
 * using a username and password supplied by the user, and
 * their credentials are invalid.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   includes.exceptions
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Base.php");

final class Login_Failed extends Base {}
?>