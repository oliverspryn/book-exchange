<?php
//Include the system's core
	require_once("../../Connections/connDBA.php");
	require_once("../system/server/Validate.php");
	
//Verify that the user is logged in
	if (!loggedIn()) {
		redirect("../../login.php?accesscheck=" . $_SERVER['REQUEST_URI'] . "&message=required");
	}
	
//Update a user's profile
	if (isset($_POST['action']) && $_POST['action'] == "profile") {
		$id = $userData['id'];
		$first = mysql_real_escape_string(Validate::required($_POST['first']));
		$last = mysql_real_escape_string(Validate::required($_POST['last']));
		$emailAddress1 = mysql_real_escape_string(Validate::email($_POST['emailAddress1']));
		$emailAddress2 = mysql_real_escape_string(Validate::email($_POST['emailAddress2'], true));
		$emailAddress3 = mysql_real_escape_string(Validate::email($_POST['emailAddress3'], true));
		
	//Check the password, did we get one?
		if ($_POST['password'] != "") {
			$hash = "+y4hn&T/'K";
			$password = md5($_POST['password'] . "_" . $hash);
			
			mysql_query("UPDATE users SET firstName = '{$first}', lastName = '{$last}', emailAddress1 = '{$emailAddress1}', emailAddress2 = '{$emailAddress2}', emailAddress3 = '{$emailAddress3}', passWord = PASSWORD('{$password}')", $connDBA);
		} else {
			mysql_query("UPDATE users SET firstName = '{$first}', lastName = '{$last}', emailAddress1 = '{$emailAddress1}', emailAddress2 = '{$emailAddress2}', emailAddress3 = '{$emailAddress3}'", $connDBA);
		}
		
		echo "success";
		exit;
	}
	
//Renew a book
	if (isset($_GET['action']) && $_GET['action'] == "renew" && isset($_GET['id'])) {
		if ($userData['role'] == "Administrator") {
			$booksGrabber = mysql_query("SELECT linkID FROM books WHERE id = '{$_GET['id']}' AND books.id IS NOT NULL", $connDBA);
		} else {
			$booksGrabber = mysql_query("SELECT linkID FROM books WHERE books.userID = '{$userData['id']}' AND id = '{$_GET['id']}' AND books.id IS NOT NULL", $connDBA);
		}
		
		if (mysql_num_rows($booksGrabber)) {
			$booksLink = mysql_fetch_assoc($booksGrabber);
			$now = strtotime("now");
			
			mysql_query("UPDATE books SET upload = '{$now}', sold = '0' WHERE linkID = '{$booksLink['linkID']}'", $connDBA);
			mysql_query("DELETE FROM purchases WHERE bookID = '{$_GET['id']}'", $connDBA); //Delete this from the purchase list. Guess the seller didn't sell it to anyone
			redirect("../account/?message=renewed#book_" . $_GET['id']);
		} else {
			redirect("../account/");
		}
	}
	
//Delete a book
	if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['id'])) {
		$booksGrabber = mysql_query("SELECT linkID FROM books WHERE books.userID = '{$userData['id']}' AND id = '{$_GET['id']}' AND books.id IS NOT NULL", $connDBA);
		
		if (mysql_num_rows($booksGrabber)) {
			$booksLink = mysql_fetch_assoc($booksGrabber);
			
		//We set the userID equal to 0, so it is not associated with any user, but
		//also isn't deleted from a buyer's purchase history
			mysql_query("UPDATE books SET userID = '0' WHERE linkID = '{$booksLink['linkID']}'", $connDBA);
			redirect("../account/?message=deleted");
		} else {
			redirect("../account/");
		}
	}
	
//Generate the breadcrumb
	$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	$title = unserialize($home['content' . $home['display']]);
	$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
<li><a href=\"../\">Book Exchange</a></li>
<li>My Account</li>\n";
	
//Include the top of the page from the administration template
	topPage("public", "All Book Listings", "" , "", "<link href=\"../system/stylesheets/style.css\" rel=\"stylesheet\" />
<link href=\"../system/stylesheets/account.css\" rel=\"stylesheet\" />
<link href=\"../../styles/jQuery/validationEngine.jquery.min.css\" rel=\"stylesheet\" />
<script src=\"../../javascripts/jQuery/jquery.validationEngine.min.js\"></script>
<script src=\"../../javascripts/common/md5.min.js\"></script>
<script src=\"../system/javascripts/interface.js\"></script>", $breadcrumb);
	echo "<section class=\"body\">
";
	
