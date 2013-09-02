<?php
//Include the necessary scripts
	require_once("../../../lib/exceptions/Validation_Failed.php");
	require_once("../../../lib/processing/Restore_Process.php");
	
//Perform the purchase operation
	try {
		new FFI\BE\Restore_Process();
		echo "success";
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
	}
?>