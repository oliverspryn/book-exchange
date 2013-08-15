/**
 * Book Exchange Sell Books Assistant plugin
 *
 * This plugin is used on the Book Exchange sell books page to
 * assist the user in several basic tasks to speed the data entry
 * process and provide the user with relevant information as he or
 * she enters the necessary book data. Some of its features 
 * include:
 *  - Search for the book's information by ISBN in the Book Exchange
 *    database and automatically fill out the fields asking for the
 *    book's information.
 *  - Suggest a list of course sections which use this book, based
 *    on previous entries.
 *  - Provide the user with a list of book covers to choose from, 
 *    or provide the correct book cover if the book is already in the
 *    database.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI_BE
 * @since     3.0
*/

(function($) {
	$.fn.FFI_BE_Sell_Books_Assistant = function(options) {
	//Merge the passed options with defaults
		$.extend($.fn.FFI_BE_Sell_Books_Assistant.defaults, options);
		
		return this.each(function() {
		//Related text input elements and containers
			$.fn.FFI_BE_Sell_Books_Assistant.ISBN10 = $(this);
			$.fn.FFI_BE_Sell_Books_Assistant.ISBN13 = $('input#ISBN13');
			$.fn.FFI_BE_Sell_Books_Assistant.title = $('input#title');
			$.fn.FFI_BE_Sell_Books_Assistant.author = $('input#author');
			$.fn.FFI_BE_Sell_Books_Assistant.edition = $('input#edition');
			$.fn.FFI_BE_Sell_Books_Assistant.imageURL = $('input#imageURL');
			$.fn.FFI_BE_Sell_Books_Assistant.courseContainer = $('div#suggestions');
			
		//Bootstrap this plugin by calling its instance methods
			$.fn.FFI_BE_Sell_Books_Assistant.eventsInit();
		});
	};

/**
 * This function initializes several events which persist for the
 * duration of the plugin. These events include:
 *  - Fetching the auto-suggest data from the server or a list of 
 *    book covers, whenever the ISBN10 or ISBN13 form input changes.
 *  - Select a book cover from the listing of supplied covers.
 *  - Apply a course suggestion to the course listings table.
 *  - Remove a course from the table.
 *  - Add a row to the course listings table.
 *
 * @access public
 * @return void
 * @since  3.0
*/
	
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

	//Select a book cover image suggestion
		$(document).on('click', 'img.suggestion', function() {
			var image = $(this);

			$('img.selected.suggestion').removeClass('selected');
			image.addClass('selected');
			$.fn.FFI_BE_Sell_Books_Assistant.imageURL.val(image.attr('src'))
		});
		
	//Listen for clicks on the course suggestion boxes
		$(document).on('click', 'div#suggestions div.btn-group input', function() {
		//Copy the template drop down menu and select the destination table
			var course = $(this);
			var table = $('table#dependent-courses');
			var courses = $('div#course-template').clone().children('select').attr('name', 'course[]').addClass('validate[required]').parent();
			
		//Generate the section A - Z drop down menu
			var sections = '<select class="input-small validate[required]" name="section[]"><option value="">-</option>';
			var character = '';
		
			for (var c = 65; c <= 90; ++c) { //Character codes: 65 = A, 90 = Z
				character = String.fromCharCode(c);
				sections += '<option' + (course.attr('data-section') == character ? ' selected' : '') + ' value="' + character + '">' + character + '</option>';
			}
			
			sections += '</select>';
			
		//Generate the HTML to insert a new row into the target table
			var ID = 'new-row-' + Math.round(100000 * Math.random());
		
			var HTML = '<tr id="' + ID + '">';
			HTML += '<td><span>Course:</span>' + courses.html() + '</td>'
			HTML += '<td><span>Number:</span><input class="input-small validate[required,custom[integer],min[101],max[499]]" max="499" min="101" name="number[]" type="number" value="' + course.attr('data-number') + '"></td>';
			HTML += '<td><span>Section:</span>' + sections + '</td>';
			HTML += '<td class="delete"><span></span></td>';
			HTML += '</tr>';
			
			table.append(HTML);
			
		//If the user is just using the suggestions, and didn't enter any value on their own, then remove the empty row
			var empty = table.children('tbody').children('tr:first-child').children('td');
		
			if (empty.eq(0).children('select').val() == '' && empty.eq(1).children('input').val() == '' && empty.eq(2).children('select').val() == '') {
				empty.parent().remove();
			}
			
		//Select the correct course (I almost spelled "curse") from the course menu, since this can't be done during the clone() operation
			$('tr#' + ID + ' td:first-child select').val(course.attr('data-code'));
			
		//If this is the last element, remove the contents of the suggestions <div>
			if (course.siblings('input').length == 0) {
				course.parent().parent().empty().hide();
		//Remove the element which was clicked on
			} else {
				course.remove();
			}
		});
		
	//Listen for clicks the delete button to remove a course from the Dependent Courses table
		$(document).on('click', 'td.delete span', function() {
			var row = $(this).parent().parent();
			
		//If there is more than one course in the table
			if (row.siblings('tr').length > 0) {
				row.remove();
		//... otherwise reset each of the form elements
			} else {
				row.children('td').eq(0).children('select').val('');
				row.children('td').eq(1).children('input').val('');
				row.children('td').eq(2).children('select').val('');
			}
		});
		
	//Listen for clicks to add a new course
		$('span.add-course').click(function() {
			var table = $('table#dependent-courses');
			var courses = $('div#course-template').clone().children('select').attr('name', 'course[]').addClass('validate[required]').parent();
			
		//Generate the section A - Z drop down menu
			var sections = '<select class="input-small validate[required]" name="section[]"><option value="">-</option>';
			var character = '';
		
			for (var c = 65; c <= 90; ++c) { //Character codes: 65 = A, 90 = Z
				character = String.fromCharCode(c);
				sections += '<option value="' + character + '">' + character + '</option>';
			}
			
			sections += '</select>';
			
		//Generate the HTML to insert a new row into the target table
			var HTML = '<tr>';
			HTML += '<td><span>Course:</span>' + courses.html() + '</td>'
			HTML += '<td><span>Number:</span><input class="input-small validate[required,custom[integer],min[101],max[499]]" max="499" min="101" name="number[]" type="number" value=""></td>';
			HTML += '<td><span>Section:</span>' + sections + '</td>';
			HTML += '<td class="delete"><span></span></td>';
			HTML += '</tr>';
			
			table.append(HTML);
		});
	};

/**
 * This method will fetch what information it can about a 
 * particular book based on the given ISBN. While this method
 * queue's the server, it can reveal an animation while the 
 * data is being retrieved.
 *
 * Based on the server's response, this method can determine
 * whether the server already has information about this book
 * and automatically fill in all of the known field, or if no
 * information is available, is suggesting a list of possible
 * book covers. These covers will be made available to the user
 * to choose the appropriate one.
 *
 * This method will also auto-correct all of the form's ISBN
 * input values, by removing any dashes or spaces the user may 
 * have entered.
 *
 * @access public
 * @param  string    ISBN   The ISBN10 or ISBN13 of the desired book
 * @param  animation jQuery A jQuery object referencing an element to reveal while the data is being requested
 * @return void
 * @since  3.0
*/
	
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
			//Validate the incoming JSON
				try {
					var JSON = $.parseJSON(data);
				} catch(e) {
					if (data.substring(0, 10) == 'USER_ERROR') {
						alert(data.substring(10));
					} else {
						alert('An error was encountered while processing your automatic book data suggestion.\n\nIf this is the first time you have seen this error, wait one minute and try reloading this page before re-entering your book\'s data. If this error continues to occur, contact the site administrator for assistance and include the details listed below.\n\n-----------------------\n\nURL:\n' + window.location.href + '\n\nISBN10:\n' + ISBN.asIsbn10() + '\n\nISBN13:\n' + ISBN.asIsbn13() +'\n\nResponse from server:\n' + data);
					}
					
					return;
				}

			//If the data contains the title of the book, then the book was on record...
				if (typeof JSON.title !== "undefined") {
				//Fill up the book information
					$.fn.FFI_BE_Sell_Books_Assistant.title.val(JSON.title);
					$.fn.FFI_BE_Sell_Books_Assistant.author.val(JSON.author);
					$.fn.FFI_BE_Sell_Books_Assistant.edition.val(JSON.edition);

				//Add the selected book cover
					var target = $('div.book-cover');
					var HTML = '<ul><li><img class="selected suggestion" src="' + JSON.imageURL + '"></li></ul>';

					$.fn.FFI_BE_Sell_Books_Assistant.imageURL.val(JSON.imageURL);
					target.empty().html(HTML);
					
				//Create a listing of suggested courses
					if (JSON.courses.length) {
						HTML = '<div class="btn-group">';
				
						for (var i = 0; i < JSON.courses.length; ++i) {
							HTML += '<input class="btn" data-code="' + JSON.courses[i].code + '" data-number="' + JSON.courses[i].number + '" data-section="' + JSON.courses[i].section + '" type="button" value="' + JSON.courses[i].name + ' ' + JSON.courses[i].number + ' ' + JSON.courses[i].section + '">';
						}
				
						HTML += '</div>';
				
						$.fn.FFI_BE_Sell_Books_Assistant.courseContainer.empty().show().append('<h3>Suggestions (click to add):</h3>' + HTML);
					}
			//... or otherwise, the server returned an array of cover images
				} else {
				//Clean out the book information
					$.fn.FFI_BE_Sell_Books_Assistant.title.val('');
					$.fn.FFI_BE_Sell_Books_Assistant.author.val('');
					$.fn.FFI_BE_Sell_Books_Assistant.edition.val('');
					$.fn.FFI_BE_Sell_Books_Assistant.courseContainer.empty().hide();

				//Create an unordered list of book cover images to choose from
					var target = $('div.book-cover');
					var HTML = '<ul>';

					for (var i = 0; i < JSON.length; ++i) {
						HTML += '<li><img class="suggestion" src="' + JSON[i] + '"></li>';
					}

					HTML += '</ul>';

					$.fn.FFI_BE_Sell_Books_Assistant.imageURL.val('');
					target.empty().html(HTML);
				}
				
			//Hide the loading animation
				animation.removeClass('show');
			}
		});
	};
	
/**
 * The plugin settings
 *
 * @access public
 * @since  3.0
 * @type   object<string>
*/
	
	$.fn.FFI_BE_Sell_Books_Assistant.defaults = {
		fetchURL : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/includes/ajax/suggest.php'
	};
})(jQuery);
