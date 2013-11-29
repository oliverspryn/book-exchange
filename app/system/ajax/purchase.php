<?php
//Include the necessary scripts
	require_once("../../../lib/processing/Purchase_Process.php");
	require_once("../../../lib/exceptions/Login_Failed.php");
	require_once("../../../lib/exceptions/Validation_Failed.php");
	require_once("../../../lib/third-party/Indextank/Exception/HttpException.php");
	require_once("../../../lib/exceptions/Network_Connection_Error.php");
	require_once("../../../lib/exceptions/Mandrill_Send_Failed.php");

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