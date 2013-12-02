<?php
/**
 * Validation Failed Exception class
 *
 * This is a custom exception class intended to be thrown
 * when validating incoming form data for processing is 
 * invalid.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.exceptions
 * @since     3.0.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Base.php");

final class Validation_Failed extends Base {}
?>