<?php
//Include the system's core
	$essentials->storeUserInfo();
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/book.css");
	$essentials->includeJS("system/javascripts/interface.js");
	$essentials->includeJS("//static.ak.fbcdn.net/connect.php/js/FB.Share");
	$essentials->includeJS("//platform.twitter.com/widgets.js");
	$essentials->includeJS("//apis.google.com/js/plusone.js");
	$essentials->includeJS("//assets.pinterest.com/js/pinit.js");
	
//Grab the book's information
	if (isset($_GET['id'])) {
		$bookData = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, wp_users.*, ffi_be_books.id AS bookID, ffi_be_books.course AS courseID, GROUP_CONCAT(ffi_be_books.course) AS classIDs, GROUP_CONCAT(ffi_be_bookcategories.course) AS classShort, GROUP_CONCAT(ffi_be_bookcategories.name) AS classes, GROUP_CONCAT(ffi_be_books.section) AS classSec, GROUP_CONCAT(ffi_be_books.number) AS classNum FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON wp_users.ID WHERE ffi_be_books.linkID = (SELECT linkID FROM ffi_be_books WHERE id = '{$_GET['id']}' LIMIT 1) AND ffi_be_books.userID != '0' GROUP BY ffi_be_books.linkID");
		
		if (count($bookData)) {
			$book = $bookData[0];
			$now = strtotime("now");
			
		//Has the book been sold, expired, or deleted? If so, only the seller can view it, unless it is deleted
			if (((($book->expires + $book->upload) < $now) || $book->sold == '1') && $essentials->user->ID != $book->userID) {
				wp_redirect($essentials->friendlyURL("listings"));
				exit;
			}
		} else {
			wp_redirect($essentials->friendlyURL("listings"));
			exit;
		}
	} else {
		wp_redirect($essentials->friendlyURL("listings"));
		exit;
	}
	
//Generate the breadcrumb
	//$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//<li><a href=\"../listings\">All Books Listings</a></li>
//<li><a href=\"../listings/view-listing.php?id=" . $book['courseID'] . "\">" . stripslashes($book['name']) . "</a></li>
//<li>" . stripslashes($book['title']) . "</li>\n";

//Generate the meta tags for the social networking plugins
	global $meta;
	$meta = "<meta property=\"og:title\" content=\"" . htmlentities(stripslashes($book->title)) . "\" />
<meta property=\"og:description\" content=\"" . htmlentities(stripslashes($book->display_name) . " is selling \"" . stripslashes($book->title) . "\" on the Grove City College Student Government Association book exchange for only \$" . stripslashes($book->price) . "!") . "\" />
<meta property=\"og:image\" content=\"" . htmlentities(stripslashes($book->imageURL)) . "\" />
<meta itemprop=\"name\" content=\"" . htmlentities(stripslashes($book->title)) . "\">
<meta itemprop=\"description\" content=\"" . htmlentities(stripslashes($book->display_name) . " is selling \"" . stripslashes($book->title) . "\" on the Grove City College Student Government Association book exchange for only \$" . stripslashes($book->price) . "!") . "\">
<meta itemprop=\"image\" content=\"" . stripslashes($book->imageURL) . "\">
";

	add_action("wp_head", "socialMeta", 0);
	function socialMeta() {
		global $meta;	
		return $meta;
	}
	
	$essentials->setTitle($book->title);

	echo "<section class=\"body\">
";
	
//Include the page header
	echo "<header class=\"styled\" style=\"border-top-color: " . stripslashes($book->color1) . "\">
<h1 style=\"background-color: " . stripslashes($book->color3) . "; border-color: " . stripslashes($book->color2) . ";\">" . stripslashes($book->title) . "</h1>
</header>

";
	
//Include the sidebar
	echo "<aside class=\"info\">
";
	
//Display the book cover, price, and social networking links
	echo "<section class=\"cover\">
