<?php
//Include section one
	echo "<section class=\"welcome\">
<div class=\"design\">
<h1>Book Exchange</h1>

<div class=\"mask\">
<div class=\"sell\">
<ul>
<li class=\"sell\"><a class=\"openLogin\" href=\"sell-books/\">Sell Your Books</a></li>
<li class=\"search\"><a href=\"search\">Search</a></li>
<li class=\"browse\"><a href=\"listings\">Browse Listings</a></li>
</ul>
</div>

<span class=\"divider\"></span>

<div class=\"search\">
<form action=\"search\" method=\"get\">
<h2 class=\"search\">Search for Books:</h2>
<input autocomplete=\"off\" class=\"search full\" name=\"search\" type=\"text\" />
<span class=\"expand\">Advanced Search Options</span>

<div class=\"controls hidden\">
<span class=\"searchStep\">Search by:</span>
<ul class=\"dropdown\" data-name=\"searchBy\">
<li class=\"selected\" data-value=\"title\">Title</li>
<li data-value=\"author\">Author</li>
<li data-value=\"ISBN\">ISBN</li>
<li data-value=\"course\">Course</li>
<li data-value=\"seller\">Seller</li>
</ul>

<br>

<div class=\"menuWrapper\">
<div style=\"height: 0px;\"><div><input class=\"collapse noMod\" name=\"category\" type=\"text\" value=\"0\" /></div></div>

<ul class=\"categoryFly\"></ul>
</div>
</div>

<input class=\"yellow submit\" type=\"submit\" value=\"Search\" />
</form>
</div>
</div>
</div>
</section>

<img class=\"shadow\" src=\"system/images/welcome/paper_shadow.png\" />

";

//Include section two
	echo "<section class=\"introduction\">
<section class=\"video\">
Quick start video is on its way and will be posted soon!
<!--<object width=\"100%\" height=\"600\">
<param name=\"allowfullscreen\" value=\"true\" />
<param name=\"allowscriptaccess\" value=\"always\" />
<param name=\"wmode\" value=\"transparent\" />
<param name=\"movie\" value=\"http://vimeo.com/moogaloop.swf?clip_id=18851690&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=c9ff23&amp;fullscreen=1&amp;autoplay=0&amp;loop=0\" />
<embed src=\"http://vimeo.com/moogaloop.swf?clip_id=18851690&amp;force_embed=1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=0&amp;show_portrait=0&amp;color=c9ff23&amp;fullscreen=1&amp;autoplay=0&amp;loop=0\" type=\"application/x-shockwave-flash\" allowfullscreen=\"true\" allowscriptaccess=\"always\" width=\"100%\" height=\"600\" wmode=\"transparent\"></embed>
</object>-->
</section>

<section class=\"description\">
<h2>Welcome to the new book exchange!</h2>
<p>The SGA has been hard at work and is proud to bring you a new and improved student book exchange.</p>
<p>We have made so many enchancements to boost your experience, that it's hard to know where to begin! Would you like to learn more about it? Sure you would! Scroll down to start exploring.</p>
</section>
</section>

";

//Include section three
	echo "<section class=\"screenshots\">
<div class=\"overflowHide\">
<ul class=\"scrollerContainer\">
<li><img src=\"system/images/welcome/view_categories.png\" /></li>
<li><img src=\"system/images/welcome/view_sell_books.png\" /></li>
<li><img src=\"system/images/welcome/view_listing.png\" /></li>
<li><img src=\"system/images/welcome/view_search.png\" /></li>
<li><img src=\"system/images/welcome/view_book.png\" /></li>
</ul>
</div>

<h2>Built By Students, For Students</h2>
<p>The semiannual book exchange ritual doesn't have to be a drag. From its captivating start to a satisfying end, this service has been designed to <strong>think the way you think</strong>. Here at SGA, we make it a point to <strong>sell your books fast</strong> by giving each of them the attention they deserve. Our new searching and cataloguing systems provide a unique and intuitive interface to help you <strong>quickly find the books you need</strong> for that next class.</p>
<h2>Old Ideas Reimagined</h2>
<p>This new release contains all of the <strong>same tools</strong> that you enjoyed in the previous exchange. However, we've <strong>improved and expanded</strong> on these old ideas and introduced a whole <strong>new set of tools and capibilities</strong> which are designed to make the book exchanging process as pain free (and enjoyable) as possible.</p>
</section>

