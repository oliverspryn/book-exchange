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
			$.fn.FFI_BE_Sell_Books_Assistant.courseContainer = $('div#suggestions');
			
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
		
	//Listen for clicks on the course suggestion boxes
		$(document).on('click', 'div#suggestions ul li', function() {
		//Copy the template drop down menu and select the destination table
			var course = $(this);
			var table = $('table#dependent-courses');
			var courses = $('div#course-template').clone().children('select').attr('name', 'course[]').addClass('validate[required]').parent();
			
		//Generate the section A - Z drop down menu
			var sections = '<select class="input-small validate[required]" name="section[]"><option value="">-</option>';
			var char = '';
		
			for (var c = 65; c <= 90; ++c) { //Char codes: 65 = A, 90 = Z
				char = String.fromCharCode(c);
				sections += '<option' + (course.attr('data-section') == char ? ' selected' : '') + ' value="' + char + '">' + char + '</option>';
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
			
		//Append the row onto the table
			table.append(HTML);
			
		//If the user is just using the suggestions, and didn't enter any value on their own, then remove the empty row
			var empty = table.children('tbody').children('tr:first-child').children('td');
		
			if (empty.eq(0).children('select').val() == '' && empty.eq(1).children('input').val() == '' && empty.eq(2).children('select').val() == '') {
				empty.parent().remove();
			}
			
		//Select the correct course (I almost spelled "curse") from the course menu, since this can't be done during the clone() operation
			$('tr#' + ID + ' td:first-child select').val(course.attr('data-id'));
			
		//If this is the last element, remove the contents of the suggestions <div>
			if (course.siblings('li').length == 0) {
				course.parent().parent().empty();
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
			var char = '';
		
			for (var c = 65; c <= 90; ++c) { //Char codes: 65 = A, 90 = Z
				char = String.fromCharCode(c);
				sections += '<option value="' + char + '">' + char + '</option>';
			}
			
			sections += '</select>';
			
		//Generate the HTML to insert a new row into the target table
			var HTML = '<tr>';
			HTML += '<td><span>Course:</span>' + courses.html() + '</td>'
			HTML += '<td><span>Number:</span><input class="input-small validate[required,custom[integer],min[101],max[499]]" max="499" min="101" name="number[]" type="number" value=""></td>';
			HTML += '<td><span>Section:</span>' + sections + '</td>';
			HTML += '<td class="delete"><span></span></td>';
			HTML += '</tr>';
			
		//Append the row onto the table
			table.append(HTML);
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
						HTML += '<li data-id="' + JSON.courses[i].ID + '" data-number="' + JSON.courses[i].number + '" data-section="' + JSON.courses[i].section + '" style="background-color: ' + JSON.courses[i].color + '">';
						HTML += JSON.courses[i].course + ' ';
						HTML += JSON.courses[i].number + ' ';
						HTML += JSON.courses[i].section;
						HTML += '</li>';
					}
					
					HTML += '</ul>';
					
					$.fn.FFI_BE_Sell_Books_Assistant.courseContainer.empty().append('<h3>Suggestions (click to add):</h3>' + HTML);
					
				//Hide the loading animation
					animation.removeClass('show');
				}
			}
		});
	};
	
/**
 * The plugin settings
 *
 * @access public
 * @since  3.0
 * @type   object 
*/
	
	$.fn.FFI_BE_Sell_Books_Assistant.defaults = {
		fetchURL : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/includes/ajax/suggest.php'
	};
})(jQuery);