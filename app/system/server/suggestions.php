<?php
//Include the system's core
	require_once("../../../Connections/connDBA.php");
	require_once("../../../Connections/jsonwrapper/jsonwrapper.php");
	require_once("Validate.php");
	
//Perform a search operation on the database
	if (isset($_GET['term']) && $_GET['term'] != "") {
		$query = mysql_real_escape_string(urldecode($_GET['term']));
		$category = mysql_real_escape_string(urldecode(Validate::numeric($_GET['category'])));
		$searchBy = mysql_real_escape_string(urldecode(Validate::required($_GET['searchBy'], array("title", "author", "ISBN", "course", "seller"))));
		
	//Search by a specific category
		if ($category != 0) {
			$category = " AND books.course = '" . $category . "'";
		} else {
			$category = "";
		}		
		
	//Different search methods will vary the query that is executed on the database
		$now = strtotime("now");
		
		switch($searchBy) {
			case "title" : 
				$searchGrabber = mysql_query("SELECT books.*, exchangesettings.expires, MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT books.linkID) AS total, MIN(books.price) AS price FROM books RIGHT JOIN(exchangesettings) ON books.id WHERE MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now}{$category} GROUP BY title ORDER BY score DESC, books.title ASC LIMIT 5", $connDBA);
				break;
				
			case "author" : 
				$searchGrabber = mysql_query("SELECT books.*, exchangesettings.expires, MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT books.linkID) AS total, MIN(books.price) AS price FROM books RIGHT JOIN(exchangesettings) ON books.id WHERE MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now}{$category} GROUP BY author ORDER BY score DESC, books.title ASC LIMIT 5", $connDBA);
				break;
				
			case "ISBN" : 
				$searchGrabber = mysql_query("SELECT books.*, exchangesettings.expires, COUNT(DISTINCT books.linkID) AS total, MIN(books.price) AS price FROM books RIGHT JOIN(exchangesettings) ON books.id WHERE ISBN = '{$query}' AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now}{$category} GROUP BY ISBN ORDER BY books.title ASC LIMIT 5", $connDBA);
				break;
				
			case "course" : 
				$number = substr($query, strlen($query) - 5, strlen($query) - 2);
				$section = substr($query, strlen($query) - 1, strlen($query));
				$searchGrabber = mysql_query("SELECT books.*, bookcategories.name, exchangesettings.expires, COUNT(DISTINCT books.linkID) AS total, MIN(books.price) AS price FROM books RIGHT JOIN (bookcategories) ON books.course = bookcategories.id RIGHT JOIN(exchangesettings) ON books.id WHERE number = '{$number}' AND section = '{$section}' AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now}{$category} GROUP BY books.course, books.number, books.section ORDER BY books.title ASC LIMIT 5", $connDBA);
				break;
				
			case "seller" : 
				$searchGrabber = mysql_query("SELECT books.*, users.*, exchangesettings.expires, MATCH(firstName, lastName) AGAINST('{$query}' IN BOOLEAN MODE) AS score, COUNT(DISTINCT books.linkID) AS total, MIN(books.price) AS price FROM users RIGHT JOIN(books) ON users.id = books.userID RIGHT JOIN(exchangesettings) ON books.id WHERE MATCH(firstName, lastName) AGAINST('{$query}' IN BOOLEAN MODE) AND books.sold = '0' AND books.userID != '0' AND books.upload + exchangesettings.expires > {$now}{$category} GROUP BY users.firstName, users.lastName ORDER BY score DESC, books.title ASC LIMIT 5", $connDBA);
				break;
				
			default : 
				redirect("../../search/");
				break;
		}
	} else {
		redirect("../");
	}

//Display the results of the search
	$output = array();
	
	while ($search = mysql_fetch_array($searchGrabber)) {
		switch($searchBy) {
			case "title" : 
				array_push($output, array("label" => stripslashes($search['title']), "byLine" => "Author: " . stripslashes($search['author']), "image" => stripslashes($search['imageURL']), "total" => stripslashes($search['total']), "price" => stripslashes($search['price'])));
				break;
				
			case "author" : 
				array_push($output, array("label" => stripslashes($search['author']), "byLine" => "", "image" => $root . "book-exchange/system/images/icons/author.png", "total" => stripslashes($search['total']), "price" => stripslashes($search['price'])));
				break;
				
			case "ISBN" : 
				array_push($output, array("label" => stripslashes($search['ISBN']), "byLine" => "Title: " . stripslashes($search['title']), "image" => stripslashes($search['imageURL']), "total" => stripslashes($search['total']), "price" => stripslashes($search['price'])));
				break;
				
			case "course" : 
				array_push($output, array("ID" => stripslashes($search['course']), "label" => stripslashes($search['number']) . " " . stripslashes($search['section']), "byLine" => "", "image" => $root . "data/book-exchange/icons/" . $search['course'] . "/icon_032.png", "total" => stripslashes($search['total']), "price" => stripslashes($search['price'])));
				break;
				
			case "seller" : 
				array_push($output, array("label" => stripslashes($search['firstName']) . " " . stripslashes($search['lastName']), "byLine" => "", "image" => $root . "book-exchange/system/images/icons/seller.png", "total" => stripslashes($search['total']), "price" => stripslashes($search['price'])));
				break;
		}
			
	}
	
	echo json_encode($output);
	exit;
?>