<img class=\"cover\" src=\"" . stripslashes($book->imageURL) . "\" />
<div class=\"facebookContainer\"><a name=\"fb_share\" type=\"button\"></a></div>
<div class=\"twitterContainer\"><a href=\"https://twitter.com/share\" class=\"twitter-share-button\" data-related=\"sgaatgcc\" data-text=\"" . htmlentities(stripslashes($book->display_name) . " is selling \"" . stripslashes($book->title) . "\" on the Grove City College Student Government Association book exchange for only \$" . stripslashes($book->price) . "!") . "\">Tweet</a></div>
<div class=\"gplusContainer\"><div class=\"g-plusone\"></div></div>
<div class=\"pinContainer\"><a href=\"http://pinterest.com/pin/create/button/?url=" . urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "&media=" . urlencode(stripslashes($book->imageURL)) . "&description=" . urlencode(stripslashes($book->display_name) . " is selling \"" . stripslashes($book->title) . "\" on the Grove City College Student Government Association book exchange for only \$" . stripslashes($book->price) . "!") . "\" class=\"pin-it-button\" count-layout=\"horizontal\" target=\"_blank\"><img src=\"https://assets.pinterest.com/images/PinExt.png\" title=\"Pin It\" /></a></div>
";
	
	//Don't allow a user to buy from themself!
		if (!is_user_logged_in() || (is_user_logged_in() && $book->userID != $essentials->user->ID)) {
			echo "<a class=\"buttonLink big buyDirect\" data-fetch=\"" . $book->bookID . "\" href=\"javascript:;\"><span>\$" . stripslashes($book->price) . " Buy</span></a>
";
		} else {
			echo "<span class=\"buttonLink big\"><span>\$" . stripslashes($book->price) . "</span></span>
";
		}
		
		echo "</section>

";
	
//If this user owns this book, given them buttons to go an edit this book
	if (is_user_logged_in() && $book->userID == $essentials->user->ID) {
		echo "<section class=\"toolbar\">
<button class=\"blue\" onclick=\"document.location.href='" . $essentials->friendlyURL("sell-books/?id=" . $_GET['id']) . "'\">Edit this Book</button>
<button class=\"red deleteBook\" data-id=\"" . $_GET['id'] . "\">Delete this Book</button>
</section>

";
	}
	
//Display a list of other categories that the user can browse
		$allCatGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
		
		echo "<section class=\"categories\">
<h2 style=\"color: " . stripslashes($book->color1) . ";\">More Book Listings</h2>
<ul class=\"moreListings\">";
		
		foreach($allCatGrabber as $allCat) {
			echo "
<li><a href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $allCat->id) . "\">" . stripslashes($allCat->name) . " <span class=\"arrow\" style=\"color: " . stripslashes($book->color1) . ";\">&raquo;</span></a></li>";
		}
		
		echo "
</ul>
</section>
</aside>

";
	
	echo "<section class=\"allInfo\">
";
	
//Display the book's general information
	echo "<section class=\"general\">
<h2>General Information</h2>
<span class=\"details\"><strong>Author:</strong> " . stripslashes($book->author) . "</span>
";
	
	if ($book->edition != "") {
		echo "<span class=\"details\"><strong>Edition:</strong> " . $book->edition . "</span>
";
	}

	echo "<span class=\"details\"><strong>ISBN:</strong> " . stripslashes($book->ISBN) . "</span>

";
	
	//Conditionally format the condition of the book
		switch($book->condition) {
			case "Excellent" : 
				echo "<span class=\"excellent\">Excellent Condition</span>
";
				break;
				
			case "Very Good" : 
				echo "<span class=\"veryGood\">Very Good Condition</span>
";
				break;
				
			case "Good" : 
				echo "<span class=\"good\">Good Condition</span>
";
				break;
				
			case "Fair" : 
				echo "<span class=\"fair\">Fair Condition</span>
";
				break;
				
			case "Poor" : 
				echo "<span class=\"poor\">Poor Condition</span>
";
				break;
		}
	
	//Conditionally format whether or not the book has been written in
		if ($book->written == "Yes") {
			echo "<span class=\"marks\">Has Writing or Markings</span>
";
		} else {
			echo "<span class=\"noMarks\">No Writing or Markings</span>
";
		}
	
	echo "
