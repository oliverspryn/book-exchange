(function($) {
	$.fn.FFI_BE_Sell_Books_Assistant = function(ISBN13Input, titleInput, authorInput, editionInput, courseContainer, options) {
	//Merge the passed options with defaults
		$.extend($.fn.FFI_BE_Sell_Books_Assistant.defaults, options);
		
		return this.each(function() {
		//Related text input elements and containers
			$.fn.FFI_BE_Sell_Books_Assistant.ISBN10 = $(this);
			$.fn.FFI_BE_Sell_Books_Assistant.ISBN13 = ISBN13Input;
			$.fn.FFI_BE_Sell_Books_Assistant.title = titleInput;
			$.fn.FFI_BE_Sell_Books_Assistant.author = authorInput;
			$.fn.FFI_BE_Sell_Books_Assistant.edition = editionInput;
			$.fn.FFI_BE_Sell_Books_Assistant.courseContainer = courseContainer;
			
		//Bootstrap this plugin by calling its instance methods
			$.fn.FFI_BE_Sell_Books_Assistant.eventsInit();
		});
	};
	
	$.fn.FFI_BE_Sell_Books_Assistant.eventsInit = function() {
	//Listen for changes to the ISBN10 text input
		$.fn.FFI_BE_Sell_Books_Assistant.ISBN10.change(function() {
			var ISBN10 = $.fn.FFI_BE_Sell_Books_Assistant.ISBN10.val();
			
			if (ISBN10 != '') {
				ISBN10 = ISBN.parse($.fn.FFI_BE_Sell_Books_Assistant.ISBN10.val());
				
				if (ISBN10 != null && ISBN10.isIsbn10()) {
					$.fn.FFI_BE_Sell_Books_Assistant.fetch(ISBN10, $('span#searching10'));
				}
			}
		});
		
	//Listen for changes to the ISBN13 text input
		$.fn.FFI_BE_Sell_Books_Assistant.ISBN13.change(function() {
			var ISBN13 = $.fn.FFI_BE_Sell_Books_Assistant.ISBN13.val();
			
			if (ISBN13 != '') {
				ISBN13 = ISBN.parse($.fn.FFI_BE_Sell_Books_Assistant.ISBN13.val());
				
				if (ISBN13 != null && ISBN13.isIsbn13()) {
					$.fn.FFI_BE_Sell_Books_Assistant.fetch(ISBN13, $('span#searching13'));
				}
			}
		});
	};
	
	$.fn.FFI_BE_Sell_Books_Assistant.fetch = function(ISBN, animation) {
	//Update the ISBN values
		$.fn.FFI_BE_Sell_Books_Assistant.ISBN10.val(ISBN.asIsbn10()); //Removes any dashes the user may have entered
		$.fn.FFI_BE_Sell_Books_Assistant.ISBN13.val(ISBN.asIsbn13());
		
	//Show the loading animation
		animation.addClass('show');
		
	//Fetch data from the server to pre-fill the rest of the book and course information
		$.ajax({
			'data' : {
				'ISBN' : ISBN.asIsbn13() //Doesn't matter which type we send
			},
			'type' : 'GET',
			'url' : $.fn.FFI_BE_Sell_Books_Assistant.defaults.fetchURL,
			'success' : function(data) {
				if (data != '') {
					var JSON = $.parseJSON(data);
					
				//Fill up the book information
					$.fn.FFI_BE_Sell_Books_Assistant.title.val(JSON.title);
					$.fn.FFI_BE_Sell_Books_Assistant.author.val(JSON.author);
					$.fn.FFI_BE_Sell_Books_Assistant.edition.val(JSON.edition);
				
				//Create a listing of suggested courses
					var HTML = '<ul>';
					
					for (var i = 0; i < JSON.courses.length; ++i) {
						HTML += '<li>';
						HTML += JSON.courses[i].course + ' ';
						HTML += JSON.courses[i].number + ' ';
						HTML += JSON.courses[i].section;
						HTML += '</li>';
					}
					
					HTML += '</ul>';
					
					$.fn.FFI_BE_Sell_Books_Assistant.courseContainer.empty().append(HTML);
					
				//Hide the loading animation
					animation.removeClass('hide');
				}
			}
		});
	};
	
/**
 * The plugin settings
 *
 * @access public
 * @since  3.0 Dev
 * @type   object 
*/
	
	$.fn.FFI_BE_Sell_Books_Assistant.defaults = {
		fetchURL : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/includes/ajax/suggest.php'
	};
})(jQuery);