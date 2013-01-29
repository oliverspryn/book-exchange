<?php
//Verify that the user is logged in
	$essentials->requireLogin();
	$essentials->setTitle("Sell Books");
	$essentials->includeCSS("system/stylesheets/style.css");
	$essentials->includeCSS("system/stylesheets/sell.css");
	$essentials->includeCSS("system/stylesheets/validationEngine.jquery.min.css");
	$essentials->includeJS("system/javascripts/interface.js");
	$essentials->includeJS("system/javascripts/sell_wizard.min.js");
	$essentials->includeJS("//cdn.jquerytools.org/1.2.7/tiny/jquery.tools.min.js");
	$essentials->includeJS("system/tiny_mce/tiny_mce.js");
	$essentials->includeJS("system/tiny_mce/jquery.tinymce.js");
	$essentials->includeJS("system/javascripts/tiny_mce_simple.php");
	$essentials->includeJS("system/javascripts/jquery.validationEngine.min.js");
	
//Is the user editing a book?
	if (isset($_GET['id'])) {
		$editing = true;
		$bookData = $wpdb->get_results("SELECT ffi_be_books.*, GROUP_CONCAT(ffi_be_books.id) AS bookIDs, GROUP_CONCAT(ffi_be_books.course) AS classIDs, GROUP_CONCAT(ffi_be_books.number) AS classNums, GROUP_CONCAT(ffi_be_books.section) AS classSec FROM ffi_be_books RIGHT JOIN (ffi_be_bookcategories) ON ffi_be_books.course = ffi_be_bookcategories.id WHERE ffi_be_books.linkID = (SELECT linkID FROM ffi_be_books WHERE id = '{$_GET['id']}' LIMIT 1) AND ffi_be_books.userID = '{$essentials->user->ID}' GROUP BY ffi_be_books.linkID ORDER BY ffi_be_books.course ASC, ffi_be_books.number ASC, ffi_be_books.section ASC");
		
		if ($bookData && count($bookData)) {
			$bookData = $bookData[0];
			
		//Users cannot edit books that don't belong to them!
			if ($bookData->userID != $essentials->user->ID) {
				wp_redirect("../");
			}
		} else {
			wp_redirect("../");
		}
	} else {
		$editing = false;
	}
	
//Generate the breadcrumb
	//$home = mysql_fetch_array(mysql_query("SELECT * FROM pages WHERE position = '1' AND `published` != '0'", $connDBA));
	//$title = unserialize($home['content' . $home['display']]);
	//$breadcrumb = "\n<li><a href=\"" . $root . "index.php?page=" . $home['id'] . "\">" . stripslashes($title['title']) . "</a></li>
//<li><a href=\"../\">Book Exchange</a></li>
//<li>Sell Books</li>\n";
	
