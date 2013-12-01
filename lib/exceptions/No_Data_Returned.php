<?php
/**
 * Database No Data Returned Exception class
 *
 * This is a custom exception class intended to be thrown
 * when an SQL query returns 0 tuples.
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

final class No_Data_Returned extends Base {}
?>