";

//Include section four
	echo "<section class=\"tiles\">
<div class=\"design\">
<h2>Something New: Exchange Tiles</h2>
<p>Life in college is hard enough, but exchanging your books shouldn't be. That's why we've introduced the exchange titles. They are a colorful feature of our cataloguing system which are designed to <strong>catch your eye</strong> and <strong>trigger your memory</strong> whenever you see one of them.</p>
<p>These tiles show up all through out the site, from browsing and searching our database, to selling books of your own. Each discipline of study is assigned a <strong>unique color and pair of letters</strong> to help set them apart from others. You'll learn to <strong>quickly spot</strong> them on a page for your <strong>areas of interest</strong>.</p>
<a class=\"explore highlight\" href=\"listings\">Start Exploring &raquo;</a>
</div>
</section>

";

//Include section five
	echo "<section class=\"features\">
<div class=\"description\">
<h2>Sell Your Book in 17.5 Seconds</h2>
<p>Yes, we actually counted. We've engineered this exchange to be as easy as possible. With lots of <strong>integrated tools</strong> that are desgined to <strong>enhance your speed and productivity</strong>, you'll be able to buy and sell books fast, so you can get back to what is really important. Who knows? Maybe you can break this record!</p>
<a class=\"explore highlight\" href=\"sell-books\">Sell Your Books &raquo;</a>

<h2>Real-time Results As You Search</h2>
<p><strong>Get up to the second</strong> search results as you search for books in our database. Before you can even finish typing the title of your book, you are given a <strong>short, comprehensive overview</strong> of the book you are searching, with details such as the <strong>total number up for sale</strong> and it <strong>starting price</strong>.</p>
<a class=\"explore highlight\" href=\"search\">Search for Books &raquo;</a>
</div>

<img class=\"sell\" src=\"system/images/welcome/view_sell_books_mini.png\" />
<img class=\"search\" src=\"system/images/welcome/view_search_mini.png\" />
</section>

";


	echo "<section class=\"closing\">
<div class=\"design\">
<ul>
<li style=\"background-image: url(system/images/welcome/sell_books.png);\">
<h3>Buy and Sell Books</h3>
<p><strong>Set your own price</strong> and sell your books in three easy steps. You can often <strong>buy other books at discounted prices</strong>.</p>
<a class=\"explore highlight\" href=\"sell-books\">Sell Your Books &raquo;</a>
</li>

<li style=\"background-image: url(system/images/welcome/search.png);\">
<h3>Search the Growing Database</h3>
<p>Search <strong>Thousands</strong> of books by title, author, course, or ISBN, contributed by students like you!</p>
<a class=\"explore highlight\" href=\"search\">Search for Books &raquo;</a>
</li>

<li style=\"background-image: url(system/images/welcome/categories.png);\">
<h3>Browse by Category</h3>
<p>Each category has its own unique <strong>color exchange tile</strong>, designed to catch your eye when you come across a class you recognize.</p>
<a class=\"explore highlight\" href=\"listings\">Browse for Books &raquo;</a>
</li>
</ul>
</div>
</section>

";

//Include section seven
	echo "<section class=\"finish\">
<div class=\"books\">
";

	//if (!loggedIn()) {
		echo "<h2>Convinced? Jump on board!</h2>
<button class=\"green large openLogin\" data-login=\"sell-books/\">Register</button>
<span class=\"alternate\">or <a class=\"highlight\" href=\"../login\">login</a></span>
";
	//} else {
		echo "<h2>Convinced? Start selling!</h2>
<button class=\"green large\" onclick=\"document.location.href='sell-books/'\">Sell Books</button>
";
	//}
	
	echo "<div class=\"pointer\"></div>
</div>
</section>";

//Include the footer from the administration template
	echo "
</section>";
?>