/**
 * Process the form
 *
 * This information will likely need to be processed several times,
 * in order to allow a given book to be searchable for each class
 * that it belongs.
 *
 * There is a set of information which will remain constant for
 * each of the processes (represented by the book image and steps
 * one and three in the form) and there is a set which will change 
 * for each process (represented by step two in the form).
 *
 * The data which will be processed several times will contain 
 * information which will need to be discarded. The first piece of
 * data from the class name and section is unneeded, as they only
 * served as they were only templates of dynmaic datafor jQuery to 
 * copy whenever a new row was added to the class table. They were
 * never filled out by the user, and only served technical purposes.
 *
 * The first values for className and classSec will be discarded.
*/
	/*
//Insert a new book
	if (isset($_POST) && !empty($_POST) && is_array($_POST) && isset($_POST['ISBN'])) {
	//Generate the server-side data needed to store a book
		$userID = $userData['id'];
		$upload = strtotime("now");
		$linkID = md5($upload . "_" . $userID);
		
	//Validate the constant data provided by the user
		$ISBN = preg_replace('/[^0-9a-zA-Z]/', '', $_POST['ISBN']);
		
		if (strlen($ISBN) == 10 || strlen($ISBN) == 13) {
			//Do nothing
		} else {
			redirect("../sell-books/");
		}
		
	//Has an image ID been specified? If so, see if the image link ID has been approved for immediate publication
		if (!empty($_POST['imageID'])) {
			$imageTestGrabber = mysql_query("SELECT * FROM books WHERE imageID = '{$_POST['imageID']}' AND awaitingImage = ''", $connDBA);
			
		//Another book of the same ISBN has been verified
			if ($imageTestGrabber && mysql_num_rows($imageTestGrabber)) {
				$imageTest = mysql_fetch_assoc($imageTestGrabber);
				
				$imageID = $_POST['imageID'];
				$imageURL = mysql_real_escape_string($imageTest['imageURL']);
				$awaitingImage = "";
		//No other book with this ISBN has been approved
			} else {
				$imageID = $_POST['imageID'];
				$imageURL = mysql_real_escape_string("/book-exchange/system/images/icons/default_book.png");
				$awaitingImage = mysql_real_escape_string(Validate::required($_POST['imageURL']));
			}
		} else {
			$imageID = md5($userID . "_" . $upload);
			$imageURL = mysql_real_escape_string("/book-exchange/system/images/icons/default_book.png");
			$awaitingImage = mysql_real_escape_string(Validate::required($_POST['imageURL']));
		}
		
		$ISBN = mysql_real_escape_string($ISBN);
		$title = mysql_real_escape_string(Validate::required($_POST['title']));
		$author = mysql_real_escape_string(Validate::required($_POST['author']));
		$edition = mysql_real_escape_string($_POST['edition']);
		$price = mysql_real_escape_string(Validate::numeric($_POST['price'], 0, 999.99));
		$condition = mysql_real_escape_string(Validate::required($_POST['condition'], array("Excellent", "Very Good", "Good", "Fair", "Poor")));
		$written = mysql_real_escape_string(Validate::required($_POST['written'], array("Yes", "No")));
		$comments = mysql_real_escape_string($_POST['comments']);
		
	//Insert a new book
		if (!$editing) {
		//Process this data multiple times, see above description for more details
			for ($i = 0; $i <= sizeof($_POST['classNum']) - 1; $i ++) {
			//Validate the dynamic data provided by the user
				$className = mysql_real_escape_string(Validate::required($_POST['className'][$i + 1]));
				$classNum = mysql_real_escape_string(Validate::numeric($_POST['classNum'][$i], 101, 499));
				$classSec = mysql_real_escape_string(Validate::required($_POST['classSec'][$i + 1], false, false, false, 1));
				
			//Execute the data on the database
				mysql_query("INSERT INTO books (
						`id`, `userID`, `upload`, `sold`, `linkID`, `ISBN`, `title`, `author`, `edition`, `course`, `number`, `section`, `price`, `condition`, `written`, `comments`, `imageURL`, `awaitingImage`, `imageID`
					 ) VALUES (
						NULL, '{$userID}', '{$upload}', '0', '{$linkID}', '{$ISBN}', '{$title}', '{$author}', '{$edition}', '{$className}', '{$classNum}', '{$classSec}', '{$price}', '{$condition}', '{$written}', '{$comments}', '{$imageURL}', '{$awaitingImage}', '{$imageID}'
					 )", $connDBA);
			}
			
		//Determine where to redirect the user
			if ($awaitingImage != "") {
				$extra = "&approval=true";
			} else {
				$extra = "";
			}
			
			if ($_POST['redirect'] == "1") {
				redirect("../sell-books/?message=added" . $extra);
			} else {
				$id = mysql_insert_id();
				
				redirect("../account/?message=added" . $extra . "#book_" . $id);
			}
		} else {
		/**
		 * Since books that are used in multiple classes are stored across multiple classes,
		 * we need to check if the *number* of classes in this update is the same *number*
		 * classes that were listed as before.
		 * 
		 * If there more classes in the update, just added some more rows into the database.
		 * If there are less classes in the update, then remove a few rows.
		*/
			/*
		//Are more or less rows needed?
			$oldClasses = explode(",", $bookData['bookIDs']);
			$action = "none";
			
		//Remove some rows
			if (sizeof($oldClasses) > sizeof($_POST['classNum'])) {
				$action = "removed";
				$difference = sizeof($oldClasses) - sizeof($_POST['classNum']);
				$oldClasses = array_merge(array_reverse($oldClasses)); //Let's delete the classes with the higher ID
				$IDs = "";
			
				for($i = 0; $i <= $difference - 1; $i ++) {
					$IDs .= " OR id = '" . $oldClasses[$i] . "'";
				}
				
				$IDs = ltrim($IDs, " OR ");				
				mysql_query("DELETE FROM books WHERE " . $IDs, $connDBA);
			}
			
		//Add some rows
			if (sizeof($oldClasses) < sizeof($_POST['classNum'])) {
				$action = "added";
				$difference = sizeof($_POST['classNum']) - sizeof($oldClasses);
				$start = sizeof($_POST['classNum']) - $difference + 1; //Don't touch old rows
				$end = sizeof($_POST['classNum']);
				
			//Add only the new rows into the database, and subtract 1 since we are working with arrays
				for ($i = $start - 1; $i <= $end - 1; $i ++) {
				//Validate the dynamic data provided by the user
					$className = mysql_real_escape_string(Validate::required($_POST['className'][$i + 1]));
					$classNum = mysql_real_escape_string(Validate::numeric($_POST['classNum'][$i], 101, 499));
					$classSec = mysql_real_escape_string(Validate::required($_POST['classSec'][$i + 1], false, false, false, 1));
					
				//Execute the data on the database
					mysql_query("INSERT INTO books (
							`id`, `userID`, `upload`, `sold`, `linkID`, `ISBN`, `title`, `author`, `edition`, `course`, `number`, `section`, `price`, `condition`, `written`, `comments`, `imageURL`, `awaitingImage`, `imageID`
						 ) VALUES (
							NULL, '{$bookData['userID']}', '{$bookData['upload']}', '0', '{$bookData['linkID']}', '{$ISBN}', '{$title}', '{$author}', '{$edition}', '{$className}', '{$classNum}', '{$classSec}', '{$price}', '{$condition}', '{$written}', '{$comments}', '{$imageURL}', '{$awaitingImage}', '{$imageID}'
						 )", $connDBA);
				}
			}
			
			$oldClasses = explode(",", $bookData['bookIDs']);
			
		//Process this data multiple times, see above description for more details
			for ($i = 0; $i <= sizeof($_POST['classNum']) - 1; $i ++) {
			//Validate the dynamic data provided by the user
				$className = mysql_real_escape_string(Validate::required($_POST['className'][$i + 1]));
				$classNum = mysql_real_escape_string(Validate::numeric($_POST['classNum'][$i], 101, 499));
				$classSec = mysql_real_escape_string(Validate::required($_POST['classSec'][$i + 1], false, false, false, 1));
			
			//Execute the data on the database
				mysql_query("UPDATE books SET ISBN = '{$ISBN}', title = '{$title}', author = '{$author}', edition = '{$edition}', course = '{$className}', number = '{$classNum}', section = '{$classSec}', price = '{$price}', `condition` = '{$condition}', written = '{$written}', comments = '{$comments}', imageURL = '{$imageURL}', awaitingImage = '{$awaitingImage}', imageID = '{$imageID}' WHERE id = '{$oldClasses[$i]}'", $connDBA);
			}
			
			redirect("../account/?message=edited#book_" . $bookData['id']);
		}
	}
	*/

