<?php
//Include the system's core
	$essentials->storeUserInfo();
	$essentials->includePHP("system/server/Validate.php");
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/search.css");
	$essentials->includeJS("system/javascripts/interface.js");
	
//Perform a search operation on the database
	if (isset($_GET['search']) && $_GET['search'] != "") {
		$query = mysql_real_escape_string($_GET['search']);
		$category = mysql_real_escape_string(Validate::numeric($_GET['category']));
		$searchBy = mysql_real_escape_string(Validate::required($_GET['searchBy'], array("title", "author", "ISBN", "course", "seller")));
		
	//Search by a specific category
		if ($category != 0) {
			$category = " AND ffi_be_books.course = '" . $category . "'";
		} else {
			$category = "";
		}
		
	//Don't fetch expired books
		$exchangeSettings = $wpdb->get_results("SELECT * FROM ffi_be_exchangesettings WHERE id = '1'");
		
	//Is there a sort criteria?
		if (isset($_GET['sortBy'])) {
			$sortBy = Validate::required($_GET['sortBy'], array("titleASC", "titleDESC", "priceASC", "priceDESC", "authorASC", "authorDESC"));
			
			switch($sortBy) {
				case "titleASC" : 
					$sort = "ffi_be_books.title ASC, ffi_be_books.price ASC";
					break;
					
				case "titleDESC" : 
					$sort = "ffi_be_books.title DESC, ffi_be_books.price ASC";
					break;
					
				case "priceASC" : 
					$sort = "ffi_be_books.price ASC, ffi_be_books.title ASC";
					break;
					
					
				case "priceDESC" : 
					$sort = "ffi_be_books.price DESC, ffi_be_books.title ASC";
					break;
					
					
				case "authorASC" : 
					$sort = "ffi_be_books.author ASC, ffi_be_books.title ASC";
					break;
					
					
				case "authorDESC" : 
					$sort = "ffi_be_books.author DESC, ffi_be_books.title ASC";
					break;
					
				default : 
					$sort = "ffi_be_books.title ASC";
					break;
			}
		} else {
			$sort = "ffi_be_books.title ASC";
		}
		
	//Only display a given number of queries		
		if (isset($_GET['display'])) {
			$limit = Validate::numeric($_GET['display'], 1);
		} else {
			$limit = 25;
		}
		
		if (isset($_GET['pageLoc'])) {
			$start = $limit * (Validate::numeric($_GET['pageLoc'], 1) - 1);
		} else {
			$start = 0;
		}
		
	//Different search methods will vary the query that is executed on the database
		$now = strtotime("now");
		
		switch($searchBy) {
			case "title" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AS score, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY score DESC, " . $sort . " LIMIT " . $start . ", " . $limit);
				$lengthGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AS score, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(title) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY score DESC, " . $sort);
				break;
				
			case "author" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AS score, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY score DESC, " . $sort . " LIMIT " . $start . ", " . $limit);
				$lengthGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AS score, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE MATCH(author) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY score DESC, " . $sort);
				break;
				
			case "ISBN" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ISBN = '{$query}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort . " LIMIT " . $start . ", " . $limit);
				$lengthGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ISBN = '{$query}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort);
				break;
				
			case "course" : 
				$number = substr($query, strlen($query) - 5, strlen($query) - 2);
				$section = substr($query, strlen($query) - 1, strlen($query));
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE number = '{$number}' AND section = '{$section}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort . " LIMIT " . $start . ", " . $limit);
				$lengthGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection FROM ffi_be_books RIGHT JOIN (wp_users) ON ffi_be_books.userID = wp_users.ID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE number = '{$number}' AND section = '{$section}' AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort);
				break;
				
			case "seller" : 
				$searchGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, wp_users.user_email, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection, MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AS score FROM wp_users RIGHT JOIN (ffi_be_books) ON wp_users.ID = ffi_be_books.userID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON wp_users.ID WHERE MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort . " LIMIT " . $start . ", " . $limit);
				$lengthGrabber = $wpdb->get_results("SELECT ffi_be_books.*, ffi_be_exchangesettings.expires, wp_users.display_name, wp_users.user_email, GROUP_CONCAT(ffi_be_books.course) AS listedInID, GROUP_CONCAT(ffi_be_bookcategories.name) AS listedIn, GROUP_CONCAT(ffi_be_books.number) AS listedInNumber, GROUP_CONCAT(ffi_be_books.section) AS listedInSection, MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AS score FROM wp_users RIGHT JOIN (ffi_be_books) ON wp_users.ID = ffi_be_books.userID RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id RIGHT JOIN(ffi_be_exchangesettings) ON wp_users.ID WHERE MATCH(display_name) AGAINST('{$query}' IN BOOLEAN MODE) AND ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now}{$category} GROUP BY linkID ORDER BY " . $sort);
				break;
				
			default : 
				wp_redirect($essentials->friendlyURL("search"));
				exit;
				break;
		}
			
	//Is a paginator necessary?
		if (count($lengthGrabber)) {
			$length = count($lengthGrabber);
	//Or did the query fail altogether?
		} else {
			wp_redirect($essentials->friendlyURL("search/?message=none&query=" . $_GET['search'] . "&by=" . $_GET['searchBy']));
			exit;
		}
	} else if (isset($_GET['search']) && $_GET['search'] == "") {
		wp_redirect($essentials->friendlyURL("search"));
		exit;
	}
	
