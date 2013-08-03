<?php
/**
 * Missing Parameter Exception class
 *
 * This is a custom exception class intended to be thrown
 * when the server does not recieve all of the expected
 * parameters which are required for processing.
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

final class Missing_Parameter extends Base {}
?>