</section>

";
	
//Display any comments associated with this book
	if ($book->comments != "") {
		echo "<section class=\"comments\">
<h2>Seller Comments</h2>
" . stripslashes($book->comments) . "
</section>

";
	}
	
//Display a list of classes that used this book
	echo "<section class=\"classes\">
<h2>Classes That Use This Book</h2>
<ul>";
	
	$classIDs = explode(",", $book->classIDs);
	$classes = explode(",", $book->classes);
	$classNum = explode(",", $book->classNum);
	$classSec = explode(",", $book->classSec);
	
	for ($i = 0; $i <= sizeof($classIDs) - 1; $i ++) {
		echo "
<li>
<img src=\"" . $essentials->normalizeURL("system/images/categories/" . $classIDs[$i] . "/icon_128.png") . "\" title=\"" . htmlentities(stripslashes($classes[$i])) . "\" />
<span class=\"classDetails\">" . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]) . "</span>
</li>";
	}
	
	echo "
</ul>
</section>

";
	
//Display the seller's profile, only if the user is loggged in, for security
	if (is_user_logged_in()) {	
		echo "<section class=\"seller\">
<h2>Seller Information</h2>
<span class=\"details\"><strong>Name:</strong> " . stripslashes($book->display_name) . "</span>
<span class=\"details\"><strong>Email:</strong> <a href=\"mailto:" . stripslashes($book->user_email) . "\">" . stripslashes($book->user_email) . "</a></span>
";

		echo "</section>

";
	}
	
//Include other books by this seller
	$now = strtotime("now");
	$sellerOtherGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, wp_users.*, ffi_be_books.id AS bookID, ffi_be_books.course AS courseID FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON wp_users.ID WHERE ffi_be_books.userID = (SELECT userID FROM ffi_be_books WHERE id = '{$_GET['id']}') AND ffi_be_books.linkID != (SELECT linkID FROM ffi_be_books WHERE id = '{$_GET['id']}' LIMIT 1) AND ffi_be_books.sold != '1' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_books.linkID ORDER BY RAND() LIMIT 4");
	
	if (count($sellerOtherGrabber)) {
		echo "<section class=\"more\">
<h2 style=\"color: " . stripslashes($book->color1) . ";\">Other Books for Sale by " . stripslashes($book->display_name) . "</h2>
<ul>";
		
		foreach($sellerOtherGrabber as $sellerOther) {
			if (is_user_logged_in() && $sellerOther->userID == $essentials->user->ID) {
				$buy = " noBuy";
			} else {
				$buy = " buy";
			}
			
			echo "
<li>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $sellerOther->bookID) . "\"><img src=\"" . htmlentities(stripslashes($sellerOther->imageURL)) . "\"></a>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $sellerOther->bookID) . "\" title=\"" . htmlentities(stripslashes($sellerOther->title)) . "\">" . stripslashes($sellerOther->title) . "</a>
<span class=\"details\" title=\"Author: " . htmlentities(stripslashes($sellerOther->author)) . "\">Author: " . stripslashes($sellerOther->author) . "</span>
<a class=\"buttonLink" . $buy . "\" href=\"javascript:;\" data-fetch=\"" . $sellerOther->bookID . "\"><span>\$" . stripslashes($sellerOther->price) . "</span></a>
</li>
";
		}
		
		echo "</ul>
	
<a class=\"more\" href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($book->display_name)) . "&searchBy=seller&category=0&options=false") . "\">See More <span class=\"arrow\" style=\"color: " . stripslashes($book->color1) . ";\">&raquo;</span></a>
</section>

";
	}
	
//Include other books for sale within the sections that this book is listed
	$SQL = "";
	$title = "";
	$seeMore = "";
	$classIDs = explode(",", $book->classIDs);
	$classNames = explode(",", $book->classShort);
	$classNum = explode(",", $book->classNum);
	$classSec = explode(",", $book->classSec);
	