//Generate the breadcrumb
	//$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//";
	
//The breadcrumb and title will requrie some specialization depending if a search query is entered
	if (isset($_GET['search'])) {		
		if ($_GET['searchBy'] == "ISBN") {
			$title = "ISBN: " . urldecode($_GET['search']);
		} else {
			$title = urldecode($_GET['search']);
		}
		
	//	$breadcrumb .= "<li><a href=\"../search\">Search</a></li>
//<li>" . $title . "</li>\n";
	} else {
	//	$breadcrumb .= "<li>Search</li>\n";
		$title = "Search";
	}
	
	echo "<section class=\"body\">
";
	
//Display a page header
	if (isset($_GET['search'])) {
	//Is the sidebar present?
		if (!isset($_GET['options']) || (!isset($_GET['options']) && $_GET['options'] != "false")) {
			$class = "";
		} else {
			$class = " noSide";
		}
		
	//Properly format the results string
		$length = count($lengthGrabber);
	
		if ($length == 1) {
			$total = "1 Result";
		} else {
			$total = $length . " Results";
		}
		
		echo "<header class=\"styled search" . $class . "\">
<h1>" . stripslashes($title) . "</h1>
<h2>" . $total . "</h2>
</header>

";
	}
	
	$essentials->setTitle(stripslashes($title));

//Display the results of the search...
	if (isset($_GET['search'])) {
	//Display the tools sidebar, if the options are turn on
		if (!isset($_GET['options']) || (!isset($_GET['options']) && $_GET['options'] != "false")) {
			echo "<aside class=\"tools\">
<section class=\"options\">
<form action=\"" . $essentials->friendlyURL("search") . "\" method=\"get\">
<h2>Search for Books:</h2>
<input autocomplete=\"off\" class=\"search full\" name=\"search\" type=\"text\" value=\"" . htmlentities($_GET['search']) . "\" />

<div class=\"controls\">
<span class=\"step\">Search by:</span>
<ul class=\"dropdown\" data-name=\"searchBy\">
<li" . ($_GET['searchBy'] == "title" ? " class=\"selected\"" : "") . " data-value=\"title\">Title</li>
<li" . ($_GET['searchBy'] == "author" ? " class=\"selected\"" : "") . " data-value=\"author\">Author</li>
<li" . ($_GET['searchBy'] == "ISBN" ? " class=\"selected\"" : "") . ">ISBN</li>
<li" . ($_GET['searchBy'] == "course" ? " class=\"selected\"" : "") . " data-value=\"course\">Course</li>
<li" . ($_GET['searchBy'] == "seller" ? " class=\"selected\"" : "") . " data-value=\"seller\">Seller</li>
</ul>

