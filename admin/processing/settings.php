<?php
//Include the necessary scripts
	require_once("../../lib/exceptions/Validation_Failed.php");
	require_once("../../lib/processing/Settings_Process.php");
	
//Instantiate the form processor class
	try {
		new FFI\BE\Settings_Process();
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
		exit;
	}
?>