<?php
//Include the system's core
	$essentials->requireLogin();
	$essentials->setTitle("My Account");
	$essentials->includePHP("system/server/Validate.php");
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/account.css");
	$essentials->includeCSS("system/stylesheets/validationEngine.jquery.min.css");
	$essentials->includeJS("system/javascripts/jquery.validationEngine.min.js");
	$essentials->includeJS("system/javascripts/interface.js");
	$essentials->includeJS("system/javascripts/md5.min.js");
	
//Update a user's profile
	if (isset($_POST['action']) && $_POST['action'] == "profile") {
		$id = $essentials->user->ID;
		$first = Validate::required($_POST['first']);
		$last = Validate::required($_POST['last']);
		$displayName = $first . " " . $last;
		$emailAddress1 = Validate::isEmail($_POST['emailAddress1']);
		//$emailAddress2 = mysql_real_escape_string(Validate::email($_POST['emailAddress2'], true));
		//$emailAddress3 = mysql_real_escape_string(Validate::email($_POST['emailAddress3'], true));
		
	//Check if the username exists
		$username = username_exists($emailAddress1);
		
		if (!is_null($username) && $username != $id) {
			echo "This username already exists";
			exit;
		}
		
	//Check the password, did we get one?
		if ($_POST['password'] != "") {
			$password = $_POST['password'];
			
			wp_update_user(array(
				"ID" => $id,
				"first_name" => $first,
				"last_name" => $last,
				"display_name" => $displayName,
				"user_email" => $emailAddress1,
				"user_pass" => $password
			));
		} else {
			wp_update_user(array(
				"ID" => $id,
				"first_name" => $first,
				"last_name" => $last,
				"display_name" => $displayName,
				"user_email" => $emailAddress1
			));
		}
		
		$wpdb->update(
			"wp_users",
			array("user_login" => $emailAddress1),
			array("ID" => $id)
		);
				
		echo "success";
		exit;
	}
	
//Renew a book
	if (isset($_GET['action']) && $_GET['action'] == "renew" && isset($_GET['id'])) {
		//if ($userData['role'] == "Administrator") {
		//	$booksGrabber = mysql_query("SELECT linkID FROM books WHERE id = '{$_GET['id']}' AND books.id IS NOT NULL", $connDBA);
		//} else {
			$booksGrabber = $wpdb->get_results("SELECT linkID FROM ffi_be_books WHERE ffi_be_books.userID = '{$essentials->user->ID}' AND id = '{$_GET['id']}' AND ffi_be_books.id IS NOT NULL");
		//}
		
		if (count($booksGrabber)) {
			$booksLink = $booksGrabber[0];
			$now = strtotime("now");
			
			$wpdb->get_results("UPDATE ffi_be_books SET upload = '{$now}', sold = '0' WHERE linkID = '{$booksLink->linkID}'");
			$wpdb->get_results("DELETE FROM ffi_be_purchases WHERE bookID = '{$_GET['id']}'"); //Delete this from the purchase list. Guess the seller didn't sell it to anyone
			wp_redirect($essentials->friendlyURL("account/?message=renewed#book_" . $_GET['id']));
			exit;
		} else {
			wp_redirect($essentials->friendlyURL("account"));
			exit;
		}
	}
	
//Delete a book
	if (isset($_GET['action']) && $_GET['action'] == "delete" && isset($_GET['id'])) {
		$booksGrabber = $wpdb->get_results("SELECT linkID FROM ffi_be_books WHERE ffi_be_books.userID = '{$essentials->user->ID}' AND id = '{$_GET['id']}' AND ffi_be_books.id IS NOT NULL");
		
		if (count($booksGrabber)) {
			$booksLink = $booksGrabber[0];
			
		//We set the userID equal to 0, so it is not associated with any user, but
		//also isn't deleted from a buyer's purchase history
			$wpdb->get_results("UPDATE ffi_be_books SET userID = '0' WHERE linkID = '{$booksLink->linkID}'", $connDBA);
			wp_redirect($essentials->friendlyURL("account/?message=deleted"));
			exit;
		} else {
			wp_redirect($essentials->friendlyURL("account"));
			exit;
		}
	}
	
//Generate the breadcrumb
	////$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//<li>My Account</li>\n";
	
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
<span class=\"firstName\">" . stripslashes($essentials->user->first_name) . "</span> <span class=\"lastName\">" . stripslashes($essentials->user->last_name) . "</span>
</span>

<span class=\"row\">
<strong>Email:</strong>
<a class=\"emailAddress1\" href=\"mailto:" . htmlentities(stripslashes($essentials->user->user_email)) .  "\">" . stripslashes($essentials->user->user_email) . "</a>
</span>

<span class=\"row\">
<strong>Password:</strong>
<span>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</span>
</span>

<button class=\"blue updateProfile\">Update Profile</button>
</section>

";
	
//Include a list of books that the user has listed for sale
	$booksGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_bookcategories.*, ffi_be_books.id AS bookID, ffi_be_books.course AS courseID FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id WHERE ffi_be_books.userID = '{$essentials->user->ID}' AND ffi_be_books.id IS NOT NULL GROUP BY ffi_be_books.linkID ORDER BY ffi_be_books.upload DESC");
	$exchangeSettings = $wpdb->get_results("SELECT * FROM ffi_be_exchangesettings WHERE id = '1'");
	$exchangeSettings = $exchangeSettings[0];
	
	echo "<section class=\"books\">
