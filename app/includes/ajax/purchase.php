<?php
//Include the necessary scripts
	require_once("../../../includes/forms/processing/Purchase_Process.php");
	require_once("../../../includes/exceptions/Login_Failed.php");
	require_once("../../../includes/exceptions/Validation_Failed.php");
	require_once("../../../includes/third-party/Indextank/Exception/HttpException.php");
	require_once("../../../includes/exceptions/Network_Connection_Error.php");
	require_once("../../../includes/exceptions/Mandrill_Send_Failed.php");

//Perform the purchase operation
	try {
		new FFI\BE\Purchase_Process();
		echo "success";
	} catch (FFI\BE\Login_Failed $e) {
		echo $e->getMessage();
	} catch (FFI\BE\Validation_Failed $e) {
		echo $e->getMessage();
	} catch (Indextank_Exception_HttpException $e) {
		echo "Error communicating with IndexDen";
	} catch (FFI\BE\Network_Connection_Error $e) {
		echo $e->getMessage();
	} catch (FFI\BE\Mandrill_Send_Failed $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>
