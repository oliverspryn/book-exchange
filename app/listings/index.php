<?php
//Include the system's core
	$essentials->setTitle("All Book Listings");
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/listings.css");
	
//Generate the breadcrumb
	//$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//<li>All Books Listings</li>\n";
	
//Grab the categories from the database and count the total number of books for sale
	if (count($wpdb->get_results("SELECT * FROM ffi_be_bookcategories"))) {
		$notEmpty= array();
		$total = 0;
		$now = strtotime("now");
		$categories = true;
		
	//Count the total number of books in a category
		$notEmptyGrabber = $wpdb->get_results("SELECT ffi_be_exchangesettings.expires, ffi_be_bookcategories.*, COUNT(DISTINCT ffi_be_books.linkID) as total FROM `ffi_be_bookcategories` LEFT JOIN (ffi_be_books) ON ffi_be_bookcategories.id = ffi_be_books.course RIGHT JOIN(ffi_be_exchangesettings) ON ffi_be_books.id WHERE ffi_be_books.sold = '0' AND ffi_be_books.userID != '0' AND ffi_be_books.upload + ffi_be_exchangesettings.expires > {$now} GROUP BY ffi_be_bookcategories.name ORDER BY name ASC");
		$categoryGrabber = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
		
		foreach($notEmptyGrabber as $notEmptyArray) {
			$notEmpty[$notEmptyArray->id] = $notEmptyArray->total;
			$total += $notEmptyArray->total;
		}
	} else {
		$categories = false;
	}

//Include the top of the page from the administration template
	echo "<section class=\"body\">
";

//Include the page header
	echo "<header class=\"styled all\">
<h1>All Book Listings</h1>
";
	
	if ($total == 1) {
		echo "<h2>1 Book for Sale</h2>
</header>
";
	} else {
			echo "<h2>" . $total . " Books for Sale</h2>
</header>
";
	}
	
//Display the categories
	if ($categories) {
		echo "<ul class=\"listing\">";
		
		foreach($categoryGrabber as $category) {
			echo "
<li>
<a href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $category->id) . "\"><img src=\"" . $essentials->normalizeURL("system/images/categories/" . $category->id . "/icon_128.png") . "\" /></a>
<a href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $category->id) . "\" class=\"title\">" . stripslashes($category->name) . "</a>
<span class=\"description\">" . stripslashes($category->description) . "</span>
";
	
			if (isset($notEmpty[$category->id])) {
				switch($notEmpty[$category->id]) {
					case "1" : 
						echo "<a class=\"buttonLink\" href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $category->id) . "\"><span>Browse 1 Book</span></a>
</li>";
						break;
						
					default : 
						echo "<a class=\"buttonLink\" href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $category->id) . "\"><span>Browse " . $notEmpty[$category->id] . " Books</span></a>
</li>";
						break;
				}
			} else {
				echo "<a class=\"buttonLink\" href=\"" . $essentials->friendlyURL("listings/view-listing.php?id=" . $category->id) . "\"><span>No Books Avaliable... yet</span></a>
</li>";
			}
		}
		
		echo "
</ul>";
	} else {
		$categories = false;
	}

//Include the footer from the public template
	echo "
</section>";
?>