<br>

<span class=\"step\">Sort by:</span>
<ul class=\"dropdown\" data-name=\"sortBy\">
<li" . ((isset($_GET['sortBy']) && $_GET['sortBy'] == "titleASC") || !isset($_GET['sortBy']) ? " class=\"selected\"" : "") . " data-value=\"titleASC\">Title A-Z</li>
<li" . (isset($_GET['sortBy']) && $_GET['sortBy'] == "titleDESC" ? " class=\"selected\"" : "") . " data-value=\"titleDESC\">Title Z-A</li>
<li" . (isset($_GET['sortBy']) && $_GET['sortBy'] == "priceASC" ? " class=\"selected\"" : "") . " data-value=\"priceASC\">Price Low to High</li>
<li" . (isset($_GET['sortBy']) && $_GET['sortBy'] == "priceDESC" ? " class=\"selected\"" : "") . " data-value=\"priceDESC\">Price High to Low</li>
<li" . (isset($_GET['sortBy']) && $_GET['sortBy'] == "authorASC" ? " class=\"selected\"" : "") . " data-value=\"authorASC\">Author A-Z</li>
<li" . (isset($_GET['sortBy']) && $_GET['sortBy'] == "authorDESC" ? " class=\"selected\"" : "") . " data-value=\"authorDESC\">Author Z-A</li>
</ul>

<br>

<span class=\"step\">Display:</span>
<ul class=\"dropdown\" data-name=\"display\">
<li" . (isset($_GET['display']) && $_GET['display'] == "10" || (isset($_GET['display']) && $_GET['display'] != "10" && $_GET['display'] != "25" && $_GET['display'] != "50" && $_GET['display'] != "75" && $_GET['display'] != "100") ? " class=\"selected\"" : "") . " data-value=\"10\">10 Results</li>
<li" . ((isset($_GET['display']) && $_GET['display'] == "25") || !isset($_GET['display']) ? " class=\"selected\"" : "") . " data-value=\"25\">25 Results</li>
<li" . (isset($_GET['display']) && $_GET['display'] == "50" ? " class=\"selected\"" : "") . " data-value=\"50\">50 Results</li>
<li" . (isset($_GET['display']) && $_GET['display'] == "75" ? " class=\"selected\"" : "") . " data-value=\"75\">75 Results</li>
<li" . (isset($_GET['display']) && $_GET['display'] == "100" ? " class=\"selected\"" : "") . " data-value=\"100\">100 Results</li>
</ul>

<br>

<span class=\"step category\">Search in:</span>
<div class=\"menuWrapper\">
<input name=\"category\" type=\"hidden\" value=\"" . $_GET['category'] . "\" />

<ul class=\"categoryFly\">";
	
	//Generate the category dropdown menu
		$categoryGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
		$counter = 1;
	
		foreach($categoryGrabber as $category) {
		//Break up this "dropdown" list into columns every 10 items
			if ($counter % 10 == 1) {
			//Include an "all" menu item if this is the first item
				if ($counter == 1) {
					echo "
<li>
<ul>
";
					
				//Should the "All Categories" be selected?
					if (!isset($_GET['category']) || (isset($_GET['category']) && $_GET['category'] == '0')) {
						echo "<li class=\"all selected\" data-value=\"0\"><span class=\"band\" style=\"border-left-color: #FFFFFF;\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/icons/all.png") . "');\">All Disciplines</span></span></li>";
					} else {
						echo "<li class=\"all\" data-value=\"0\"><span class=\"band\" style=\"border-left-color: #FFFFFF;\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/icons/all.png") . "');\">All Disciplines</span></span></li>";
					}
	
				//Since we inserted a "free" item, add one to the counter
					$counter++;
				} else {
					echo "
<li>
<ul>";
				}
			}
			
		//Should this category be selected?
			if (isset($_GET['category']) && $_GET['category'] == $category->id) {
				echo "
<li class=\"selected\" data-value=\"" . $category->id . "\"><span class=\"band\" style=\"border-left-color: " . stripslashes($category->color1) . ";\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/categories/" . $category->id . "/icon_032.png") . "');\">" . stripslashes($category->name) . "</span></span></li>";
			} else {
				echo "
<li data-value=\"" . $category->id . "\"><span class=\"band\" style=\"border-left-color: " . stripslashes($category->color1) . ";\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/categories/" . $category->id . "/icon_032.png") . "');\">" . stripslashes($category->name) . "</span></span></li>";
			}
	
			if ($counter % 10 == 0) {
				echo "
</ul>
</li>
";
			}
	
			$counter++;
		}
		
		echo "</ul>
