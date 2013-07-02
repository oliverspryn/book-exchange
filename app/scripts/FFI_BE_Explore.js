/**
 * Book Exchange search and explore plugin
 *
 * This plugin is used on the Book Exchange home page to add
 * an interesting UI to the exploration and searching capabilities
 * which are intended to be used on the home page. The server 
 * will have already built the exploration section of the page, 
 * which shows by default and features a listing of available
 * courses.
 *
 * This plugin will manage the search aspect of the page, by
 * gracefully replacing the course list with search results in
 * real-time without refreshing the page as search results
 * are loaded or as a new search is queried. The course list 
 * will then gracefully transition back into place as the user
 * closes the search.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI_BE
 * @since     3.0
*/

(function($) {
	$.fn.FFI_BE_Explore = function(options) {
	//Merge the passed options with defaults
		$.extend($.fn.FFI_BE_Explore.defaults, options);
		
		return this.each(function() {
		//The text input
			$.fn.FFI_BE_Explore.input = $(this);
			
		//Several page-related elements
			$.fn.FFI_BE_Explore.document = $(document);
			$.fn.FFI_BE_Explore.page = $('html, body');
			$.fn.FFI_BE_Explore.window = $(window);
			
		//Different pages of the section header, which includes the text input control
			$.fn.FFI_BE_Explore.container = $.fn.FFI_BE_Explore.input.parent().parent().parent();
			$.fn.FFI_BE_Explore.section = $.fn.FFI_BE_Explore.container.parent();
			$.fn.FFI_BE_Explore.searchOptions = $.fn.FFI_BE_Explore.input.parent().siblings('div.options');
			
		//Other form elements
			$.fn.FFI_BE_Explore.by = $.fn.FFI_BE_Explore.searchOptions.find('select.by');
			$.fn.FFI_BE_Explore.inMenu = $.fn.FFI_BE_Explore.searchOptions.find('select.in');
			$.fn.FFI_BE_Explore.sortMenu = $.fn.FFI_BE_Explore.searchOptions.find('select.sort');
			
		//Plugin utilities
			$.fn.FFI_BE_Explore.timer;
			
		//Bootstrap this plugin by calling its instance methods
			$.fn.FFI_BE_Explore.removeMask();
			$.fn.FFI_BE_Explore.searchBar();
			$.fn.FFI_BE_Explore.eventsInit();
			$.fn.FFI_BE_Explore.updateInputsFromHash();
			
		//Listen for a cancel search button click
			$('button.close-search').click(function() {
				$.fn.FFI_BE_Explore.resetSearch();
			});
		});
	};
	
/**
 * This function initializes the content and events for the page.
 * It will place and update position of the search bar on page load
 * as the user scrolls down the page or resizes the window.
 * 
 * @access public
 * @return void
 * @since  3.0
*/
						 
	$.fn.FFI_BE_Explore.searchBar = function() {
		$.fn.FFI_BE_Explore.placeSearchBar();
		$.fn.FFI_BE_Explore.updateSearchBarPositionOnScroll();
		$.fn.FFI_BE_Explore.updateSearchBarPositionOnWindowResize();
	};
								   
/**
 * This function initializes several events which persist for the
 * duration of the plugin. These events include:
 *  - Focus on the search input when a user beings typing, if it
 *    does not already have focus
 *  - Allow a small delay between the time the user starts typing
 *    a query until he or she is finished
 *  - Activate the searching tools as the user begins typing
 *  - Scroll the explore panel into view as the search input field
 *    gains focus
 *  - Update the hash and search results as the user presses the 
 *    forward and back buttons
 *  - Update the URL hash and perform a new search as drop down 
 *    menu values change
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.eventsInit = function() {
	//Focus on the search input when the user types
		$.fn.FFI_BE_Explore.document.keydown(function(e) {
			var code = e.keyCode || e.which;
			var val = e.shiftKey ? String.fromCharCode(e.keyCode).toUpperCase() : String.fromCharCode(e.keyCode).toLowerCase();
			
		//Did the user press "Escape"?
			if (code == 27) {
				$.fn.FFI_BE_Explore.resetSearch();
				return;
			}
			
		//Did the user press "Enter"?
			if (code == 13) {
				clearTimeout($.fn.FFI_BE_Explore.timer);
				$.fn.FFI_BE_Explore.updateHash();
				return;
			}
			
		//Focus on the input, if the user presses an alphanumeric key
			if (!$.fn.FFI_BE_Explore.input.is(':focus') && /[a-z0-9]/i.test(val)) {
				$.fn.FFI_BE_Explore.input.focus();
			}
			
		//Reset the URL hash timer
			clearTimeout($.fn.FFI_BE_Explore.timer);
			
			$.fn.FFI_BE_Explore.timer = setTimeout(function() {
				$.fn.FFI_BE_Explore.updateHash();
			}, $.fn.FFI_BE_Explore.defaults.hashUpdateDelay);
		});
		
	//Show the controls when a value is entered
		$.fn.FFI_BE_Explore.document.keyup(function(e) {
			if ($.fn.FFI_BE_Explore.input.val() == '') {
				$.fn.FFI_BE_Explore.hideControls();
				$.fn.FFI_BE_Explore.resetSearch();
			} else {
				$.fn.FFI_BE_Explore.showControls();
			}
		});
		
	//Scroll the explore section into view on input focus
		$.fn.FFI_BE_Explore.input.focus(function() {
			if (!$.fn.FFI_BE_Explore.searchActive()) {
			//Scroll the explore section into view
				$.fn.FFI_BE_Explore.page.animate({
					scrollTop: $.fn.FFI_BE_Explore.section.offset().top + 1
				}, $.fn.FFI_BE_Explore.defaults.windowScrollTime);
			}
		});
		
	//Repspond the forward and back button clicks
		$.fn.FFI_BE_Explore.window.bind('hashchange', function() {
			$.fn.FFI_BE_Explore.updateInputsFromHash();
		});
		
	//Update the hash when the dropdown menus change value
		$.fn.FFI_BE_Explore.by.change(function() {
			$.fn.FFI_BE_Explore.updateHash();
		});
		
		$.fn.FFI_BE_Explore.inMenu.change(function() {
			$.fn.FFI_BE_Explore.updateHash();
		});
		
		$.fn.FFI_BE_Explore.sortMenu.change(function() {
			$.fn.FFI_BE_Explore.updateHash();
		});
	};
	
/**
 * Remove the page initialization mask after a set duration
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.removeMask = function() {
		setTimeout(function() {
			$('body').addClass('show');
			
			setTimeout(function() {
				$('section.loader').remove();
			}, $.fn.FFI_BE_Explore.defaults.maskRemoveFadeDuration);
		}, $.fn.FFI_BE_Explore.defaults.maskRemoveDelay);
	};
	
/**
 * Determine whether or not the explore section is in search
 * mode
 *
 * @access public
 * @return boolean  Whether or not the explore section is in search mode
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.searchActive = function() {
		return $.fn.FFI_BE_Explore.section.hasClass('active-search');
	};
	
/**
 * Place the search bar a given number of pixels from the bottom
 * of the screen
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.placeSearchBar = function() {
		$.fn.FFI_BE_Explore.section.css('margin-top', $.fn.FFI_BE_Explore.window.height() - $.fn.FFI_BE_Explore.container.height());
	};
	
/**
 * Anchor the search bar to the top of the screen as the user
 * scrolls down the page
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.updateSearchBarPositionOnScroll = function() {
		$.fn.FFI_BE_Explore.window.scroll(function() {
			if ($.fn.FFI_BE_Explore.window.scrollTop() > $.fn.FFI_BE_Explore.window.height() - $.fn.FFI_BE_Explore.container.height()) {
				$.fn.FFI_BE_Explore.section.addClass('fixed');
			} else {
				$.fn.FFI_BE_Explore.section.removeClass('fixed');
			}
		});
	};
	
/**
 * Maintain the search bar's distance from the top of the screen
 * as the browser window is resized
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.updateSearchBarPositionOnWindowResize = function() {
		$.fn.FFI_BE_Explore.window.resize(function() {
			$.fn.FFI_BE_Explore.section.css('margin-top', $.fn.FFI_BE_Explore.window.height() - $.fn.FFI_BE_Explore.container.height());
		});
	};
	
/**
 * Update the URL hash value based on the search form input values
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.updateHash = function() {
		var value = $.fn.FFI_BE_Explore.input.val();
		var hash;
		
	//Don't set the hash value if the search input is empty
		if (value != '') {
			hash = 'q=' + encodeURIComponent(value)
		    	 + '&by=' + encodeURIComponent($.fn.FFI_BE_Explore.by.val())
				 + '&in=' + encodeURIComponent($.fn.FFI_BE_Explore.inMenu.val())
				 + '&sort=' + encodeURIComponent($.fn.FFI_BE_Explore.sortMenu.val());
		} else {
			hash = 'nop';
		}
		
		window.location.hash = hash;
	};
	
/**
 * Send the search request to the server and build the listing
 * of search results from the fetched data
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.search = function() {
		var query = $.fn.FFI_BE_Explore.input.val(), by = $.fn.FFI_BE_Explore.by.val();
		
		$.ajax({
			'data' : {
				'q' : query,
				'by' : by,
				'in' : $.fn.FFI_BE_Explore.inMenu.val(),
				'sort' : $.fn.FFI_BE_Explore.sortMenu.val(),
				'page' : '1',
				'limit' : $.fn.FFI_BE_Explore.defaults.searchResultsLimit
			}, 
			'type' : 'GET',
			'url' : $.fn.FFI_BE_Explore.defaults.searchURL,
			'success' : function(data) {
			//Searching may have been canceled by the time the request was returned
				if ($.fn.FFI_BE_Explore.searchActive()) {
					var JSON = $.parseJSON(data);
					var HTML = '<ul class="search-list">';
					var condition = ['poor', 'fair', 'good', 'very-good', 'excellent'];
					
				//Build the listing of search results
					if (JSON.length) {
						for (var i = 0; i < JSON.length; ++i) {
							HTML += '<li>';
							HTML += '<a href="#"><img src="' + JSON[i].imageURL + '"></a>';
							HTML += '<h3><a href="#">' + JSON[i].title + '</a> <span>by ' + JSON[i].author + '</span></h3>';
							HTML += '<p class="merchant"><strong>Merchant:</strong> ' + JSON[i].merchant + '</p>';
							HTML += '<p class="condition ' + condition[JSON[i].condition - 1] + '"><strong>Condition:</strong></p>';
							HTML += '<button class="btn btn-primary purchase" data-id=\"' + JSON[i].ID + '\">Buy for $' + JSON[i].price + '.00</button>';
							HTML += '</li>';
						}
					} else {
						HTML += '<li class="none">';
						HTML += '<h3>No Results Found</h3>';
						HTML += '<p>We looked hard, but we couldn\'t find anything related to <strong>' + query + '</strong> when searching by <strong>' + by + '</strong>. Try adjusting your search criteria. However, it\'s possible that we just don\'t have anything on <strong>' + query + '</strong>. Bummer. :-(</p>';
					}
					
					HTML += '</ul>';
					
				//Push the results into the search container
					var hotspot = $('section.search-hotspot');
					
					if (!hotspot.length) {
						hotspot = $('<section class="search-hotspot"/>').appendTo($.fn.FFI_BE_Explore.section);
					}
					
					hotspot.empty().html(HTML);
					
				//Remove the loading mask, if any
					$('div.loader-mask').remove();
				}
			}
		});
	};
	
/**
 * Parse the hash value as if it were a query string and store 
 * these values in an array. A hash value like this:
 *
 *   #v1=text&v2=more
 *
 * ... will be parsed into an array and returned, like this:
 *
 *   [['v1', 'text'],
 *    ['v2', 'more']] 
 *
 * @access public
 * @return array<string> An array containing the keys and values to each query in the hash value
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.parseHash = function() {
		var hash = window.location.hash.substring(1);
		var vars = hash.split('&');
		var ret = new Array();
		
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			ret.push([pair[0], decodeURIComponent(pair[1])]);
		}
		
		return ret;
	};
	
/**
 * Read the values from the hash value and fill out the form input
 * elements from the parsed hash values. If it is determined that 
 * the hash value contains a search query, then a search will also
 * be performed. 
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.updateInputsFromHash = function() {
		var hash = $.fn.FFI_BE_Explore.parseHash();
		
		if (hash[0][1] && hash[0][1] != undefined && hash[0][1] != 'undefined') {	
			$.fn.FFI_BE_Explore.input.val(hash[0][1]);
			$.fn.FFI_BE_Explore.by.val(hash[1][1]);
			$.fn.FFI_BE_Explore.inMenu.val(hash[2][1]);
			$.fn.FFI_BE_Explore.sortMenu.val(hash[3][1]);
			
			$.fn.FFI_BE_Explore.activateSearch();
			$.fn.FFI_BE_Explore.showControls();
			$.fn.FFI_BE_Explore.scrollToSearch();
			$.fn.FFI_BE_Explore.search();
		} else {
			$.fn.FFI_BE_Explore.resetSearch();
		}
	};
	
/**
 * Scroll the viewport to the top of the explore/search section
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.scrollToSearch = function(animate) {
		if (animate) {
			$.fn.FFI_BE_Explore.page.animate({
				scrollTop: $.fn.FFI_BE_Explore.section.offset().top + 1
			}, $.fn.FFI_BE_Explore.defaults.windowScrollTime);
		} else {
			$.fn.FFI_BE_Explore.page.scrollTop($.fn.FFI_BE_Explore.section.offset().top + 1);
		}
	};
	
/**
 * Show the search controls
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.showControls = function() {
		$.fn.FFI_BE_Explore.section.addClass('show-controls');
	};
	
/**
 * Hide the search controls
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.hideControls = function() {
		$.fn.FFI_BE_Explore.section.removeClass('show-controls');
	};
	
/**
 * Prepare the UI for searches by hiding the course listing sections, 
 * or, if the page is already in search mode, apply a light visual
 * mask overtop of the existing search results while new results are
 * fetched
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.activateSearch = function() {
		if (!$.fn.FFI_BE_Explore.searchActive()) {
			$.fn.FFI_BE_Explore.section.addClass('active-search');
			
		//Wait for old elements to transition out
			setTimeout(function() {
				$('section.liberal-arts, section.science-mathematics').hide();
			}, $.fn.FFI_BE_Explore.adjacentContainersFadeDuration);
		} else {
			$('<div class="loader-mask"/>').appendTo($.fn.FFI_BE_Explore.section);
		}
	};
	
/**
 * Reset the UI back to explore mode, where the listing of avaliable 
 * courses are displayed, instead of search results. Also, the URL
 * hash is cleared of its search query parameters, and the search
 * tools are collapsed.
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
	$.fn.FFI_BE_Explore.resetSearch = function() {
		$.fn.FFI_BE_Explore.input.val('');
		$.fn.FFI_BE_Explore.updateHash();
		$.fn.FFI_BE_Explore.section.removeClass('active-search show-controls');
		$('section.liberal-arts, section.science-mathematics').show();
		$('section.search-hotspot').remove();
		$('div.loader-mask').remove();
		clearTimeout($.fn.FFI_BE_Explore.timer);
	};
	
	$.fn.FFI_BE_Explore.defaults = {
		adjacentContainersFadeDuration : 500,  //The amount of time required for CSS to fade out the course containers
		hashUpdateDelay : 1000,                //The amount of time to wait after the user finishes typing updating the hash
		maskRemoveDelay : 1500,                //The delay before removing the page initialization hash
		maskRemoveFadeDuration : 250,          //The amount of time required for CSS to fade out the page initialization hash
		searchResultsLimit : 10,               //The maximum number of search results to retrieve at a time
		searchURL : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/includes/ajax/search.php',
		windowScrollTime : 500                 //The amount of time required to scroll the search/explore section into view
	};
})(jQuery);

(function($) {
	$(function() {
	//Initialize the explore plugin with default settings
		$('input#search-main').FFI_BE_Explore();
		
	//Scroll the explore section into view when a user clicks on the big "Browse" tile
		$('li.browse').click(function() {
			$("html, body").animate({
				'scrollTop' : $('section.explore').offset().top + 1
			}, 500);
		});
		
	//When the user clicks on the "Search" tile, focus on the search element
		$('li.search').click(function() {
			$('input#search-main').focus();
		})
	})
})(jQuery);