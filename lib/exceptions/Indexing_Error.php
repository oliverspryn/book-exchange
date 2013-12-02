<?php
/**
 * IndexDen Indexing Error Exception class
 *
 * This is a custom exception class intended to be thrown
 * when a document or batch of documents are sent to the 
 * IndexDen service, and IndexDen cannot index the document(s).
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

final class Indexing_Error extends Base {}
?>