</div>
</div>

<input class=\"blue submit\" type=\"submit\" value=\"Search\" />
</form>
</section>

";
	
	//Display a list of other categories that the user can browse
			$allCatGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
			
			echo "<section class=\"categories\">
<h2 style=\"color:" . stripslashes($category->color1) . "\">More Book Listings</h2>
<ul class=\"moreListings\">";
			
			foreach($allCatGrabber as $allCat) {
				echo "
<li><a href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $allCat->id) . "\">" . stripslashes($allCat->name) . " <span class=\"arrow\">&raquo;</span></a></li>";
			}
			
			echo "
</ul>
</section>
</aside>

";
		}
		
	//Is the sidebar present?
		if (!isset($_GET['options']) || (!isset($_GET['options']) && $_GET['options'] != "false")) {
			$class = "";
		} else {
			$class = " noSide";
		}
		
	//If we are searching by seller, display the seller's profile at top
		if ($_GET['searchBy'] == "seller" && is_user_logged_in() && (!isset($_GET['pageLoc']) || (isset($_GET['pageLoc']) && $_GET['pageLoc'] == "1"))) {
		//Yes, we did use $lengthGrabber for finding the number of results, but let's steal it here
			$seller = $lengthGrabber[0];
			
			echo "<section class=\"profile" . $class . "\">
<h2>" . stripslashes($seller->display_name) . "'s Profile</h2>
<span class=\"row\"><strong>Name:</strong> " . stripslashes($seller->display_name) . "</a></span>
<span class=\"row\"><strong>Email address:</strong> <a href=\"mailto:" . stripslashes($seller->user_email) . "\">" . stripslashes($seller->user_email) . "</a></span>
</section>

";
		}
		
	//Display the search results
		echo "<section class=\"results" . $class . "\">