//Display any needed success messages
	if (isset($_GET['message']) && $_GET['message'] == "added") {
		if (isset($_GET['approval'])) {
			$extra = ". The book's cover must be approved before it will display.";
		} else {
			$extra = "";
		}
		
		echo "<div class=\"center\"><div class=\"success\">Your book is now up for sale" . $extra . "</div></div>
		
";
	}

//Display the page header
	echo "<section class=\"body\">
<form action=\"" . $_SERVER['REQUEST_URI'] . "\" method=\"post\">
<header class=\"styled sell\"><h1>Sell Your Books</h1></header>

<aside class=\"preview\">
";
	
//Determine where the actual book URL is located, not the placeholder URL
	if ($editing) {
		if (!empty($bookData->awaitingImage)) {
			$imageURL = stripslashes($bookData->awaitingImage);
		} else {
			$imageURL = stripslashes($bookData->imageURL);
		}
	}
	
//Determine how the edition string should display
	if ($editing) {
		if (!empty($bookData->edition)) {
			$edition = "<span class=\"editionPreview details\"><strong>Edition:</strong> " . stripslashes($bookData->edition) . "</span>";
		} else {
			$edition = "<span class=\"editionPreview details\" style=\"display: none;\"><strong>Edition:</strong> </span>";
		}
	} else {
		$edition = "<span class=\"editionPreview details\" style=\"display: none;\"><strong>Edition:</strong> </span>";
	}
	
