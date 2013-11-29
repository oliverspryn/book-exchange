<?php
//Include the necessary scripts
	require_once("../../../lib/display/Book.php");
	require_once("../../../lib/exceptions/Network_Connection_Error.php");
	require_once("../../../lib/exceptions/No_Data_Returned.php");
	require_once("../../../lib/exceptions/Validation_Failed.php");

//Provide the user with a listing of suggestions
	try {
		echo FFI\BE\Book::getBookByISBN($_GET['ISBN']);
 	} catch (FFI\BE\No_Data_Returned $e) {
		try {
			echo FFI\BE\Book::suggestCovers($_GET['ISBN']);
		} catch (FFI\BE\Network_Connection_Error $e) {
			echo $e->getMessage();
		} catch (FFI\BE\Validation_Failed $e) {
			echo "USER_ERROR" . $e->getMessage();
		}
	} catch (FFI\BE\Validation_Failed $e) {
		echo "USER_ERROR" . $e->getMessage();
	}
?>
