<?php
/**
 * Invalid IndexDen Results Exception class
 *
 * This is a custom exception class intended to be thrown
 * when queries executed on the IndexDen indexing service
 * does not return a collection of search results, but rather
 * includes an error message due to a malformed query or a
 * processing error.
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

final class Invalid_Indexden_Results extends Base {}
?>