//Include a book preview box, the double <div> around the text input is a lazy fix for a positioning bug in the jQuery validator
	echo "<section class=\"bookPreview\">
<div style=\"height: 0px;\"><div><input class=\"imageURL noMod collapse validate[required,funcCall[checkImage]]\" name=\"imageURL\" type=\"text\"" . ($editing ? " value=\"" . htmlentities($imageURL) . "\"" : "") . " /></div></div>
<input class=\"imageID\" name=\"imageID\" type=\"hidden\"" . ($editing ? " value=\"" . htmlentities($bookData->imageID) . "\"" : "") . " />

<div class=\"imageContainer\">
" . ($editing ? "<img src=\"" . $imageURL . "\" />" : "<p>Enter the book's ISBN and we'll show the book cover here</p>") . "
</div>

<div class=\"imageBrowser hidden\">
<span class=\"back disabled\"></span>
<span class=\"forward\"></span>
</div>

<span class=\"titlePreview\">" . ($editing ? stripslashes($bookData->title) : "&lt;Book Title&gt;") . "</span>
<span class=\"authorPreview details\"><strong>Author:</strong> " . ($editing ? stripslashes($bookData->author) : "&lt;Book Author&gt;") . "</span>
<span class=\"details\"><strong>Seller:</strong> " . stripslashes($essentials->user->first_name) . " " . stripslashes($essentials->user->last_name) . "</span>
" . $edition . "
<br>
<span class=\"buttonLink big pricePreview\"><span>\$" . ($editing ? stripslashes($bookData->price) : "0.00") . "</span></span>
</section>

";

//Include an instructions section for tips on selling and pricing a book
	echo "<section class=\"pricing hints\">
<h2>Pricing Your Book</h2>

<ul>
<li>How much are others selling your book for?</li>
<li>Is this book the current edition?</li>
<li>How good of condition is this book?</li>
<li>Did you get it new or used?</li>
<li>How much did you buy it for?</li>
</ul>
</section>";

	echo "
</aside>

<section class=\"sell\">
";

//Include the book's information section
	echo "<section class=\"bookInformationSection\">
<h2>Enter the book's information</h2>

<table>
<tbody>
<tr>
<td>ISBN:</td>
<td><input autocomplete=\"off\" class=\"ISBN noIcon validate[required,funcCall[checkISBN]]\" name=\"ISBN\" title=\"This is a 10 or 13 digit number, usually printed on the back of the book by the barcode, but is <strong>NOT</strong> the barcode number itself.<br><br>In some cases, an ISBN may include a letter.\" type=\"text\"" . ($editing ? " value=\"" . htmlentities(stripslashes($bookData->ISBN)) . "\"" : "") . " /></td>
</tr>

<tr>
<td>Title:</td>
<td><input autocomplete=\"off\" class=\"noIcon titleInput validate[required]\" name=\"title\" type=\"text\"" . ($editing ? " value=\"" . htmlentities(stripslashes($bookData->title)) . "\"" : "") . " /></td>
</tr>

