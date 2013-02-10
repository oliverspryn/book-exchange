<?php
//Include the system's core
	$essentials->storeUserInfo();
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/view-listing.css");
	$essentials->includeJS("system/javascripts/interface.js");
	$exist = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` WHERE `id` = '{$_GET['id']}'");
	
//Grab the information about a certain book category
	if (count($exist)) {
		$now = strtotime("now");
		
		if (isset($_GET['number']) && isset($_GET['section'])) {
			$categoryGrabber = $wpdb->get_results("SELECT ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course WHERE ffi_be_bookcategories.id = '{$_GET['id']}' AND ffi_be_books.number = '{$_GET['number']}' AND ffi_be_books.section = '{$_GET['section']}' GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
			$countGrabber = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_bookcategories.id = '{$_GET['id']}' ANDffi_be_ books.number = '{$_GET['number']}' AND ffi_be_books.section = '{$_GET['section']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
		} elseif (isset($_GET['number']) && !isset($_GET['section'])) {
			$categoryGrabber = $wpdb->get_results("SELECT ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course WHERE ffi_be_bookcategories.id = '{$_GET['id']}' AND ffi_be_books.number = '{$_GET['number']}' GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
			$countGrabber = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_bookcategories.id = '{$_GET['id']}' AND ffi_be_books.number = '{$_GET['number']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
		} else {
			$categoryGrabber = $wpdb->get_results("SELECT ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course WHERE ffi_be_bookcategories.id = '{$_GET['id']}' GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
			$countGrabber = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_bookcategories.id = '{$_GET['id']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
		}
		
		$category = $categoryGrabber[0];
		
		if (count($countGrabber)) {
			$countAll = $countGrabber[0];
			$count = $countAll->total;
		} else {
			$count = 0;
		}
	} else {
		wp_redirect($essentials->friendlyURL("listings"));
	}
	
//Generate the breadcrumb
	//$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//<li><a href=\"../listings\">All Book Listings</a></li>\n";
	
//Generate the title for the page and <header> as well as any specific class names that are needed for the header
	if (isset($_GET['number']) && isset($_GET['section'])) {
		$title = stripslashes($category->name) . " " . urldecode($_GET['number']) . " " . urldecode($_GET['section']);
		$class = " noSide";
	} elseif (isset($_GET['number']) && !isset($_GET['section'])) {
		$title = stripslashes($category->name) . " " . urldecode($_GET['number']);
		$class = " noSide";
	} else {
		$title = stripslashes($category->name);
		$class = "";
	}
	
//If the user has drilled down into class numbers and sections, add more to the breadcrumb
	//if (isset($_GET['number']) && isset($_GET['section'])) {
		//$breadcrumb .= "<li><a href=\"view-listing.php?id=" . $_GET['id'] . "\">" . stripslashes($category['name']) . "</a></li>
//<li><a href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urldecode($_GET['number']) . "\">" . urldecode($_GET['number']) . "</a></li>
//<li>" . urldecode($_GET['section']) . "</li>\n";
	//} elseif (isset($_GET['number']) && !isset($_GET['section'])) {
	//	$breadcrumb .= "<li><a href=\"view-listing.php?id=" . $_GET['id'] . "\">" . stripslashes($category['name']) . "</a></li>
//<li>" . urldecode($_GET['number']) . "</li>\n";
	//} else {
	//	$breadcrumb .= "<li>" . stripslashes($category['name']) . "</li>\n";
	//}
	
	$essentials->setTitle($title);
	
	echo "<section class=\"body\">
";

//Include the page header
	echo "<header class=\"styled" . $class . "\" style=\"border-top-color: " . stripslashes($category->color1) . ";\">
<h1 style=\"background-color: " . stripslashes($category->color3) . "; border-color: " . stripslashes($category->color2) . ";\">" . $title . "</h1>
";
	
	if ($count == 1) {
		echo "<h2>1 Book for Sale</h2>
</header>
";
	} else {
		echo "<h2>" . $count . " Books for Sale</h2>
</header>

";
	}
	
//Display a sort by dropdown menu whenever the user has drilled down to section level
	if (isset($_GET['section'])) {
		if (isset($_GET['sort'])) {
			$sortSelected = urldecode($_GET['sort']);
		} else {
			$sortSelected = "titleASC";
		}
		
		echo "<div class=\"sort\">
<span>Sort by:</span>

<form action=\"" . htmlentities($_SERVER['REQUEST_URI']) . "\" method=\"get\">
<input name=\"id\" type=\"hidden\" value=\"" . htmlentities(urldecode($_GET['id'])) . "\" />
<input name=\"number\" type=\"hidden\" value=\"" . htmlentities(urldecode($_GET['number'])) . "\" />
<input name=\"section\" type=\"hidden\" value=\"" . stripslashes(urldecode($_GET['section'])) . "\" />

<select name=\"sort\">
<option value=\"titleASC\"" . ($sortSelected == "titleASC" ? " selected" : "") . ">Title A-Z</option>
<option value=\"titleDESC\"" . ($sortSelected == "titleDESC" ? " selected" : "") . ">Title Z-A</option>
<option value=\"priceASC\"" . ($sortSelected == "priceASC" ? " selected" : "") . ">Price Low to High</option>
<option value=\"priceDESC\"" . ($sortSelected == "priceDESC" ? " selected" : "") . ">Price High to Low</option>
<option value=\"authorASC\"" . ($sortSelected == "authorASC" ? " selected" : "") . ">Author A-Z</option>
<option value=\"authorDESC\"" . ($sortSelected == "authorDESC" ? " selected" : "") . ">Author Z-A</option>
</select>

<input class=\"blue\" type=\"submit\" value=\"Go\" />
</form>
</div>

";
	}
	
//Only display the sidebar whenever the user hasn't drilled down
	if (!isset($_GET['number'])) {
		echo "<aside class=\"info\">
";
	
	//Display the main icon for this category
		echo "<img class=\"icon\" src=\"" . $essentials->normalizeURL("system/images/categories/" . $category->id . "/icon_256.png") . "\" />";
		
	//Display a search form for this listing
		if ($count > 0) {
			echo "

<h2 style=\"color: " . stripslashes($category->color1) . ";\">Search this Listing</h2>
<form action=\"" . $essentials->friendlyURL("search") . "\" method=\"get\">
<input autocomplete=\"off\" class=\"search full\" name=\"search\" type=\"text\" />
<input type=\"hidden\" name=\"category\" value=\"" . htmlentities(urldecode($_GET['id'])) . "\" />
<span class=\"expand\">Advanced Search Options</span>

<div class=\"controls hidden\">
<span class=\"step\">Search by:</span>
<ul class=\"dropdown\" data-name=\"searchBy\">
<li class=\"selected\" data-value=\"title\">Title</li>
<li data-value=\"author\">Author</li>
<li>ISBN</li>
<li data-value=\"course\">Course</li>
<li data-value=\"seller\">Seller</li>
</ul>

<br>
<span class=\"step\">In: <strong>" . stripslashes($category->name) . "</strong></span>
</div>

<input type=\"submit\" value=\"Search\" />
</form>";
		}
		
	//Display a listing of featured books in this category
		$featuredList = "";
		$now = strtotime("now");
		$featuredGrabber = $wpdb->get_results("SELECT ISBN, title, author, imageURL, COUNT(DISTINCT linkID) AS repeats, ffi_be_exchangesettings.expires FROM ffi_be_books RIGHT JOIN (ffi_be_exchangesettings) ON ffi_be_books.id WHERE course = '" . $_GET['id'] . "' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > '{$now}' GROUP BY ISBN HAVING repeats > 1 ORDER BY repeats DESC LIMIT 7");
				
		foreach($featuredGrabber as $featured) {
			 $featuredList .= "
<li>
<a href=\"../search/?search=" . urlencode(stripslashes($featured->ISBN)) . "&category=" . $_GET['id'] . "&searchBy=ISBN&options=false\"><img src=\"" . htmlentities(stripslashes($featured->imageURL)) . "\" /></a>
<a href=\"../search/?search=" . urlencode(stripslashes($featured->ISBN)) . "&category=" . $_GET['id'] . "&searchBy=ISBN&options=false\" class=\"title\" title=\"" . htmlentities(stripslashes($featured->title)) . "\">" . stripslashes($featured->title) . "</a>
<span class=\"details\" title=\"Author: " . htmlentities(stripslashes($featured->author)) . "\"><strong>Author:</strong> <a href=\"../search?search=" . urlencode(stripslashes($featured->author)) . "&searchBy=author&category=0\">" . stripslashes($featured->author) . "</a></span>
<a href=\"../search/?search=" . urlencode(stripslashes($featured->ISBN)) . "&category=" . $_GET['id'] . "&searchBy=ISBN&options=false\" class=\"buttonLink\"><span>Browse from " . $featured->repeats . " Sellers</span></a>
</li>
";
		}
		
	//If $featuredList is not empty, then the while() loop filled it with values, and at least one value exists
		if (!empty($featuredList)) {
			echo "
		
<section class=\"featured\">
<h2 style=\"color: " . stripslashes($category->color1) . ";\">Featured Books</h2>
<ul>";
			echo $featuredList;
			echo "</ul>
</section>";
		}
		
	//Display a listing of recent additions to this category
		$recentList = "";
		$now = strtotime("now");
		$recentGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.ID AS userTableID, wp_users.display_name, users.user_email FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.course = '" . $_GET['id'] . "' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > '{$now}' GROUP BY ffi_be_books.linkID ORDER BY ffi_be_books.upload DESC LIMIT 7");
				
		foreach($recentGrabber as $recent) {
			if (loggedIn() && $recent['userID'] == $essentials->user->ID) {
				$buy = " noBuy";
			} else {
				$buy = " buy";
			}
			
			$recentList .= "
<li>
<a href=\"../book/?id=" . $recent->id . "\"><img src=\"" . htmlentities(stripslashes($recent->imageURL)) . "\" /></a>
<a href=\"../book/?id=" . $recent->id . "\" class=\"title\" title=\"" . htmlentities(stripslashes($recent->title)) . "\">" . $recent->title . "</a>
<span class=\"details\" title=\"Author: " . htmlentities(stripslashes($recent->author)) . "\"><strong>Author:</strong>  <a href=\"../search?search=" . urlencode(stripslashes($recent->author)) . "&searchBy=author&category=0\">" . stripslashes($recent->author) . "</a></span>
<span class=\"details\" title=\"Seller: " . htmlentities(stripslashes($recent->display_name)) . "\"><strong>Seller:</strong>  <a href=\"../search?search=" . urlencode(stripslashes($recent->display_name)) . "&searchBy=seller&category=0\">" . stripslashes($recent->display_name) . "</a></span>
<a href=\"javascript:;\" class=\"buttonLink" . $buy . "\" data-fetch=\"" . $recent->id . "\"><span>\$" . stripslashes($recent->price) . "</span></a>
</li>
";
		}
		
	//If $recentList is not empty, then the while() loop filled it with values, and at least one value exists
		if (!empty($recentList)) {
			echo "

<section class=\"recent\">
<h2 style=\"color: " . stripslashes($category->color1) . ";\">Recent Additions</h2>
<ul>";
			echo $recentList;
			echo "</ul>
</section>";
		}
		
	//Display a list of other categories that the user can browse
		$allCatGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
		
		echo "

<section class=\"categories\">
<h2 style=\"color: " . stripslashes($category->color1) . ";\">More Book Listings</h2>
<ul>";
		
		foreach($allCatGrabber as $allCat) {
			if ($allCat->id == $_GET['id']) {
				echo "
<li><a href=\"view-listing.php?id=" . $allCat->id . "\" style=\"color: " . stripslashes($category->color1) . "; font-weight: bold;\">" . stripslashes($allCat->name) . " <span class=\"arrow\">&raquo;</span></a></li>";
			} else {
				echo "
<li><a href=\"view-listing.php?id=" . $allCat->id . "\">" . stripslashes($allCat->name) . " <span class=\"arrow\" style=\"color: " . stripslashes($category->color1) . ";\">&raquo;</span></a></li>";
			}
		}
		
		echo "
</ul>
</section>
</aside>

";
	}
	
//The style of the main container will need modified if the sidebar isn't there
	if (!isset($_GET['number'])) {
		echo "<section class=\"listing\">
";
	} else {
		echo "<section class=\"listing noSide\">
";
	}
	

//Display a Wikipedia article introduction at the top of the main content
//Client-side code will fetch the article, so just provide the container
	if (!isset($_GET['number'])) {
		echo "<article class=\"description\">
<section class=\"article loading\"></section>
<a href=\"javascript:;\" class=\"buttonLink\" style=\"display: none;\"><span>Read More</span></a>
<section class=\"disclaimer hidden\">The entry was extracted from <a class=\"highlight\" href=\"http://en.wikipedia.org/\" target=\"_blank\">Wikipedia</a>, which is licensed under the <a class=\"highlight\" href=\"http://creativecommons.org/licenses/by-sa/3.0/\" target=\"_blank\">CC BY-SA 3.0</a> license. The contents of the entry above reflect the views of the Wikipedia contributors, not the views of this site's owner, maintenance staff, or parent organization.</section>
</article>

";
	}

//Fetch and display a listing of books by course ID and futhermore course ID and section letter
	$currentNumber = "0";
	$currentSection = "0";
	$firstInSection = false;
	$counter = 0;
	$sectionCounter = 1;
	$now = strtotime("now");
	
	if (isset($_GET['number']) && isset($_GET['section'])) {
		if (isset($_GET['sort'])) {
			switch($_GET['sort']) {
				case "titleASC" : 
					$sort = "books.title ASC, books.price ASC";
					break;
					
				case "titleDESC" : 
					$sort = "books.title DESC, books.price ASC";
					break;
					
				case "priceASC" : 
					$sort = "books.price ASC, books.title ASC";
					break;
					
					
				case "priceDESC" : 
					$sort = "books.price DESC, books.title ASC";
					break;
					
					
				case "authorASC" : 
					$sort = "books.author ASC, books.title ASC";
					break;
					
					
				case "authorDESC" : 
					$sort = "books.author DESC, books.title ASC";
					break;
					
				default : 
					$sort = "books.title ASC";
					break;
			}
		} else {
			$sort = "books.title ASC";
		}
		
		$booksGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.ID AS userTableID, wp_users.display_name, wp_users.user_email FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.course = '" . $_GET['id'] . "' AND ffi_be_books.number = '{$_GET['number']}' AND ffi_be_books.section = '{$_GET['section']}'AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > '{$now}' ORDER BY ffi_be_books.number ASC, ffi_be_books.section ASC, " . $sort);
	} elseif (isset($_GET['number']) && !isset($_GET['section'])) {
		$booksGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.ID AS userTableID, wp_users.display_name, wp_users.user_email FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.course = '" . $_GET['id'] . "' AND ffi_be_books.number = '{$_GET['number']}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > '{$now}' ORDER BY ffi_be_books.number ASC, ffi_be_books.section ASC, ffi_be_books.title ASC");
	} else {
		$booksGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.ID AS userTableID, wp_users.display_name, wp_users.user_email FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.course = '" . $_GET['id'] . "' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > '{$now}' ORDER BY ffi_be_books.number ASC, ffi_be_books.section ASC, ffi_be_books.title ASC");
	}
	
	echo "<section class=\"books\">
";
	
	foreach($booksGrabber as $books) {
	//If this is a our first iteration or first iteration through a course number, save the number as a marker, and print a new <section>
		if ($currentNumber == "0" || $currentNumber != $books->number) {
			if ($currentNumber != "0") {
				echo "</ul>
";
				if (!isset($_GET['section'])) {
					echo "
<a class=\"more\" href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($currentNumber)) . "&section=" . urlencode(stripslashes($currentSection)) . "\">See more <span class=\"arrow\" style=\"color: " . stripslashes($category->color1) . ";\">&raquo;</span></a>
";
				}

				echo "</section>

<section class=\"courses\">
";

				if (!isset($_GET['number'])) {
					echo "<h2><a href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode($books->number) . "\" style=\"color: " . stripslashes($category->color1) .";\">" . stripslashes($category->course) . " " . stripslashes($books->number) . " &raquo;</a></h2>
";
				}
				
			//We are now in a new section, so reset the section counter
				$sectionCounter = 1;
			} else {
				echo "<section class=\"courses\">
";
				if (!isset($_GET['number'])) {
					echo "<h2><a href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($books->number)) . "\" style=\"color: " . stripslashes($category->color1) .";\">" . stripslashes($category->course) . " " . stripslashes($books->number) . " &raquo;</a></h2>
";
				}
			}
			
			$currentNumber = $books->number;
			$firstInSection = true; //We have started a new section, so make sure the nested <ul> knows which is the first item!
		}
		
	/**
	 * If we are going from, say 101 A to 102 A, then the "A" in both section numbers
	 * will cause the algorithm  to think that 102 A is still in the "A" section for
	 * 101, and thus render it incorrectly.
	 * 
	 * If the algorithm is going from one number to another number with the same 
	 * section letter, then force the algorithm to construct the unordered list and
	 * display the section letter by setting the $currentSection equal to 0
	*/
		if ($currentSection == $books->section && $firstInSection) {
			$currentSection = "0";
		}
		
	//If this is a our first iteration or first iteration through a course letter, save the letter as a marker, and print a new <ul>
		if ($currentSection == "0" || $currentSection != $books->section) {
			if (!$firstInSection) {
				echo "</ul>

";
				
				if (!isset($_GET['section'])) {
					echo "<a class=\"more\" href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($currentNumber)) . "&section=" . urlencode(stripslashes($currentSection)) . "\">See more <span class=\"arrow\" style=\"color: " . stripslashes($category->color1) . ";\">&raquo;</span></a>

";
				}
		
			//Display a section letter if the user is viewing the class numbers...
				if (isset($_GET['number'])) {
					echo "<h2><a href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($books->number)) . "&section=" . urlencode(stripslashes($books->section)) . "\" style=\"color: " . stripslashes($category->color1) . ";\">" . stripslashes($category->course) . " " . stripslashes($books->number) . " " . stripslashes($books->section) . " &raquo;</a></h2>
";
				}

				echo "<ul>";
			
			//... otherwise include the section letter inline with the book titles
				if (!isset($_GET['number'])) {
					echo "
<li class=\"section\">" . stripslashes($books->section) . "</li>
";
				}
				
			//We are now in a new section, so reset the section counter
				$sectionCounter = 1;
			} else {
			//Display a section letter if the user is viewing the class numbers...
				if (isset($_GET['number']) && !isset($_GET['section'])) {
					echo "<h2><a href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($books->number)) . "&section=" . urlencode(stripslashes($books->section)) . "\" style=\"color: " . stripslashes($category->color1) .";\">" . stripslashes($category->course) . " " . stripslashes($books->number) . " " . stripslashes($books->section) . " &raquo;</a></h2>
";
				}
				
			//This unordered list will display if the user is viewing a section, which will require a special class
				if (isset($_GET['number']) || isset($_GET['section'])) {
					echo "<ul class=\"viewAll\">";
				} else {
					echo "<ul>";
				}
		
			//... otherwise include the section letter inline with the book titles
				if (!isset($_GET['number'])) {
					echo "
<li class=\"section\">" . stripslashes($books->section) . "</li>
";
				}
			}
		}
		
	//Only display four books if this is the main page, but display up to 6 if we are looking within a class number
		if ((!isset($_GET['number']) && $sectionCounter <= 4) || (isset($_GET['number']) && !isset($_GET['section']) && $sectionCounter <= 7) || (isset($_GET['number']) && isset($_GET['section']))) {
			if (is_user_logged_in() && $books->userID == $essentials->user->ID) {
				$buy = " noBuy";
			} else {
				$buy = " buy";
			}
			
		//Give the first book in the list a special CSS class
			if ($sectionCounter == 1 && !isset($_GET['number']) && !isset($_GET['section'])) {
				$class = " class=\"firstBook\"";
			} else {
				$class = "";
			}
			
			echo "
<li" . $class . ">
<a href=\"../book/?id=" . $books->id . "\"><img src=\"" . htmlentities(stripslashes($books->imageURL)) . "\" /></a>
<a class=\"title\" href=\"../book/?id=" . $books->id . "\" title=\"" . htmlentities(stripslashes($books->title)) . "\">" . stripslashes($books->title) . "</a>
<span class=\"details\" title=\"Author: " . htmlentities(stripslashes($books->author)) . "\"><strong>Author:</strong> <a href=\"../search?search=" . urlencode(stripslashes($books->author)) . "&searchBy=author&category=0\">" . stripslashes($books->author) . "</a></span>
<span class=\"details\" title=\"Seller: " . stripslashes($books->display_name) . "\"><strong>Seller:</strong> <a href=\"../search?search=" . urlencode(stripslashes($books->display_name)) . "&searchBy=seller&category=0\">" . stripslashes($books->display_name) . "</a></span>
<a href=\"javascript:;\" class=\"buttonLink" . $buy . "\" data-fetch=\"" . $books->id . "\"><span>\$" .stripslashes($books->price) . "</span></a>
</li>
";
		}
		
		$currentSection = $books->section;
		$firstInSection = false; //We've already gone at least once through section, so make sure the nested <ul> knows that!
		$sectionCounter ++;
		
	//Keep track of the iterations, if any were done at all
		$counter ++;
	}
	
//Print the closing tags, if any books were listed in this category
	if ($counter > 0) {
		echo "</li>
</ul>
";
		
	//Don't show the see more whenever the user has drilled down to a class section
		if (!isset($_GET['section'])) {
			echo "
<a class=\"more\" href=\"view-listing.php?id=" . $_GET['id'] . "&number=" . urlencode(stripslashes($currentNumber)) . "&section=" . urlencode(stripslashes($currentSection)) . "\">See more <span class=\"arrow\" style=\"color: " . stripslashes($category->color1) . ";\">&raquo;</span></a>
";
		}
 
		echo "</section>";
	} else {
		$courseSpecific = "";
		
	//Is it just a particular section or number where we don't have any books?
		if (isset($_GET['number']) || isset($_GET['section'])) {
			redirect("view-listing.php?id=" . $_GET['id']);
		}
		
		echo "<section class=\"empty\">
<p>We don't have any books for sale in " . stripslashes($category->name) . " right now. Come back later, and we'll be sure to have some!</p>
</section>";
	}

//Include the footer from the administration template
	echo "
</section>
</section>
</section>";
?>