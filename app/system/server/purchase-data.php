<?php
//Include the system's core
	require_once("../../../Connections/connDBA.php");
	require_once("../../../Connections/jsonwrapper/jsonwrapper.php");
	
//Is the user logged in?
	if (!loggedIn()) {
		echo "logged out";
	}
	
//Fetch all relevant data from the database
	if (isset($_GET['id']) && !empty($_GET['id']) && is_numeric($_GET['id'])) {
		$now = strtotime("now");
		$datagrabber = mysql_query("SELECT exchangesettings.expires, books.ISBN, books.title, books.author, books.edition, GROUP_CONCAT(bookcategories.name) AS name, GROUP_CONCAT(books.number) AS number, GROUP_CONCAT(books.section) AS section, GROUP_CONCAT(bookcategories.id) AS classID, books.price, books.condition, books.written, books.comments, books.imageURL, users.firstName, users.lastName, users.emailAddress1, users.emailAddress2, users.emailAddress3 FROM `books` RIGHT JOIN (bookcategories) ON books.course = bookcategories.id RIGHT JOIN (users) ON books.userID = users.id RIGHT JOIN(exchangesettings) ON books.id WHERE books.linkID = (SELECT linkID FROM books WHERE id = '{$_GET['id']}' AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now} LIMIT 1)", $connDBA);
		
		if ($datagrabber && mysql_num_rows($datagrabber)) {
			$data = mysql_fetch_assoc($datagrabber);
			
			$overview = array();
			$overview['ISBN'] = stripslashes($data['ISBN']);
			$overview['title'] = stripslashes($data['title']);
			$overview['author'] = stripslashes($data['author']);
			$overview['edition'] = stripslashes($data['edition']);
			$overview['name'] = stripslashes($data['name']);
			$overview['number'] = stripslashes($data['number']);
			$overview['section'] = stripslashes($data['section']);
			$overview['classID'] = stripslashes($data['classID']);
			$overview['price'] = stripslashes($data['price']);
			$overview['condition'] = stripslashes($data['condition']);
			$overview['written'] = stripslashes($data['written']);
			$overview['comments'] = stripslashes($data['comments']);
			$overview['imageURL'] = stripslashes($data['imageURL']);
			$overview['firstName'] = stripslashes($data['firstName']);
			$overview['lastName'] = stripslashes($data['lastName']);
			$overview['emailAddress1'] = stripslashes($data['emailAddress1']);
			$overview['emailAddress2'] = stripslashes($data['emailAddress2']);
			$overview['emailAddress3'] = stripslashes($data['emailAddress3']);			
						
			echo json_encode($overview);
		} else {
			echo "failed";
		}
	} else {
		echo "failed";
	}
?>