<ul>";
		
		foreach($searchGrabber as $search) {
			echo "
<li class=\"result\">
<a href=\"" . $essentials->friendlyURL("book-details/?id=" . $search->id) . "\"><img src=\"" . stripslashes($search->imageURL) . "\" /></a>
<a class=\"title\" href=\"" . $essentials->friendlyURL("book-details/?id=" . $search->id) . "\" title=\"" . htmlentities(stripslashes($search->title)) . "\">" . stripslashes($search->title) . "</a>
<span class=\"details\"><strong>Author:</strong> <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($search->author)) . "&searchBy=author&category=0") . "\">" . stripslashes($search->author) . "</a></span>
<span class=\"details\"><strong>Seller:</strong> <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($search->display_name)) . "&searchBy=seller&category=0") . "\">" . stripslashes($search->display_name) . "</a></span>
<span class=\"details\"><strong>ISBN:</strong> <a href=\"" . $essentials->friendlyURL("search/?search=" . urlencode(stripslashes($search->ISBN)) . "&searchBy=ISBN&category=0") . "\">" . stripslashes($search->ISBN) . "</a></span>
";
			
		//Conditionally format the condition of the book
			switch($search->condition) {
				case "Excellent" : 
					echo "<span class=\"excellent\">Excellent Condition</span>";
					break;
					
				case "Very Good" : 
					echo "<span class=\"veryGood\">Very Good Condition</span>";
					break;
					
				case "Good" : 
					echo "<span class=\"good\">Good Condition</span>";
					break;
					
				case "Fair" : 
					echo "<span class=\"fair\">Fair Condition</span>";
					break;
					
				case "Poor" : 
					echo "<span class=\"poor\">Poor Condition</span>";
					break;
			}
			
		//Generate the list of classes that are listed for this book
			$classIDs = explode(",", stripslashes($search->listedInID));
			$classes = explode(",", stripslashes($search->listedIn));
			$classNums = explode(",", stripslashes($search->listedInNumber));
			$classSections = explode(",", stripslashes($search->listedInSection));
			
			echo "

<ul class=\"classes\">
<li><span class=\"directions\">Classes used:</span></li>";
			
			for ($i = 0; $i <= sizeof($classIDs) - 1; $i++) {
				echo "
<li>
<img src=\"" . $essentials->normalizeURL("system/images/categories/" . $classIDs[$i] . "/icon_032.png") . "\" title=\"" . htmlentities(stripslashes($classes[$i])) . "\" />
<span class=\"courseDetails\">" . stripslashes($classNums[$i]) . " " . stripslashes($classSections[$i]) . "</span>
</li>
";
			}
			
			if (is_user_logged_in() && $search->userID == $essentials->user->ID) {
				$buy = " noBuy";
			} else {
				$buy = " buy";
			}
			
			echo "</ul>
			
<a class=\"buttonLink" . $buy . "\" data-fetch=\"" . $search->id . "\" href=\"javascript:;\"><span>\$" . stripslashes($search->price) . "</span></a>
</li>
";
		}
		
		echo "</ul>
