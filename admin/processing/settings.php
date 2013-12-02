<?php
//Include the necessary scripts
	require_once("../../lib/exceptions/Login_Failed.php");
	require_once("../../lib/exceptions/Validation_Failed.php");
	require_once("../../lib/processing/Settings_Process.php");
	
//Instantiate the form processor class
	try {
		new FFI\BE\Settings_Process(); 
	} catch (FFI\BE\Login_Failed $e) {
		echo $e->getMessage();
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>