<tr>
<td>Author:</td>
<td><input autocomplete=\"off\" class=\"noIcon authorInput validate[required]\" name=\"author\" title=\"Seperate multiple authors by a comma and a space\" type=\"text\"" . ($editing ? " value=\"" . htmlentities(stripslashes($bookData->author)) . "\"" : "") . " /></td>
</tr>

<tr>
<td>Edition:</td>
<td><input autocomplete=\"off\" class=\"editionInput noIcon\" name=\"edition\" title=\"[Optional]<br><br>Enter the edition of this book, such as &quot;Second Edition&quot; or &quot;Revised Edition&quot;.\" type=\"text\"" . ($editing ? " value=\"" . htmlentities(stripslashes($bookData->edition)) . "\"" : "") . " /></td>
</tr>
</tbody>
</table>
</section>

";

//Generate the course information section
	echo "<section class=\"courseInformationSection\">
<h2>In which classes did you use this book?</h2>

";

//Grab the categories from the database
	if ($wpdb->get_results("SELECT * FROM `ffi_be_bookcategories`")) {
		$categories = array();
		$category = $wpdb->get_results("SELECT * FROM `ffi_be_bookcategories` ORDER BY name ASC");
		
		for ($i = 0; $i < count($category); $i++) {
			array_push($categories, $category[$i]);
		}
	} else {
		$categories = false;
	}

//The double <div> around the text input is a lazy fix for a positioning bug in the jQuery validator
	$courseFlyout = "<div class=\"menuWrapper\">
<div style=\"height: 0px;\"><div><input class=\"collapse noMod validate[required]\" name=\"className[]\" type=\"text\" /></div></div>

<ul class=\"categoryFly\">";
	
//Generate the category dropdown menu
	$counter = 1;

	foreach($categories as $category) {
	//Break up this "dropdown" list into columns every 10 items
		if ($counter % 10 == 1) {
		//Include an "all" menu item if this is the first item
			if ($counter == 1) {
				$courseFlyout .= "
<li>
<ul>
<li class=\"all selected\" data-value=\"0\"><span class=\"band\" style=\"border-left-color: #FFFFFF;\"><span class=\"icon\" style=\"background-image: url('../system/images/icons/all.png');\">Select a Discipline</span></span></li>";

			//Since we inserted a "free" item, add one to the counter
				$counter++;
			} else {
				$courseFlyout .= "
<li>
<ul>";
			}
		}
		
		$courseFlyout .= "
<li data-value=\"" . $category->id . "\"><span class=\"band\" style=\"border-left-color: " . stripslashes($category->color1) . ";\"><span class=\"icon\" style=\"background-image: url('../../data/book-exchange/icons/" . $category->id . "/icon_032.png');\">" . stripslashes($category->name) . "</span></span></li>";

		if ($counter % 10 == 0) {
			$courseFlyout .= "
</ul>
</li>
";
		}

		$counter++;
	}
	
	$courseFlyout .= "</ul>
</div>";

//Generate the course section dropdown
	$section = "<ul class=\"dropdown\" data-name=\"classSec[]\">
<li class=\"selected\">A</li>
<li>B</li>
<li>C</li>
<li>D</li>
<li>E</li>
<li>F</li>
<li>G</li>
<li>H</li>
<li>I</li>
<li>J</li>
<li>K</li>
<li>L</li>
<li>M</li>
<li>N</li>
<li>O</li>
<li>P</li>
</ul>";
	
//Include a hidden <div> which will contain a copy of the flyout menu for jQuery to copy when additional menus are needed
	echo "<div class=\"flyoutTemplate hidden\">
" . $courseFlyout . "
</div>

";
	
//Include another hidden <div> which will contain a copy of the section letter menu for jQuery to copy when additional menus are needed
	echo "<div class=\"sectionTemplate hidden\">
" . $section . "
</div>
	
";
	