//Display any messages
	if (isset($_GET['message'])) {
		switch ($_GET['message']) {
			case "added" : 
				if (isset($_GET['approval'])) {
					$extra = ". The book's cover must be approved before it will display.";
				} else {
					$extra = "";
				}
				
				echo "<div class=\"center\"><div class=\"success\">Your book is now up for sale" . $extra . "</div></div>
				
";
				break;
				
			case "edited" : 
				echo "<div class=\"center\"><div class=\"success\">Your book has been edited</div></div>
				
";
				break;
				
			case "renewed" : 
				echo "<div class=\"center\"><div class=\"success\">Your book has been restored to the exchange</div></div>
				
";
				break;
				
			case "deleted" : 
				echo "<div class=\"center\"><div class=\"success\">Your book has been deleted</div></div>
				
";
				break;
		}
	}

//Display the user account information
	echo "<section class=\"profile\">
<h2>My Profile</h2>
<span class=\"row\">
<strong>Name:</strong>
<span class=\"firstName\">" . stripslashes($userData['firstName']) . "</span> <span class=\"lastName\">" . stripslashes($userData['lastName']) . "</span>
</span>

<span class=\"row\">
<strong>Email:</strong>
<a class=\"emailAddress1\" href=\"mailto:" . htmlentities(stripslashes($userData['emailAddress1'])) .  "\">" . stripslashes($userData['emailAddress1']) . "</a>
</span>

";
	
	if ($userData['emailAddress2'] != "") {
		echo "<span class=\"row\">
<strong>Alternate email:</strong>
<a class=\"emailAddress2\" href=\"mailto:" . htmlentities(stripslashes($userData['emailAddress2'])) .  "\">" . stripslashes($userData['emailAddress2']) . "</a>
</span>

";
	} else {
		echo "<span class=\"row\">
<strong>Alternate email:</strong>
<a class=\"none emailAddress2\">None given</a>
</span>

";
	}
	
	if ($userData['emailAddress3'] != "") {
		echo "<span class=\"row\">
<strong>Alternate email:</strong>
<a class=\"emailAddress3\" href=\"mailto:" . htmlentities(stripslashes($userData['emailAddress3'])) .  "\">" . stripslashes($userData['emailAddress3']) . "</a>
</span>

";
	} else {
		echo "<span class=\"row\">
<strong>Alternate email:</strong>
<a class=\"none emailAddress3\">None given</a>
</span>

";
	}
	
	echo "<span class=\"row\">
<strong>Password:</strong>
<span>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
</span>

<button class=\"blue updateProfile\">Update Profile</button>
</section>

";
	
//Include a list of books that the user has listed for sale
	$booksGrabber = mysql_query("SELECT books.*, bookcategories.*, books.id AS bookID, books.course AS courseID FROM books RIGHT JOIN (bookcategories) ON books.course = bookcategories.id WHERE books.userID = '{$userData['id']}' AND books.id IS NOT NULL GROUP BY books.linkID ORDER BY books.upload DESC", $connDBA);
	$exchangeSettings = mysql_fetch_assoc(mysql_query("SELECT * FROM exchangesettings WHERE id = '1'", $connDBA));
	
	echo "<section class=\"books\">
