<?php
//Include the necessary scripts
	require_once("../../lib/APIs/IndexDen.php");
	require_once("../../lib/third-party/Indextank/Exception/HttpException.php");
	require_once("../../lib/third-party/Indextank/Exception/IndexAlreadyExists.php");
	require_once("../../lib/third-party/Indextank/Exception/TooManyIndexes.php");
	require_once("../../../../../wp-includes/capabilities.php");
	require_once("../../../../../wp-includes/pluggable.php");
	
//Perform the index reload operation
	try {
		if (is_user_logged_in() && current_user_can("update_core")) {
			echo json_encode(FFI\BE\IndexDen::reloadIndex());
		}
	} catch (Indextank_Exception_HttpException $e) {
		echo $e->getMessage();
	} catch (Indextank_Exception_IndexAlreadyExists $e) {
		echo $e->getMessage();
	} catch (Indextank_Exception_TooManyIndexes $e) {
		echo $e->getMessage();
	} catch (Exception $e) {
		echo "Unknown error: " . $e->getMessage();
	}
?>