";
		
	//Display a paginator, if needed
		if ($limit <= $length) {
		//The maxmium number of pages to list in the paginator at once, only works with odd numbers
			$paginatorMax = 9;
			
		//Calculate the number of needed pages
			$pagesNeeded = ceil($length / $limit);
			
		//The current page information will need validated
			if (isset($_GET['pageLoc']) && is_numeric($_GET['pageLoc']) && $_GET['pageLoc'] <= $pagesNeeded) {
				$currentPage = $_GET['pageLoc'];
			} else if (isset($_GET['pageLoc']) && (!is_numeric($_GET['pageLoc']) && $_GET['pageLoc'] > $pagesNeeded)) {
				$currentPage = 1;
			} else {
				$currentPage = 1;
			}
			
		//Generate the base URL for each of the pagination links
			$baseURL = "search/";
			$baseURL .= "?search=" . urlencode(urldecode($_GET['search']));
			$baseURL .= "&searchBy=" . urlencode(urldecode($_GET['searchBy']));
			$baseURL .= "&category=" . urlencode(urldecode($_GET['category']));
			
			if (isset($_GET['display'])) {
				$baseURL .= "&display=" . urlencode(urldecode($_GET['display']));
			}
			
			if (isset($_GET['sortBy'])) {
				$baseURL .= "&sortBy=" . urlencode(urldecode($_GET['sortBy']));
			}
			
			if (isset($_GET['options'])) {
				$baseURL .= "&options=" . urlencode(urldecode($_GET['options']));
			}
			
			echo "
<ul class=\"pagination\">";
			
		//Can a back button be displayed?
			if ($currentPage != 1) {
				echo "
<li class=\"back\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . ($currentPage - 1)) . "\"></a></li>";
			}
				
		/**
		 * Calculuate the lower bound
		 * 
		 * This will visually balance the number of pages around the current page
		 * as the user goes higher and higher in the list. For example, if we are
		 * displaying 11 pages at a time, the user will see (26 is selected):
		 * 
		 *  1 ... 21 22 23 24 25 *26* 27 28 29 30 31 ... 100
		 *        |____________|      |____________|
		 *              |                   |
		 *           5 items + 1 item +  5 items = 11 items
		*/
			if ($paginatorMax % 2 == 1) { //Is the number odd?
				$originalPaginator = $paginatorMax; // Just make a copy, if we need it
				$paginatorMax --; //This is needed so we can calculate
			} else {
				$originalPaginator = $paginatorMax;
			}
			
			$centerBalance = $paginatorMax / 2;
			$minOutput = $currentPage - $centerBalance;
			
			if ($minOutput < 1) {
				$minOutput = 1;
			}
			
		//Calculate the upper bound
			$maxOutput = $currentPage + $centerBalance;
			
			if ($maxOutput > $pagesNeeded) {
				$maxOutput = $pagesNeeded;
			}
		/**
		 * Now for some last minute checks
		 * 
		 * Are we actually displaying all of the pages that $paginatorMax told us
		 * to display? Or is there a problem like this near the beginning and end:
		 * 
		 *   $paginatorMax = 9
		 *   
		 *   1 2 3 4 *5* ... 20 >
		 *   < 1 ... 16 17 18 19 *20*
		 *   
		 * Do some last minute calculations to adjust this problem.
		 */
			
			if ($maxOutput - $minOutput < $paginatorMax) {
			//Should more be added to the beginning?
				if ($maxOutput + ($maxOutput - $minOutput) >= $paginatorMax) {
					$minOutput -= $minOutput - ($maxOutput - $paginatorMax);
					
				//This must be greater than 0
					if ($minOutput < 1) {
						$minOutput = 1;
					}
				}
				
			//Should more be added to the end?
				if ($minOutput + ($maxOutput - $minOutput) <= $paginatorMax) {
					$maxOutput += $originalPaginator - $maxOutput;
					
				//This must be greater than $pagesNeeded
					if ($maxOutput > $pagesNeeded) {
						$maxOutput = $pagesNeeded;
					}
				}
			}
			
		//Were there beginnning pages that the paginator didn't print out to conserve space? Print the first page, if so.
			if ($minOutput != $pagesNeeded && $minOutput != 1) {
			//Don't display something like 1 ... 2
				if ($minOutput - 1 == 1) {
					echo "
<li class=\"noDot\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=1") . "\">1</a></li>";
			//Don't display something like 1 ... 3, just print 1 2 3
				} else if ($minOutput - 2 == 1) {
					echo "
<li class=\"noDot\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=1") . "\">1</a></li>
<li><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=2") . "\">2</a></li>";
				} else {
					echo "
<li class=\"noDot\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=1") . "\">1</a></li>
<li class=\"noDot more\">&hellip;</li>";
				}
			}
			
		//Print out the pagination list
			for ($i = $minOutput; $i <= $maxOutput; $i++) {
				$class = "";
				
			//Highlight the current page
				if ($i == $currentPage) {
					$class = " class=\"current\"";
				}
				
			/**
			 * Some page links won't need a seperator, such as the first one...
			 * 
			 * The last two conditions offset the logic for the codeblock above this 
			 * loop. Search for:
			 * 
			 *   //Don't display something like 1 ... 2
			 *   //Don't display something like 1 ... 3, just print 1 2 3
			 *  
			 * to see where these initial conditons are created.
			 */
				if ($i == $minOutput && $i - 1 != 1 && $i - 2 != 1) {
					$class = " class=\"noDot\"";
				}
				
				if ($i == $minOutput && $i == $currentPage) {
					$class = " class=\"current noDot\"";
				}
			
				
			//Display the list item
				echo "
<li" . $class . "><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . $i) . "\">" . $i . "</a></li>";
			}
			
		//Were there extra pages that the paginator didn't print out to conserve space? Print the last page, if so.
			if ($maxOutput < $pagesNeeded) {
			//Don't display something like 19 ... 20
				if ($maxOutput + 1 == $pagesNeeded) {
					echo "
<li><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . $pagesNeeded) . "\">" . $pagesNeeded . "</a></li>";
			//Don't display something like 18 ... 20, just print 18 19 20
				} else if ($maxOutput + 2 == $pagesNeeded) {
					echo "
<li><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . ($pagesNeeded - 1)) . "\">" . ($pagesNeeded - 1) . "</a></li>
<li><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . $pagesNeeded) . "\">" . $pagesNeeded . "</a></li>";
				} else {
					echo "
<li class=\"noDot more\">&hellip;</li>
<li class=\"noDot\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . $pagesNeeded) . "\">" . $pagesNeeded . "</a></li>";
				}
			}
			
		//Can a forward button be displayed?
			if ($currentPage + 1 <= $pagesNeeded) {
				echo "
<li class=\"forward\"><a href=\"" . $essentials->friendlyURL($baseURL . "&pageLoc=" . ($currentPage + 1)) . "\"></a></li>";
			}
			
			echo "