<h2>My Books</h2>
";
	
	if (mysql_num_rows($booksGrabber)) {
		echo "<ul>";
		
		$now = strtotime("now");
		$week = strtotime("+1 week");
		
		while ($book = mysql_fetch_assoc($booksGrabber)) {
		//Has this book expired, been sold, or will it expire within the next week?
			if ($book['sold'] == "0" && ($book['upload'] + $exchangeSettings['expires']) < $now) {
				$class = " expired";
				$expireRenew = "<a class=\"action renew\" href=\"../account/?action=renew&id=" . $book['bookID'] . "\" title=\"Restore to the Exchange\"><img src=\"../system/images/icons/renew.png\" /></a>
";
				$expire = "<span class=\"expire\">Expired: " . date("F jS, Y \a\\t h:i A", ($book['upload'] + $exchangeSettings['expires'])) . "</span>
";
				$status = "<span class=\"expired\">Expired</span>
";
			} elseif ($book['sold'] == "0" && ($book['upload'] + $exchangeSettings['expires']) < ($week) && ($book['upload'] + $exchangeSettings['expires']) > ($now)) {
				$class = " soon";
				$expireRenew = "<a class=\"action renew\" href=\"../account/?action=renew&id=" . $book['bookID'] . "\" title=\"Restore to the Exchange\"><img src=\"../system/images/icons/renew.png\" /></a>
";
				$expire = "<span class=\"expire\">Expires: " . date("F jS, Y \a\\t h:i A", ($book['upload'] + $exchangeSettings['expires'])) . "</span>
";
				$status = "<span class=\"expiring\">Expiring Soon, Click the Renew Button to Prevent Expiration</span>
";
			} elseif ($book['sold'] == "1") {
				$class = " sold";
				$expireRenew = "<a class=\"action renew\" href=\"../account/?action=renew&id=" . $book['bookID'] . "\" title=\"Restore to the Exchange\"><img src=\"../system/images/icons/renew.png\" /></a>
";
				$expire = "";
				$status = "<span class=\"sold\">Sold</span>
";
			} else {
				$class = "";
				$expireRenew = "";
				$expire = "<span class=\"expire\">Expires: " . date("F jS, Y \a\\t h:i A", ($book['upload'] + $exchangeSettings['expires'])) . "</span>
";
				$status = "";
			}
			
			echo "
<li class=\"book\">
<div class=\"alert" . $class . "\">
<a name=\"book_" . $book['bookID'] . "\"></a>
<a href=\"../book/?id=" . $book['bookID'] . "\"><img class=\"cover\" src=\"" . htmlentities(stripslashes($book['imageURL'])) . "\" /></a>
<a class=\"title\" href=\"../book/?id=" . $book['bookID'] . "\">" . stripslashes($book['title']) . "</a>
<span class=\"details\"><strong>Author:</strong> <a href=\"../search/?search=" . urlencode(stripslashes($book['author'])) . "&searchBy=author&category=0\">" . stripslashes($book['author']) . "</a></span>
<span class=\"details\"><strong>ISBN:</strong> <a href=\"../search/?search=" . urlencode(stripslashes($book['ISBN'])) . "&searchBy=ISBN&category=0\">" . stripslashes($book['ISBN']) . "</a></span>
" . $expireRenew . "<a class=\"action edit\" href=\"../sell-books/?id=" . $book['bookID'] . "\" title=\"Edit this Book\"><img src=\"../system/images/icons/edit.png\" /></a>
<a class=\"action deleteBook\" data-id=\"" . $book['bookID'] . "\" href=\"javascript:;\" title=\"Delete this Book\"><img src=\"../system/images/icons/delete.png\" /></a>
" . $expire . $status . "</div>
</li>
";
		}
		
		echo "</ul>";
	} else {
		echo "
<div class=\"none\">You don't have any books for sale yet! <a class=\"highlight\" href=\"../sell-books/\">Sell some now</a>.</div>";
	}
	
	echo "
</section>";
	
//Include a list of books that the user has purchased
	$purchasedGrabber = mysql_query("SELECT books.*, purchases.*, users.*, books.id AS bookID FROM books RIGHT JOIN (purchases) ON books.id = purchases.bookID RIGHT JOIN (users) ON purchases.sellerID = users.id WHERE purchases.buyerID = '{$userData['id']}' AND books.id IS NOT NULL GROUP BY books.linkID ORDER BY purchases.time DESC", $connDBA);
	
	
	echo "<section class=\"purchases\">
<h2>My Purchases</h2>
";
	
	if (mysql_num_rows($purchasedGrabber)) {
		echo "<ul>";
		
		while ($purchase = mysql_fetch_assoc($purchasedGrabber)) {
			echo "
<li class=\"book\">
<img src=\"" . htmlentities(stripslashes($purchase['imageURL'])) . "\" />
<span class=\"title\">" . stripslashes($purchase['title']) . "</span>
<span class=\"details\"><strong>Seller</strong>: <a href=\"../search/?search=" . urlencode(stripslashes($purchase['firstName']) . " " . stripslashes($purchase['lastName'])) . "&searchBy=seller&category=0\">" . stripslashes($purchase['firstName']) . " " . stripslashes($purchase['lastName']) . "</a></span>
<span class=\"details\"><strong>Purchased</strong>: " . date("F jS, Y", $purchase['time']) . "</span>
<span class=\"buttonLink\"><span>\$" . stripslashes($purchase['price']) . "</span></span>
</li>
";
		}
		
		echo "</ul>";
	} else {
		echo "
<div class=\"none\">You haven't purchased any books yet! <a class=\"highlight\" href=\"../listings/\">Browse</a> or <a class=\"highlight\" href=\"../search/\">search</a> for books now.</div>";
	}
	
	echo "
</section>";
	
//Include the footer from the public template
	echo "
</section>";

	footer("public");
?>