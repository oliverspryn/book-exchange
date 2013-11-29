<?php
//Include the necessary scripts
	require_once("../../../lib/exceptions/Validation_Failed.php");
	require_once("../../../lib/processing/Delete_Process.php");
	
//Perform the purchase operation
	try {
		new FFI\BE\Delete_Process();
		echo "success";
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
	}
?>