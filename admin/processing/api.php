<?php
//Include the necessary scripts
	require_once("../../lib/exceptions/Login_Failed.php");
	require_once("../../lib/processing/API_Process.php");
	
//Instantiate the form processor class
	try {
		new FFI\BE\API_Process();
	} catch (FFI\BE\Login_Failed $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>