//The generation algorithm for inserting a book is different than updating a book
	if (!$editing) {	
	//Finally display the rest of the portion of the class information step
		echo "<div class=\"classTableHeader\">
<span class=\"className\">Class Name</span>
<span class=\"classNum\">Class Number</span>
<span class=\"classSec\">Class Section</span>
</div>
	
<div class=\"classUsed\">
" . $courseFlyout . "

<input autocomplete=\"off\" class=\"noIcon validate[required,custom[integer],min[101],max[499]]\" data-prompt-position=\"bottomRight\" name=\"classNum[]\" maxlength=\"3\" type=\"text\" />

" . $section . "

<span class=\"delete\" title=\"Delete this class\"></span>
</div>
";
	} else {
	//Display the "table" header
		echo "<div class=\"classTableHeader\">
<span class=\"className\">Class Name</span>
<span class=\"classNum\">Class Number</span>
<span class=\"classSec\">Class Section</span>
</div>
";
		
	//Fetch all of the relavent data from the query
		$classIDs = explode(",", $bookData->classIDs);
		$classNums = explode(",", $bookData->classNums);
		$classSec = explode(",", $bookData->classSec);
		
	//Display each row in the table
		for ($i = 0; $i <= sizeof($classIDs) - 1; $i ++) {
		//The double <div> around the text input is a lazy fix for a positioning bug in the jQuery validator
			$courseFlyout = "<div class=\"menuWrapper\">
<div style=\"height: 0px;\"><div><input class=\"collapse noMod validate[required]\" name=\"className[]\" type=\"text\" value=\"" . $classIDs[$i] . "\" /></div></div>

<ul class=\"categoryFly\">";
		
		//Generate the category dropdown menu
			$counter = 1;
		
			foreach($categories as $category) {
			//Catch when a specific item in the list should be selected
				if ($category->id == $classIDs[$i]) {
					$class =" class=\"selected\"";
				} else {
					$class = "";
				}
				
			//Break up this "dropdown" list into columns every 10 items
				if ($counter % 10 == 1) {
				//Include an "all" menu item if this is the first item
					if ($counter == 1) {
						$courseFlyout .= "
<li>
<ul>
<li class=\"all\" data-value=\"0\"><span class=\"band\" style=\"border-left-color: #FFFFFF;\"><span class=\"icon\" style=\"background-image: url('../system/images/icons/all.png');\">Select a Discipline</span></span></li>";
		
					//Since we inserted a "free" item, add one to the counter
						$counter++;
					} else {
						$courseFlyout .= "
<li>
<ul>";
					}
				}
				
				$courseFlyout .= "
<li" . $class . " data-value=\"" . $category->id . "\"><span class=\"band\" style=\"border-left-color: " . stripslashes($category->color1) . ";\"><span class=\"icon\" style=\"background-image: url('../../data/book-exchange/icons/" . $category->id . "/icon_032.png');\">" . stripslashes($category->name) . "</span></span></li>";
		
				if ($counter % 10 == 0) {
					$courseFlyout .= "
</ul>
</li>
";
				}
		
				$counter++;
			}
			
			$courseFlyout .= "</ul>
</div>";
			
		//Generate the course section dropdown
		$section = "<ul class=\"dropdown\" data-name=\"classSec[]\">
<li" . ($classSec[$i] == "A" ? " class=\"selected\"" : "") . ">A</li>
<li" . ($classSec[$i] == "B" ? " class=\"selected\"" : "") . ">B</li>
<li" . ($classSec[$i] == "C" ? " class=\"selected\"" : "") . ">C</li>
<li" . ($classSec[$i] == "D" ? " class=\"selected\"" : "") . ">D</li>
<li" . ($classSec[$i] == "E" ? " class=\"selected\"" : "") . ">E</li>
<li" . ($classSec[$i] == "F" ? " class=\"selected\"" : "") . ">F</li>
<li" . ($classSec[$i] == "G" ? " class=\"selected\"" : "") . ">G</li>
<li" . ($classSec[$i] == "H" ? " class=\"selected\"" : "") . ">H</li>
<li" . ($classSec[$i] == "I" ? " class=\"selected\"" : "") . ">I</li>
<li" . ($classSec[$i] == "J" ? " class=\"selected\"" : "") . ">J</li>
<li" . ($classSec[$i] == "K" ? " class=\"selected\"" : "") . ">K</li>
<li" . ($classSec[$i] == "L" ? " class=\"selected\"" : "") . ">L</li>
<li" . ($classSec[$i] == "M" ? " class=\"selected\"" : "") . ">M</li>
<li" . ($classSec[$i] == "N" ? " class=\"selected\"" : "") . ">N</li>
<li" . ($classSec[$i] == "O" ? " class=\"selected\"" : "") . ">O</li>
<li" . ($classSec[$i] == "P" ? " class=\"selected\"" : "") . ">P</li>
</ul>";
			
		//Finally display the rest of the portion of the class information step
			echo "	
