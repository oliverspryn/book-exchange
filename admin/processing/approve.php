<?php
//Include the necessary scripts
	require_once("../../lib/exceptions/Login_Failed.php");
	require_once("../../lib/exceptions/Validation_Failed.php");
	require_once("../../lib/processing/Approve_Process.php");
	
//Instantiate the form processor class
	try {
		new FFI\BE\Approve_Process();
		echo "success";
	} catch (FFI\BE\Login_Failed $e) {
		echo $e->getMessage();
	} catch (Validation_Failed $e) {
		echo $e->getMessage();
	} catch (InvalidArgumentException $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo $e->getMessage();
	}
?>