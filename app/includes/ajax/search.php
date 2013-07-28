<?php
//Include the necessary scripts
	require_once("../../../includes/APIs/IndexDen.php");
	
//Execute the user's search request
	try {
		echo FFI\BE\IndexDen::search($_GET['q'], $_GET['by'], $_GET['in'], $_GET['sort'], $_GET['page'], $_GET['limit']);
	} catch(FFI\BE\Indexden_Syntax_Error $e) {
		echo "USER_ERROR" . $e->getMessage(); //Prefixed with "USER_ERROR" so the user will be alerted of their mistake
	} catch(FFI\BE\Invalid_Search_Parameter $e) {
		echo "USER_ERROR" . $e->getMessage(); //Prefixed with "USER_ERROR" so the user will be alerted of their mistake
	} catch(FFI\BE\Invalid_Indexden_Results $e) {
		echo "IndexDen error dump: " . $e->getMessage();
	}
?>