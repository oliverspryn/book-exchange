<?php
/**
 * Mandrill Send Email Failed Exception class
 *
 * This is a custom exception class intended to be thrown
 * when a connection to the Mandrill service is successful,
 * but Mandrill fails to send the desired email.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @extends   FFI\BE\Base
 * @license   MIT
 * @namespace FFI\BE
 * @package   lib.exceptions
 * @since     3.0
*/

namespace FFI\BE;

require_once(dirname(__FILE__) . "/Base.php");

final class Mandrill_Send_Failed extends Base {}
?>