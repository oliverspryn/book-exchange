<?php
//Include the necessary scripts
	require_once("../../../lib/exceptions/Validation_Failed.php");
	require_once("../../../lib/processing/Restore_Process.php");
	require_once("../../../lib/third-party/Indextank/Exception/HttpException.php");
	
//Perform the purchase operation
	try {
		new FFI\BE\Restore_Process();
		echo "success";
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
	} catch (Indextank_Exception_HttpException $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>