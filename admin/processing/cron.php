<?php
//Include the necessary scripts
	require_once("../../lib/APIs/IndexDen.php");
	require_once("../../lib/third-party/Indextank/Exception/HttpException.php");
	
//Perform the expired books purge operation
	try {
		FFI\BE\IndexDen::purgeExpired();
		echo "success";
	} catch (Indextank_Exception_HttpException $e) {
		echo $e->getMessage();
	}
?>