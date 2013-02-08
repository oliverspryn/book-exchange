(function($) {
	$(document).ready(function() {
		var images = new Array();
		var currentImage = 0;
		var dialog, confirmClassesDialog, checkISBNDialog;
		var container = $('div.imageContainer');
		var imageURLContainer = $('input.imageURL');
		var backButton = $('span.back');
		var forwardButton = $('span.forward');
		var APIKey = 'AIzaSyCCfXsNv47Xg62-Kz6opyvmn3YBPhliZ0k';
		var APIRequest = 'https://www.googleapis.com/shopping/search/v1/public/products?country=US&key=' + APIKey + '&q=';
		var localRequest = '../wp-content/plugins/book-exchange/app/system/server/suggest_local.php?ISBN=';
		
	/**
	 * Fetch an image from the local 
	 * database or from the Google 
	 * Shopping API when the book's ISBN
	 * is entered
	 * ------------------------------------
	*/
	
	//Listen for entries in the ISBN and fetch a series of book covers from the Google Shopping API
		$('input.ISBN').change(function() {
			var input = $(this);
			
		//Check and see if the input is in a valid ISBN format
			var ISBN = input.val().replace(/[^0-9a-zA-Z]/g, '');
			
			if (ISBN.length == 10 || ISBN.length == 13) {
			//Check with the local database and see if an entry for this book already exists
				$.ajax({
					'url' : localRequest + ISBN,
					success : function(data) {		
					//Empty the imageID input field so any previous image ID isn't linked to this book
						$('input.imageID').attr('value', '');
						
					//Is the book already in the database? If so, let the user know, and give them the option to auto fill the form
						if (data.toString() != 'failure') {
							data = $.parseJSON(data);
							
						//Check and see if the is an "Edition" value for this book
							var edition = '<span class="editionInfo"></span>';
							
							if (data.edition != '') {
								edition = '<span class="editionInfo"><strong>Edition:</strong> ' + data.edition + '</span>';
							}
							
						//Build the list of classes which use this book
							var classes = '';
							
							for (var i = 0; i <= data.classes.length - 1; i++) {
								classes += '<span class="class"><span class="band" style="border-left-color: ' + data.classes[i].color + ';"><span class="icon" style="background-image: url(\'../wp-content/plugins/book-exchange/app/system/images/categories/' + data.classes[i].id + '/icon_032.png\');">' + data.classes[i].name + ' ' + data.classes[i].classNum + ' ' + data.classes[i].section + '</span></span></span>'
							}
							
						//Construct the alert dialog with the fetched information from the server
							dialog = $('<section class="dialog suggestions"><h1>We Already Have this Book On Record</h1><div class="content"><div class="imagePreview"><p>Verify that the suggested book cover is correct:</p><img src="' + data.imageURL + '"><span class=\"imageID\" style=\"display: none;\"">' + data.imageID + '</span></div><div class="bookInfo"><p>Check the title, author, and edition (if available):</p><span class="titleInfo"><strong>Title:</strong> ' + data.title + '</span><span class="authorInfo"><strong>Author:</strong> ' + data.author + '</span>' + edition + '</div><div class="classInfo"><p><span class="highlight">Click on the classes</span> where you used this book (you can add more later):</p>' + classes + '</div></div><div class="buttons"><button class="green accurate">This Information Is Accurate</button><button class="red inAccurate">This Information Is Not Accurate</button></div></section>').appendTo('body').delfini_dialog();
							
						//Hide the image browser controls
							$('div.imageBrowser').addClass('hidden');
					//If it is not, all we can do is try to fetch a cover image from the Google Shopping API
						} else {
						//Change the text of the image container
							container.text('Guessing...');
							
						//Empty the images array
							images = new Array();
							
						//Reset the back button to disabled
							backButton.addClass('disabled');
							
						//Reset the forward button
							forwardButton.removeClass('disabled');
							
						//Fetch the data from the Google Shopping API
							$.ajax({
								'url' : APIRequest + ISBN,
								'dataType' : 'jsonp',
								'success' : function(data) {
									var APIItems = data.items;
									currentImage = 0;
									
									if (APIItems && APIItems != undefined) {
									//Generate the array of possible book URLs
										for (var i = 0; i <= APIItems.length - 1; i++) {
											if (APIItems[i].product.images && APIItems[i].product.images != undefined) {
												images.push(APIItems[i].product.images[0].link);
											}
										}
									}
									
								//Were images retrieved?
									if ((!APIItems && APIItems == undefined) || (APIItems && APIItems != undefined && APIItems.length == 0)) {
									//Generate the link to the default image
										var URL = document.location.href.toString().match(/(.*)\/sell-books\//)[1] + '/system/images/icons/default_book.png';
										
									//Display a standard image
										container.html('<img src="' + URL + '" />');
										$('input.imageURL').attr('value', URL);
										
									//Hide the image browser controls
										$('div.imageBrowser').addClass('hidden');
										
									//Let the user know that the reason the a cover could not be found is probably because it was entered incorrectly
										checkISBNDialog = $('<section class="dialog checkISBN" style="margin: 0px 30% 0px 30%; width: 40%;"><h1>Whoops!</h1><div class="content"><p style="font-size: 16px; margin-top: 5px;">We were unable to find a cover to match the ISBN you entered. This is usually because it was entered incorrectly. Check it again to see if you typed it in correctly.<br><br>If you did enter it in correctly, this may be a rare case where we cannot match the ISBN to a book cover.</p></div><div class="buttons"><button class="blue close">Ok, thanks</button></div></section>').appendTo('body').delfini_dialog();
									} else {
									//Put the first image in the placeholder on the page
										container.html('<img src="' + images[0] + '" />');
										$('input.imageURL').attr('value', images[0]);
										
									//Is there only one suggestion?
										if (APIItems.length == 1) {
											forwardButton.addClass('disabled');
										}
										
									//Show the image browser controls
										$('div.imageBrowser').removeClass('hidden');
										
									//Build a prompt to point out the controls, after a delay so the image can load
										if (images.length > 1) {
											setTimeout(function() {
												$('div.imageBrowser span.forward').validationEngine('showPrompt', 'Click on these arrows to browse through the images and find the best looking cover!', 'pass', 'topRight', true);
											}, 4000);
										}
									}
								}
							});
						}
					}
				});
			} else {
				if (ISBN != '') {
					checkISBNDialog = $('<section class="dialog checkISBN" style="margin: 0px 30% 0px 30%; width: 40%;"><h1>Whoops!</h1><div class="content"><p style="font-size: 16px; margin-top: 5px;"><strong>' + input.val() + '</strong> doesn\'t look like an ISBN. Check it again to see if you entered it correctly.</p></div><div class="buttons"><button class="blue close">Ok, thanks</button></div></section>').appendTo('body').delfini_dialog();
				}
			}
		});
		
	//Dismiss an Invalid ISBN dialog
		$('body').delegate('section.checkISBN div.buttons button.close', 'click', function() {
			$(this).parent().parent().delfini_dialog('close');
		});
		
	//Show that a class is selected whenever it is clicked on from the list of avaliable classes in the suggestion dialog
		$('body').delegate('section.dialog div.content div.classInfo span.class', 'click', function() {
			var selectedClass = $(this);
			
			if (selectedClass.hasClass('selected')) {
				selectedClass.removeClass('selected');
			} else {
				selectedClass.addClass('selected');	
			}
		});
		
	//Listen for when the "This Information Is Accurate" button is clicked in the suggestion dialog
		$('body').delegate('section.dialog div.buttons button.accurate', 'click', function() {
		//Validate whether or not a user has selected a class
			var dialog = $(this).parent().parent();
			var classes = dialog.children('div.content').children('div.classInfo').children('span.class.selected');
			
			/* [Abandoned idea]
			
			if (!classes.length) {
				confirmClassesDialog = $('<section class="dialog confirm" style="background: #CCCCCC; color: #000000; margin: 0px 30% 0px 30%; width: 40%;"><h1>No Classes Selected</h1><div class="content"><p style="font-size: 16px; margin-top: 5px;">We have suggested several classes which use this book. You can select them from the suggested list, or, if you haven\'t found your class in the list, add them in later.<br><br>Do you wish to continue without selecting courses from the list?</p></div><div class="buttons"><button class="blue continue">I\'ll Add Courses Later</button><button class="returnToDialog">Return and Select Courses</button></div></section>').appendTo('body').delfini_dialog();
				
				return false;
			}*/
			
		//Grab all of the needed text from the dialog
			var image = dialog.children('div.content').children('div.imagePreview').children('img').attr('src');
			var imageID = dialog.children('div.content').children('div.imagePreview').children('span.imageID').text();
			var title = dialog.children('div.content').children('div.bookInfo').children('span.titleInfo').text();
			var authorHTML = dialog.children('div.content').children('div.bookInfo').children('span.authorInfo').html();
			var authorText = dialog.children('div.content').children('div.bookInfo').children('span.authorInfo').text();
			var editionHTML = dialog.children('div.content').children('div.bookInfo').children('span.editionInfo').html();
			var editionText = dialog.children('div.content').children('div.bookInfo').children('span.editionInfo').text();
			
		//.substring() will remove the "Title: ", "Author: ", or "Edition: " that was extracted from the dialog
			$('input.titleInput').val(title.substring(7, title.length));
			$('input.authorInput').val(authorText.substring(8, authorText.length));
			$('input.editionInput').val(editionText.substring(9, editionText.length));
			
		//We can use all of the text, including the "Title: ", "Author: ", or "Edition: ", when generating the preview in the <aside> tag
			$('div.imageContainer').html('<img src="' + image + '" />');
			$('input.imageURL').attr('value', image);
			$('input.imageID').attr('value', imageID);
			$('span.titlePreview').text(title.substring(7, title.length)); //Except for the title
			$('span.authorPreview').html(authorHTML);
			
			if (editionText != '') {
				$('span.editionPreview').html(editionHTML).show();
			} else {
				$('span.editionPreview').hide()
			}
			
		//Fetch all of the selected classes and apply them to the step 2 in the form
			//var classes = dialog.children('div.content').children('div.classInfo').children('span.class.selected'); //Defined at top of function
			var selectedClasses = new Array();
			
			classes.each(function() {
				var currentClass = $(this).text();
				var classInfo = new Array();
				
			//Fetch the class name
				classInfo.push(currentClass.substring(0, currentClass.length - 6)); //Trim off the course number and section
				
			//Fetch the class number
				classInfo.push(currentClass.substring(currentClass.length - 5, currentClass.length - 2)); //Trim off the course name and section
				
			//Fetch the class section
				classInfo.push(currentClass.substring(currentClass.length - 1, currentClass.length)); //Trim off the course name and number
				
			//Store this information in the selectedClasses array
				selectedClasses.push(classInfo);
			});
			
		//Now remove any existing classes that are listed in step 2 and add in the ones seleceted from the dialog
			var targetLoc = $('section.courseInformationSection');
			var flyoutMenu = targetLoc.children('div.flyoutTemplate').html();   //The DOM we need to copy was already generated
			var sectionMenu = targetLoc.children('div.sectionTemplate').html(); //by the server and we simply need to grab it
			
		//If the user didn't select any classes, don't bother clearing the current list of classes
			if (classes.length > 0) {
				targetLoc.children('div.classUsed').remove();
			}
			
		/**
		 * Within this loop the selectedClass's data is organized thusly:
		 *  - selectedClasses[i][0] = course name
		 *  - selectedClasses[i][1] = course number
		 *  - selectedClasses[i][2] = course section
		*/
			for (var i = 0; i <= selectedClasses.length - 1; i++) {
			//Create the class container <div> and place its contents
				var newClass = $('<div class="classUsed" />');
				newClass.insertAfter(targetLoc.children('div:last'));
				newClass.append(flyoutMenu);
				newClass.append('<div class="inputWrapper"><input autocomplete="off" class="noIcon validate[required,custom[integer],min[101],max[499]]" maxlength="3" name="classNum[]" type="text" value="' + selectedClasses[i][1] + '" /><div>');
				newClass.append(sectionMenu);
				newClass.append('<span class="delete" title="Delete this class"></span>');
				
			//Select the appropriate class in the flyout and put the value in the associated hidden field
				newClass.find('div.menuWrapper ul.categoryFly li ul li').each(function() {
					var currentClass = $(this);
					
				//Deselect the old selected item...
					if (currentClass.hasClass('selected')) {
						currentClass.removeClass('selected');
					}
					
				//... and select the new item when we come across the right one
					if (currentClass.text() == selectedClasses[i][0]) {
						currentClass.addClass('selected');
						
					//Update the text field value
						newClass.find('div.menuWrapper input').attr('value', currentClass.attr('data-value'));
					}
				});
				
			//Select the appropriate class section
				newClass.find('div.dropdownWrapper ul.dropdown li').each(function() {
					var currentSection = $(this);
					
				//Deselect the old selected item...
					if (currentSection.hasClass('selected')) {
						currentSection.removeClass('selected');
					}
					
				//... and select the new item when we come across the right one
					if (currentSection.text() == selectedClasses[i][2]) {
						currentSection.addClass('selected');
						
					//Update the text field value
						newClass.find('div.dropdownWrapper input').attr('value', currentSection.text());
					}
				});
			}
			
		//Close the dialog
			dialog.delfini_dialog('close');
		});
		
	//Dismiss a "No Classes Suggested" dialog [abandoned idea]
		/*$('body').delegate('section.confirm div.buttons button.returnToDialog', 'click', function() {
			$(this).parent().parent().delfini_dialog('close');
		});*/
		
	//Listen for when the "This Information Is Not Accurate" button is clicked in the suggestion dialog
		$('body').delegate('section.dialog div.buttons button.inAccurate', 'click', function() {
			var URL = APIRequest + $('input.ISBN').val();
			
		//Close the dialog
			$(this).parent().parent().delfini_dialog('close');
			
		//Change the text of the image container
			container.text('Guessing...');
			
		//Empty the images array
			images = new Array();
			
		//Reset the back button to disabled
			backButton.addClass('disabled');
			
		//Reset the forward button
			forwardButton.removeClass('disabled');
			
		//Fetch a suggestion book cover from the Google Shopping API
			$.ajax({
				'url' : URL,
				'dataType' : 'jsonp',
				'success' : function(data) {
					var APIItems = data.items;
					currentImage = 0;
					
					if (APIItems && APIItems != undefined) {
					//Generate the array of possible book URLs
						for (var i = 0; i <= APIItems.length - 1; i++) {
							if (APIItems[i].product.images && APIItems[i].product.images != undefined) {
								images.push(APIItems[i].product.images[0].link);
							}
						}
					}
					
				//Were images retrieved?
					if ((!APIItems && APIItems == undefined) || (APIItems && APIItems != undefined && APIItems.length == 0)) {
					//Generate the link to the default image
						var URL = document.location.href.toString().match(/(.*)\/sell-books\//)[1] + '/system/images/icons/default_book.png';
						
					//Display a standard image
						container.html('<img src="' + URL + '" />');
						$('input.imageURL').attr('value', URL);
						
					//Hide the image browser controls
						$('div.imageBrowser').addClass('hidden');
					} else {
					//Put the first image in the placeholder on the page
						container.html('<img src="' + images[0] + '" />');
						$('input.imageURL').attr('value', images[0]);
						
					//Is there only one suggestion?
						if (APIItems.length == 1) {
							forwardButton.addClass('disabled');
						}
						
					//Show the image browser controls
						$('div.imageBrowser').removeClass('hidden');
						
					//Build a prompt to point out the controls, after a delay so the image can load
						if (images.length > 1) {
							setTimeout(function() {
								$('div.imageBrowser span.forward').validationEngine('showPrompt', 'Click on these arrows to browse through the images and find the best looking cover!', 'pass', 'topRight', true);
							}, 4000);
						}
					}
				}
			});
		});
		
	/**
	 * Browse through a list of avaliable
	 * images that was fetched from the
	 * Google Shopping API
	 * ------------------------------------
	*/
		forwardButton.click(function() {
		//Are we already at the end of the list?
			if (!forwardButton.hasClass('disabled') && images[currentImage + 1] != undefined) {
				currentImage ++;
				container.html('<img src="' + images[currentImage] + '" />');
				imageURLContainer.attr('value', images[currentImage]);
				
			//Does another image after this one exist, or should the button become disabled?
				if (images[currentImage + 1] == undefined) {
					forwardButton.addClass('disabled');
				}
				
			//Make sure that the back button is enabled
				backButton.removeClass('disabled');
			}
		});
		
		backButton.click(function() {
		//Are we already at the end of the list?
			if (!backButton.hasClass('disabled') && images[currentImage - 1] != undefined) {
				currentImage --;
				container.html('<img src="' + images[currentImage] + '" />');
				imageURLContainer.attr('value', images[currentImage]);
				
			//Does another image after this one exist, or should the button become disabled?
				if (images[currentImage - 1] == undefined) {
					backButton.addClass('disabled');
				}
				
			//Make sure that the forward button is enabled
				forwardButton.removeClass('disabled');
			}
		});
	
	/**
	 * Update the preview sidebar as the
	 * form is filled out
	 * ------------------------------------
	*/
	
	//Update the title preview
		$('input.titleInput').on('change', function() {
			var input = $(this);
			
			if (input.val() == '') {
				$('span.titlePreview').html('&lt;Book Title&gt;');
			} else {
				$('span.titlePreview').text(input.val());
			}
		});
		
	//Update the author preview
		$('input.authorInput').on('change', function() {
			var input = $(this);
			
			if (input.val() == '') {
				$('span.authorPreview').html('<strong>Author:</strong> &lt;Book Title&gt;');
			} else {
				$('span.authorPreview').html('<strong>Author:</strong> ' + input.val());
			}
		});
		
	//Update the edition preview
		$('input.editionInput').on('change', function() {
			var input = $(this);
			
			if (input.val() == '') {
				$('span.editionPreview').html('<strong>Edition:</strong> ').hide();
			} else {
				$('span.editionPreview').html('<strong>Edition:</strong> ' + input.val()).show();
			}
		});
		
	//Update the price preview
		$('input.priceInput').on('change', function() {
			var input = $(this);
			var price = parseInt(input.val().replace(/[^0-9]/g, '')); //Strip off the decimal
			
			if (input.val() != '' && price >= 0 && price <= 99999) {
				$('span.pricePreview span').text('$' + parseFloat(input.val()).toFixed(2));
				input.attr('value', parseFloat(input.val()).toFixed(2));
			} else {
				$('span.pricePreview span').text('$0.00');
				input.attr('value', '0.00');
			}
		});
		
	/**
	 * Add or delete rows from the class
	 * table
	 * ------------------------------------
	*/
	
	//Remove a table row
		$('body').delegate('span.delete', 'click', function() {
		//Check and see if there is at least one other row in this table
			if ($('div.classUsed').length > 1) {
				$(this).parent().remove(); //Remove the row
			} else {
				$('<section class="dialog" style="margin: 0px 35% 0px 35%; width: 30%;"><h1>Whoops!</h1><div class="content"><p style="font-size: 16px; margin-top: 5px;">You must have at least one class in this list.</p></div><div class="buttons"><button class="blue close">Ok, thanks</button></div></section>').appendTo('body').delfini_dialog();
			}
		});
		
	//Clear the dialog created by the event handler above
		$('body').delegate('button.close', 'click', function() {
			$(this).parent().parent().delfini_dialog('close');
		});
		
	//Add a table row
		$('span.add').click(function() {
		//Fetch the target location and dynmaic menus that are hidden from the user in the DOM
			var targetLoc = $('section.courseInformationSection');
			var flyoutMenu = targetLoc.children('div.flyoutTemplate').html();   //The DOM we need to copy was already generated
			var sectionMenu = targetLoc.children('div.sectionTemplate').html(); //by the server and we simply need to grab it
			
		//Construct the row
			var newClass = $('<div class="classUsed" />');
			newClass.insertAfter(targetLoc.children('div:last'));
			newClass.append(flyoutMenu);
			newClass.append('<div class="inputWrapper"><input autocomplete="off" class="noIcon validate[required,custom[integer],min[101],max[499]]" maxlength="3" name="classNum[]" type="text" value="" /><div>');
			newClass.append(sectionMenu);
			newClass.append('<span class="delete" title="Delete this class"></span>');
		});
		
	/**
	 * Additional jQuery plugins
	 * ------------------------------------
	*/
		
	//Instantiate the tooltip
		$(':text').tooltip({
			position : 'center right',
			offset : [-2, 10],
			effect : 'fade'
		});
		
	//Instantiate the validator
		$('form').validationEngine({
			'validationEventTrigger' : 'submit',
			'promptPosition' : 'topRight',
			'autoHidePrompt' : 'true',
			'autoHideDelay' : 7000
		});
		
	/**
	 * Misc
	 * ------------------------------------
	*/
	
	//Update the hidden input on-click to either redirect to back to this page to add another book or back to account page
		$('input.again').click(function() {
			$('input.redirect').attr('value', '1');
		});
		
		$('input.finish').click(function() {
			$('input.redirect').attr('value', '0');
		});
	
	//Listen for the cancel button and redirect the user to the main book exchange page
		$('input.cancel').click(function() {
			document.location.href = '../';
		});
	});
})(jQuery);
	
//Create a custom validation function for the image validation
	function checkImage(field, rules, i, options) {
		if (field.val() == '') {
			return '* You will probably want an image to go with your book';
		}
	}

//Create a custom validation function for the ISBN
	function checkISBN(field, rules, i, options) {
		var ISBN = field.val().replace(/[^0-9a-zA-Z]/g, ''); //Strip off any dashes from the ISBN
		
		if (ISBN.length == 10 || ISBN.length == 13) {
			//No problem!
		} else {
			return '* An ISBN will have either 10 or 13 numbers, dashes are okay';
		}
	}
	
//Create a custom validation function for the price
	function checkPrice(field, rules, i, options) {
		var price = parseInt(field.val().replace(/[^0-9]/g, '')); //Strip off the decimal
		var priceRegex = /^\d+(\.\d{2})?$/;
		
		if (price >= 0 && price <= 99999 && priceRegex.test(field.val())) {
			//No problem!
		} else {
			return '* Valid prices range from $0.00 to $999.99';
		}
	}