<h2>My Books</h2>
";
	
	if (count($booksGrabber)) {
		echo "<ul>";
		
		$now = strtotime("now");
		$week = strtotime("+1 week");
		
		for($i = 0; $i < count($booksGrabber); $i++) {
			$book = $booksGrabber[$i];
			
		//Has this book expired, been sold, or will it expire within the next week?
			if ($book->sold == "0" && ($book->upload + $exchangeSettings->expires) < $now) {
				$class = " expired";
				$expireRenew = "<a class=\"action renew\" href=\"" . $essentials->friendlyURL("account/?action=renew&id=" . $book->bookID) . "\" title=\"Restore to the Exchange\"><img src=\"" . $essentials->normalizedURL("system/images/icons/renew.png") . "\" /></a>
";
				$expire = "<span class=\"expire\">Expired: " . date("F jS, Y \a\\t h:i A", ($book->upload + $exchangeSettings->expires)) . "</span>
";
				$status = "<span class=\"expired\">Expired</span>
";
			} elseif ($book->sold == "0" && ($book->upload + $exchangeSettings->expires) < ($week) && ($book->upload + $exchangeSettings->expires) > ($now)) {
				$class = " soon";
				$expireRenew = "<a class=\"action renew\" href=\"" . $essentials->friendlyURL("account/?action=renew&id=" . $book->bookID) . "\" title=\"Restore to the Exchange\"><img src=\"" . $essentials->normalizeURL("system/images/icons/renew.png") . "\" /></a>
";
				$expire = "<span class=\"expire\">Expires: " . date("F jS, Y \a\\t h:i A", ($book->upload + $exchangeSettings->expires)) . "</span>
";
				$status = "<span class=\"expiring\">Expiring Soon, Click the Renew Button to Prevent Expiration</span>
";
			} elseif ($book->sold == "1") {
				$class = " sold";
				$expireRenew = "<a class=\"action renew\" href=\"" . $essentials->friendlyURL("account/?action=renew&id=" . $book->bookID) . "\" title=\"Restore to the Exchange\"><img src=\"" . $essentials->normalizeURL("system/images/icons/renew.png") . "\" /></a>
";
				$expire = "";
				$status = "<span class=\"sold\">Sold</span>
";
			} else {
				$class = "";
				$expireRenew = "";
				$expire = "<span class=\"expire\">Expires: " . date("F jS, Y \a\\t h:i A", ($book->upload + $exchangeSettings->expires)) . "</span>
";
				$status = "";
			}
			
			echo "
<li class=\"book\">
<div class=\"alert" . $class . "\">
<a name=\"book_" . $book->bookID . "\"></a>
<a href=\"" . $essentials->friendlyURL("book-details/?id=" . $book->bookID) . "\"><img class=\"cover\" src=\"" . htmlentities(stripslashes($book->imageURL)) . "\" /></a>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $book->bookID) . "\">" . stripslashes($book->title) . "</a>
<span class=\"details\"><strong>Author:</strong> <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($book->author)) . "&searchBy=author&category=0") . "\">" . stripslashes($book->author) . "</a></span>
<span class=\"details\"><strong>ISBN:</strong> <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($book->ISBN)) . "&searchBy=ISBN&category=0") . "\">" . stripslashes($book->ISBN) . "</a></span>
" . $expireRenew . "<a class=\"action edit\" href=\"" . $essentials->friendlyURL("sell-books/?id=" . $book->bookID) . "\" title=\"Edit this Book\"><img src=\"" . $essentials->normalizeURL("system/images/icons/edit.png") . "\" /></a>
<a class=\"action deleteBook\" data-id=\"" . $book->bookID . "\" href=\"javascript:;\" title=\"Delete this Book\"><img src=\"" . $essentials->normalizeURL("system/images/icons/delete.png") . "\" /></a>
" . $expire . $status . "</div>
</li>
";
		}
		
		echo "</ul>";
	} else {
		echo "
<div class=\"none\">You don't have any books for sale yet! <a class=\"highlight\" href=\"" . $essentials->normalizeURL("sell-books") . "\">Sell some now</a>.</div>";
	}
	
	echo "
</section>";
	
//Include a list of books that the user has purchased
	$purchasedGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_purchases.*, wp_users.*, ffi_be_books.id AS bookID FROM ffi_be_books RIGHT JOIN (ffi_be_purchases) ON ffi_be_books.id = ffi_be_purchases.bookID RIGHT JOIN (wp_users) ON ffi_be_purchases.sellerID = wp_users.ID WHERE ffi_be_purchases.buyerID = '{$essentials->user->ID}' AND ffi_be_books.id IS NOT NULL GROUP BY ffi_be_books.linkID ORDER BY ffi_be_purchases.time DESC");
	
	
	echo "<section class=\"purchases\">
<h2>My Purchases</h2>
";
	
	if (count($purchasedGrabber)) {
		echo "<ul>";
		
		for($i = 0; $i < count($purchasedGrabber); $i++) {
			$purchase = $purchasedGrabber[$i];
			
			echo "
<li class=\"book\">
<img src=\"" . htmlentities(stripslashes($purchase->imageURL)) . "\" />
<span class=\"title\">" . stripslashes($purchase->title) . "</span>
<span class=\"details\"><strong>Seller</strong>: <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($purchase->display_name)) . "&searchBy=seller&category=0") . "\">" . stripslashes($purchase->display_name) . "</a></span>
<span class=\"details\"><strong>Purchased</strong>: " . date("F jS, Y", $purchase->time) . "</span>
<span class=\"buttonLink\"><span>\$" . stripslashes($purchase->price) . "</span></span>
</li>
";
		}
		
		echo "</ul>";
	} else {
		echo "
<div class=\"none\">You haven't purchased any books yet! <a class=\"highlight\" href=\"" . $essentials->friendlyURL("listings") . "\">Browse</a> or <a class=\"highlight\" href=\"" . $essentials->friendlyURL("search") . "\">search</a> for books now.</div>";
	}
	
	echo "
</section>";
	
//Include the footer from the public template
	echo "
</section>";
?>