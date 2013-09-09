<?php
//Include the necessary scripts
	require_once("../lib/APIs/IndexDen.php");
	
	echo var_dump(FFI\BE\IndexDen::reloadIndex());
?>