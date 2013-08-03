<?php
/**
 * Invalid Search Parameter Exception class
 *
 * This is a custom exception class intended to be thrown
 * when the user attempts to perform a search with invalid
 * search parameters, such as with an empty query or with an 
 * invalid sorting criteria.
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

final class Invalid_Search_Parameter extends Base {}
?>