</ul>
";
		}	
		
		echo "</section>";
//... or show a search form
	} else {
	//Was the user redirected back here because of an error?
		if (isset($_GET['message']) && $_GET['message'] == "none") {
			echo "<div class=\"center\"><div class=\"error\">Sorry we couldn't find any results for <strong>" . urldecode($_GET['query']) . "</strong> when searching by <strong>" . urldecode($_GET['by']) . "</strong>. Did you enter it correctly?</div></div>
			
	";
		}
		
		echo "<section class=\"searchForm\">
<div class=\"mask\">
<form action=\".\" method=\"get\">
<h2 class=\"search\">Search for Books:</h2>
<input autocomplete=\"off\" class=\"search full\" name=\"search\" type=\"text\" />
<span class=\"expand\">Advanced Search Options</span>

<div class=\"controls hidden\">
<span class=\"searchStep\">Search by:</span>
<ul class=\"dropdown\" data-name=\"searchBy\">
<li class=\"selected\" data-value=\"title\">Title</li>
<li data-value=\"author\">Author</li>
<li>ISBN</li>
<li data-value=\"course\">Course</li>
<li data-value=\"seller\">Seller</li>
</ul>

<br>

<div class=\"menuWrapper\">
<input name=\"category\" type=\"hidden\" value=\"0\" />

<ul class=\"categoryFly\">";

//Generate the category dropdown menu
	$categoryGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
	$counter = 1;
	
	foreach($categoryGrabber as $category) {
	//Break up this "dropdown" list into columns every 10 items
		if ($counter % 10 == 1) {
		//Include an "all" menu item if this is the first item
			if ($counter == 1) {
				echo "
<li>
<ul>
<li class=\"all selected\" data-value=\"0\"><span class=\"band\" style=\"border-left-color: #FFFFFF;\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/icons/all.png") . "');\">All Disciplines</span></span></li>";

			//Since we inserted a "free" item, add one to the counter
				$counter++;
			} else {
				echo "
<li>
<ul>";
			}
		}
		
		echo "
<li data-value=\"" . $category->id . "\"><span class=\"band\" style=\"border-left-color: " . stripslashes($category->color1) . ";\"><span class=\"icon\" style=\"background-image: url('" . $essentials->normalizeURL("system/images/categories/" . $category->id . "/icon_032.png") . "');\">" . stripslashes($category->name) . "</span></span></li>";

		if ($counter % 10 == 0) {
			echo "
</ul>
</li>
";
		}

		$counter++;
	}
	
	echo "</ul>
</div>
</div>

<input class=\"blue submit\" type=\"submit\" value=\"Search\" />
</form>
</div>

<img class=\"animatedSearch\" src=\"" . $essentials->normalizeURL("system/images/icons/search.png") . "\" />
</section>

<img class=\"shadow\" src=\"" . $essentials->normalizeURL("system/images/welcome/paper_shadow.png") . "\" />";
	}
	
//Include the footer from the administration template
	echo "
</section>";
?>