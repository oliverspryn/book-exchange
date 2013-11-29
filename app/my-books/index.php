<?php
//Include the necessary scripts
	$essentials->includeCSS("my-books.min.css");
	$essentials->includePluginClass("APIs/Cloudinary");
	$essentials->includePluginClass("display/User");
	$essentials->includeJS("//cdnjs.cloudflare.com/ajax/libs/highcharts/3.0.2/highcharts.js");
	$essentials->includeJS("my-books.min.js");
	$essentials->requireLogin();
	$essentials->setTitle("My Books");
	
//Save all data from the user's purchase and spending history
	$expireData = $wpdb->get_results("SELECT `BookExpireMonths` FROM `ffi_be_settings`");
	$forSale = FFI\BE\User::getBooksForSale();
	$purchases = FFI\BE\User::getPurchases();
	$purchaseCount = count($purchases);
	$sold = FFI\BE\User::getSold();
	$soldCount = count($sold);
	$stats = FFI\BE\User::fetchMonetaryStats();
	
//Generate the initialzation code for the Highcharts graph
	$categories = implode(",", $stats['Transaction Dates']);
	$earned = implode(",", $stats['Sold']);
	$spent = implode(",", $stats['Purchases']);

	$essentials->includeHeadHTML("<script>\$(function(){\$('div#chart').highcharts({chart:{type:'area'},credits:{enabled:false},exporting:{enabled:false},plotOptions:{area:{marker:{enabled:false}}},series:[{name:'Spent',data:[" . $spent . "]},{name:'Earned',data:[" . $earned . "]}],title:{text:'Total Spent and Earned Over Time'},tooltip:{pointFormat:'<span style=\"color:{series.color}\">{series.name}</span>: <b>\${point.y}.00</b><br>',shared:true},xAxis:{categories:[" . $categories . "]},yAxis:{labels:{formatter:function(){return'\$'+this.value+'.00'}},title:{text:''}}})});</script>");
	
//Display the user's spending and earnings chart
	echo "<section class=\"welcome\">
<div id=\"chart\"></div>

<ul class=\"stats\">
<li class=\"spent\"><p>\$" . $stats['Purchase Total'] . "<span class=\"desktop\">.00</span><span>Total Spent</span></p></li>
<li class=\"earned\"><p>\$" . $stats['Sold Total'] . "<span class=\"desktop\">.00</span><span>Total Earned</span></p></li>
<li class=\"purchased\"><p>" . $purchaseCount . "<span>" . ($purchaseCount == 1 ? "Book" : "Books") . " Purchased</span></p></li>
<li class=\"sold\"><p>" . $soldCount . "<span>" . ($soldCount == 1 ? "Book" : "Books") . " Sold</span></p></li>
<li class=\"sale\"><p><span class=\"saleTotal\">" . $forSale['Published Total'] . "</span><span>" . ($forSale['Published Total'] == 1 ? "Book" : "Books") . " for Sale</span></p></li>
<li class=\"expiring" . ($forSale['Expiring Total'] > 0 ? " prompt" : "") . "\"><p>" . $forSale['Expiring Total'] . "<span>" . ($forSale['Expiring Total'] == 1 ? "Book" : "Books") . " Expiring Soon</span></p></li>
</ul>
</section>

";

//Display the Books for Sale section
	$class = "";
	$notice = "";
	$showEdit = true;
	$showNotice = false;
	$showRestore = false;

	echo "<section class=\"content even\">
<h2>Books for Sale</h2>

";

	if ($forSale['Books Total']) {	
		echo "<ul class=\"sale\">";
	
		foreach($forSale['Books'] as $book) {
			$class = "";
			$notice = "";
			$showEdit = true;
			$showRestore = false;
			
		//Determine which pieces of data should be displayed when a book has expired
			if (in_array($book->SaleID, $forSale['Expired'])) {
				$class = " class=\"expired\"";
				$notice = "<span class=\"notice\">Expired</span>";
				$showEdit = true;
				$showRestore = true;
			}
			
		//Determine which pieces of data should be displayed when a book is expiring soon
			if (in_array($book->SaleID, $forSale['Expiring'])) {
				$class = " class=\"expiring\"";
				$notice = "<span class=\"notice\">Expiring Soon</span>";
				$showEdit = true;
				$showRestore = true;
			}
			
		//Determine which pieces of data should be displayed when a book is sold
			if (in_array($book->SaleID, $forSale['Sold'])) {
				$class = " class=\"sold\"";
				$notice = "<span class=\"notice\">Sold</span>";
				$showEdit = false;
				$showRestore = false;
			}
			
		//Print out the sale item
			echo "
<li" . $class . ">
<td class=\"details\">
<img alt=\"" . htmlentities($book->Title) . " Cover\" src=\"" . FFI\BE\Cloudinary::coverPreview($book->ImageID) . "\">
<h3>" . $book->Title . $notice . "</h3>
<h4>by " . $book->Author . "</h4>
<p class=\"price\">\$" . $book->Price . ".00</p>
";

			if ($showRestore) {
				echo "<button class=\"btn restore\" data-expire=\"" . $expireData[0]->BookExpireMonths . "\" data-id=\"" . $book->SaleID . "\"><i class=\"icon-trash icon-repeat\"></i><span class=\"desktop\"> Restore</span></button>
";
			}
		
			if ($showEdit) {
				echo "<a class=\"btn btn-primary edit\" href=\"" . $essentials->friendlyURL("sell-books/" . $book->SaleID) . "\"><i class=\"icon-pencil icon-white\"></i><span class=\"desktop\"> Edit</span></a>
";
			}

			echo "<button class=\"btn btn-danger delete\" data-id=\"" . $book->SaleID . "\"><i class=\"icon-trash icon-white\"></i><span class=\"desktop\"> Delete</span></button>
</td>
</li>
";
		}

		echo "</ul>

";
	} else {
		$showNotice = true;
	}
		
	echo "<div class=\"none" . ($showNotice ? " show" : "") . "\">
<p>You don't have any books up for sale right now.</p>
<a href=\"" . $essentials->friendlyURL("sell-books") . "\">Add some now &#187;</a>
</div>
</section>

";

//Display the Books Purchased section
	echo "<section class=\"content no-border\">
<h2>Books Purchased</h2>

";

	if ($purchaseCount) {
		echo "<ul class=\"book-list\">";
	
		foreach($purchases as $book) {
			echo "
<li>
<img src=\"" . FFI\BE\Cloudinary::coverPreview($book->ImageID) . "\">

<div>
<h3>" . $book->Title . "</h3>
<h4 class=\"author\">by " . $book->Author . "</h4>
<h4 class=\"sold-by\">sold by " . $book->Merchant . "</h4>
<p class=\"price\">\$" . $book->Price . ".00</p>
</div>
</li>
";	
		}

		echo "</ul>";
	} else {	
		echo "<div class=\"none show\">
<p>You haven't bought any books yet.</p>
<a href=\"" . $essentials->friendlyURL("") . "\">Explore available books now &#187;</a>
</div>";
	}

	echo "
</section>";
?>