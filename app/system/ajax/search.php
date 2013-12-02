<?php
//Include the necessary scripts
	require_once("../../../lib/APIs/IndexDen.php");
	require_once("../../../lib/exceptions/Validation_Failed.php");
	require_once("../../../lib/third-party/indextank.php");
	require_once("../../../lib/third-party/Indextank/Exception/HttpException.php");
	
//Execute the user's search request
	try {
		echo FFI\BE\IndexDen::search($_GET['q'], $_GET['by'], $_GET['in'], $_GET['sort'], $_GET['page'], $_GET['limit']);
	} catch (Indextank_Exception_HttpException $e) {
		echo $e->getMessage();
	} catch (FFI\BE\Validation_Failed $e) {
		echo "USER_ERROR" . $e->getMessage(); //Prefixed with "USER_ERROR" so the user will be alerted of their mistake
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>