//If there only one section that this class is listed in, then we only need a "See More" link, otherwise
//generate a "See More" link for each specific class
	if (sizeof($classIDs) == 1) {
		$seeMore = "<a class=\"more\" href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($classNum['0'])) . " " . urlencode(stripslashes($classSec['0'])) . "&category=" . $classIDs['0'] . "&searchBy=course&options=false") . "\">See More <span class=\"arrow\" style=\"color: " . stripslashes($book->color1) . ";\">&raquo;</span></a>
";
	}
	
//Generate a dynamic SQL query, title for the "Other Book" section, and listing of "See More" links for
//each class section
	for ($i = 0; $i <= sizeof($classIDs) - 1; $i ++) {
	//Generate the SQL
		$SQL .= " OR (ffi_be_books.course = '" . $classIDs[$i] . "' AND ffi_be_books.number = '" . $classNum[$i] . "' AND ffi_be_books.section = '" . $classSec[$i] . "')";
		
	//Generate the title
	//Add an "and" before the last class in the list, only if the list has more than 1 value
		if ($i == sizeof($classIDs) - 1 && $title != "") {
		//The list will need to be comma seperated if there are more than 2 items
			if (sizeof($classIDs) > 2) {
				$title .= ", and " . stripslashes($classNames[$i]) . " " . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]);
			} else {
				$title .= " and " . stripslashes($classNames[$i]) . " " . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]);
			}
		} else {
		//The list will need to be comma seperated if there are more than 2 items
			if (sizeof($classIDs) > 2) {
				$title .= ", " . stripslashes($classNames[$i]) . " " . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]);
			} else {
				$title .= " " . stripslashes($classNames[$i]) . " " . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]);
			}
		}
		
	//Generate the "See More" links
		if (sizeof($classIDs) > 1) {
			$seeMore .= "<a class=\"more\" href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($classNum[$i])) . " " . urlencode(stripslashes($classSec[$i])) . "&category=" . urlencode(stripslashes($classIDs[$i])) . "&searchBy=course&options=false") . "\">See More in " . stripslashes($classNames[$i]) . " " . stripslashes($classNum[$i]) . " " . stripslashes($classSec[$i]) . " <span class=\"arrow\" style=\"color: " . stripslashes($book->color1) . ";\">&raquo;</span></a>
";
		}
	}
	
	$SQL = ltrim($SQL, " OR ");
	$title = ltrim($title, ", ");
	$now = strtotime("now");
	$catOtherGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, wp_users.*, ffi_be_books.id AS bookID, ffi_be_books.course AS courseID FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON wp_users.ID WHERE {$SQL} AND books.linkID != (SELECT linkID FROM ffi_be_books WHERE id = '{$_GET['id']}' LIMIT 1) AND ffi_be_books.sold != '1' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_books.linkID ORDER BY RAND() LIMIT 4");
	
	if (count($catOtherGrabber)) {
		echo "<section class=\"other\">
<h2 style=\"color: " . $book->color1 . ";\">Other Books For Sale in " . stripslashes($title) . "</h2>
<ul>";
		
		foreach($catOtherGrabber as $catOther) {
			if (is_user_logged_in() && $catOther->userID == $essentials->user-ID) {
				$buy = " noBuy";
			} else {
				$buy = " buy";
			}
			
			echo "
<li>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $catOther->bookID) . "\"><img src=\"" . htmlentities(stripslashes($catOther->imageURL)) . "\"></a>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $catOther->bookID) . "\" title=\"" . htmlentities(stripslashes($catOther->title)) . "\">" . stripslashes($catOther->title) . "</a>
<span class=\"details\" title=\"Author: " . htmlentities(stripslashes($catOther->author)) . "\">Author: " . stripslashes($catOther->author) . "</span>
<a class=\"buttonLink" . $buy . "\" href=\"javascript:;\" data-fetch=\"" . $catOther->bookID . "\"><span>\$" . stripslashes($catOther->price) . "</span></a>
</li>
";
		}
		
		echo "</ul>
		
" . $seeMore . "</section>";
	}
	
//Include the footer from the public template
	echo "
</section>
</section>";
?>