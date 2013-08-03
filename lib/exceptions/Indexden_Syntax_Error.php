<?php
/**
 * IndexDen Syntax Error Exception class
 *
 * This is a custom exception class intended to be thrown
 * when a user enters an IndexDen search query which contains
 * invalid syntax, and causes the service to return an error.
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

final class Indexden_Syntax_Error extends Base {}
?>
