<?php
//Include the necessary scripts
	require_once("../../lib/APIs/IndexDen.php");
	require_once("../../lib/third-party/Indextank/Exception/HttpException.php");
	require_once("../../../../../wp-includes/capabilities.php");
	require_once("../../../../../wp-includes/pluggable.php");
	
//Try to get the size of the index
	try {
		if (is_user_logged_in() && current_user_can("update_core")) {
			echo FFI\BE\IndexDen::getSize();
		}
	} catch (Indextank_Exception_HttpException $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>