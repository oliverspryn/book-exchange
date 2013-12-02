<?php
//Include the necessary scripts
	require_once("../../lib/APIs/IndexDen.php");
	
	echo json_encode(FFI\BE\IndexDen::reloadIndex());
?>