<div class=\"classUsed\">
" . $courseFlyout . "

<input autocomplete=\"off\" class=\"noIcon validate[required,custom[integer],min[101],max[499]]\" data-prompt-position=\"bottomRight\" name=\"classNum[]\" maxlength=\"3\" type=\"text\" value=\"" . htmlentities(stripslashes($classNums[$i])) . "\" />

" . $section . "

<span class=\"delete\" title=\"Delete this class\"></span>
</div>
";
		}
	}
	
	echo "
<span class=\"add\">Add Another Class</span>
</section>

";

//Include the book's information section
	echo "<section class=\"userInformationSection\">
<h2>It's all up to you</h2>

<table>
<tbody>
<tr>
<td>Price:</td>
<td class=\"price\">
<span class=\"align\">\$</span>
<input autocomplete=\"off\" class=\"priceInput noIcon validate[required,funcCall[checkPrice]]\" maxlength=\"6\" name=\"price\" title=\"Valid prices range from \$0.00 to \$999.99.\" type=\"text\"" . ($editing ? " value=\"" . htmlentities(stripslashes($bookData->price)) . "\"" : "") . " />
</td>
</tr>

<tr>
<td>Condition:</td>
<td class=\"containsMenu\">
<ul class=\"dropdown\" data-name=\"condition\">
<li" . ($editing && $bookData->condition == "Excellent" ? " class=\"selected\"" : "") . ">Excellent</li>
<li" . (!$editing || ($editing && $bookData->condition == "Very Good") ? " class=\"selected\"" : "") . ">Very Good</li>
<li" . ($editing && $bookData->condition == "Good" ? " class=\"selected\"" : "") . ">Good</li>
<li" . ($editing && $bookData->condition == "Fair" ? " class=\"selected\"" : "") . ">Fair</li>
<li" . ($editing && $bookData->condition == "Poor" ? " class=\"selected\"" : "") . ">Poor</li>
</ul>
</td>
</tr>

<tr>
<td>Written in:</td>
<td class=\"containsMenu\">
<ul class=\"dropdown\" data-name=\"written\">
<li" . (!$editing || ($editing && $bookData->written == "No") ? " class=\"selected\"" : "") . ">No</li>
<li" . ($editing && $bookData->written == "Yes" ? " class=\"selected\"" : "") . ">Yes</li>
</ul>
</td>
</tr>

<tr class=\"editor\">
<td class=\"description\">Comments:</td>
<td><textarea name=\"comments\">" . ($editing ? stripslashes($bookData->comments) : "") . "</textarea></td>
</tr>
</tbody>
</table>
</section>

<br>

";

//Include the submit and cancel buttons
	if ($editing) {
		echo "<input class=\"blue\" type=\"submit\" value=\"Submit\" />
";
	} else {
		echo "<input class=\"redirect\" name=\"redirect\" type=\"hidden\" value=\"0\" />
<input class=\"again blue\" type=\"submit\" value=\"Submit and Add Another Book\" />
<input class=\"blue finish\" type=\"submit\" value=\"Submit and Finish\" />
";
	}
	
	echo "<input class=\"cancel\" type=\"button\" value=\"Cancel\" />";

//Include the footer from the administration template
	echo "
</section>
</form>
</section>";
?>