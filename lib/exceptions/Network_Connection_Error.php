<?php
/**
 * Network Connection Error Exception class
 *
 * This is a custom exception class intended to be thrown
 * when the server is attempting to communicate with another
 * server (via cURL) and the connection attempt fails.
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

final class Network_Connection_Error extends Base {}
?>