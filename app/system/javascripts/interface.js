/*!
 * jQuery Cookie Plugin
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2011, Klaus Hartl
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/GPL-2.0
 */
 
(function($) {
    $.cookie = function(key, value, options) {

        // key and at least value given, set cookie...
        if (arguments.length > 1 && (!/Object/.test(Object.prototype.toString.call(value)) || value === null || value === undefined)) {
            options = $.extend({}, options);

            if (value === null || value === undefined) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires, t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            value = String(value);

            return (document.cookie = [
                encodeURIComponent(key), '=', options.raw ? value : encodeURIComponent(value),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path    ? '; path=' + options.path : '',
                options.domain  ? '; domain=' + options.domain : '',
                options.secure  ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var decode = options.raw ? function(s) { return s; } : decodeURIComponent;

        var pairs = document.cookie.split('; ');
        for (var i = 0, pair; pair = pairs[i] && pairs[i].split('='); i++) {
            if (decode(pair[0]) === key) return decode(pair[1] || ''); // IE saves cookies with empty string as "c; ", e.g. without "=" as opposed to EOMB, thus pair[1] may be undefined
        }
        return null;
    };
})(jQuery);

$(document).ready(function() {
/**
 * If the user has not logged, expand
 * the login <section> when the 
 * "Sell Books" button is clicked
 * ------------------------------------
*/

	$('a.openLogin, button.openLogin').click(function(event) {
		event.preventDefault();
		
	//The login panel will exist if the user is not logged
		var trigger = $(this);
		var panel = $('section.login');
		
		if (panel.length) {
		//Open the login panel
			panel.slideDown();
			
		//Slide it into view
			$('html, body').animate({
				'scrollTop' : '0px'
			}, 'slow');
			
		//Fill the redirect form element
			if (trigger && trigger.attr('href') && trigger.attr('href') != undefined && trigger.attr('href') != '') {
				$('input.redirect').val(trigger.attr('href'));
			} else {
				$('input.redirect').val(trigger.attr('data-redirect'));
			}
		} else {
			document.location = 'sell-books/';
		}
	});
	
/**
 * Book exchange category flyout menu
 * ------------------------------------
*/

	var events = $('ul.categoryFly');
	var hovered = false;
	
//Let the application know that the menu is being hovered over
	events.bind('menuActive', function() {
		hovered = true;
	});
	
//Let the application know that the menu is being hovered out
	events.bind('menuInactive', function() {
		hovered = false;
	});

//Expand the menu when it is clicked on
	$('body').delegate('ul.categoryFly:not(.open)', 'click', function() {
		if (!hovered) {
			var documentWidth = $(document).width();
			var documentHeight = $(document).height();
			var menu = $(this);
			menu.addClass('open');
			menu.children('li').css('display', 'table-cell');
			
		//Calculate the required height by seeing how many rows are in the first column and multiplying by the height of each one
			var items = menu.children('li:first').children('ul').children('li').length;
			var height = 40 * items;
		
		//Calculate the required width by seeing how many columns exist and multiplying by the width of each one
			var items = menu.children('li').length;
			var width = 250 * items;
			
		//Calculate the offeset height from the top and left so that this can be floated overtop of the other elements and not push elements as it opens
			var top = menu.offset().top;
			var left = menu.offset().left;
			var newTop = top;
			var newLeft = left;
			
		//Will the menu fly off the bottom of the screen? If so, adjust the position just enough so that the entire menu is visible
			if (top + height > documentHeight) {
				newTop = documentHeight - (documentHeight - top) + (documentHeight - (top + height)) - 20; //-20 is for aesthetics
			}
			
		//Will the menu fly off the side of the screen? If so, just align the whole menu to the vertical center
			if (left + width > documentWidth) {
				newLeft = (documentWidth - width) / 2;
			}
			
		//IE8 and below renders complex animations very poorly, so just open the menu without animations
			if ($.browser.msie && parseInt($.browser.version, 10) <= 8) {
				menu.css({
					'left' : newLeft + 'px',
					'height' : height + 'px',
					'position' : 'absolute',
					'top' : newTop + 'px',
					'width' : width + 'px',
					'z-index' : '5'
				}).find('li ul li:not(.selected)').css({
					'display' : 'list-item',
					'height' : '40px',
					'width' : '250px'
				});
				
				events.trigger('menuActive');
			} else {
			//Slide the unselected menu items into view
				menu.css({
					'left' : left + 'px',
					'position' : 'absolute',
					'top' : top + 'px',
					'z-index' : '5'
				}).animate({
					'left' : newLeft + 'px',
					'height' : height + 'px',
					'top' : newTop + 'px',
					'width' : width + 'px'
				}, function() {
				//Let the application know that the menu is being hovered over
					events.trigger('menuActive');
				}).find('li ul li:not(.selected)').css({
					'display' : 'list-item',
					'height' : '0px',
					'width' : '0px'
				}).animate({
					'height' : '40px',
					'width' : '250px'
				});
			}
		}
	});
	
//If something outside of the menu is clicked on, collapse the menu
	$(document).click(function(e) {
		if (!$(e.target).is('ul.categoryFly.open') && !$(e.target).parents().is('ul.categoryFly.open') && $('ul.categoryFly.open').length) {
			var menu = $('ul.categoryFly.open');
			menu.removeClass('open');
			var left = menu.parent().offset().left;
			var top = menu.parent().offset().top;
			
		//IE8 and below renders complex animations very poorly, so just close the menu without animations
			if ($.browser.msie && parseInt($.browser.version, 10) <= 8) {
			//Remove the styles which were added by the mouseover handler
				menu.removeAttr('style').find('li ul li:not(.selected)').removeAttr('style'); 
				
			//Let the application know that the menu has been hovered out
				events.trigger('menuInactive');
			} else {
			//Slide the unselected menu items out of the way
				menu.animate({
					'left' : left + 'px',
					'height' : '40px',
					'top' : top + 'px',
					'width' : '198px'
				}, function() {
				//Remove the styles which were added by the mouseover handler
					menu.removeAttr('style').find('li ul li:not(.selected)').removeAttr('style'); 
					
				//In order to fix an issue with Chrome, where the <li> columns only collapse to 1px wide, hide all of the unnecessary columns
					menu.children('li').each(function() {
						var currentColumn = $(this);
						
						if (!currentColumn.has('li.selected').length) {
							currentColumn.css('display', 'none');
						}
					});
					
				//Let the application know that the menu has been hovered out
					events.trigger('menuInactive');
				}).find('li ul li:not(.selected)').animate({
					'height' : '0px',
					'width' : '0px'
				});
			}
		}
	});
	
//Slide the menu out of view on-click
	$('body').delegate('ul.categoryFly li ul li', 'click', function() {
		if (hovered) {
			var item = $(this);
			var menu = item.parent().parent().parent();
			menu.removeClass('open');
			var left = menu.parent().offset().left;
			var top = menu.parent().offset().top;
			
		//Remove the selected class from the previously selected item and add the selected class to the clicked one
			menu.find('li ul li.selected').removeClass('selected').css({
				'display' : 'list-item',
				'height' : '40px',
				'width' : '250px'
			});
			
			item.addClass('selected');
			
		//Grab the value of the selected item and store it in the associated hidden element
			menu.parent().find('input').attr('value', item.attr('data-value'));
			
		//IE8 and below renders complex animations very poorly, so just close the menu without animations
			if ($.browser.msie && parseInt($.browser.version, 10) <= 8) {
			//Remove the styles which were added by the mouseover handler
				menu.css({
					'left' : left + 'px',
					'height' : '40px',
					'top' : top + 'px',
					'width' : '198px'
				}).removeAttr('style').find('li ul li:not(.selected)').css({
					'height' : '0px',
					'width' : '0px'
				}).removeAttr('style'); 
				
			//Let the application know that the menu has been hovered out
				events.trigger('menuInactive');
			} else {
			//Slide the unselected menu items out of the way
				menu.animate({
					'left' : left + 'px',
					'height' : '40px',
					'top' : top + 'px',
					'width' : '198px'
				}, function() {
				//Remove the styles which were added by the mouseover handler
					menu.removeAttr('style').find('li ul li:not(.selected)').removeAttr('style'); 
					
				//In order to fix an issue with Chrome, where the <li> columns only collapse to 1px wide, hide all of the unnecessary columns
					menu.children('li').each(function() {
						var currentColumn = $(this);
						
						if (!currentColumn.has('li.selected').length) {
							currentColumn.css('display', 'none');
						}
					});
					
				//Let the application know that the menu has been hovered out
					events.trigger('menuInactive');
				}).find('li ul li:not(.selected)').animate({
					'height' : '0px',
					'width' : '0px'
				});
			}
		}
	});
	
/**
 * Load a Wikipedia article for each
 * category listing
 * ------------------------------------
*/

	if (parseInt($('article.description').length) > 0) {
		var container = $('article.description');
		
		$.ajax({
			url : 'http://en.wikipedia.org/w/api.php?action=query&prop=extracts&format=json&exintro=1&redirects&callback=?',
			dataType: 'json',
			data : {
				titles : container.parent().parent().children('header.styled').children('h1').text() //Fetched from the page <header> element
			},
			success : function(data) {	
			// At some point in the JSON structure, we will need the article ID, which is different for every article, to access the 
			// the body of the article. Since this is an unknown, the we will need to walk through the strcuture and find the ID of
			// the article
				var keys = new Array();
				
				for (var key in data.query.pages) {
					if (data.query.pages.hasOwnProperty(key)){
						keys.push(key);
					}
				}
				
			//If the key is -1, then Wikipedia couldn't find and article on the subject
				if (keys[0] != '-1') {
				//Put the article from Wikipieda in the appropriate container, and remove the loading class
					var article = data.query.pages[keys[0]].extract;
					container.children('section.article').removeClass('loading').html(article);
					
				//Is all of the article being displayed, or should a "Read More/Less" button display?
					var normalHieght = container.children('section.article').css('max-height', 'none').height();
					container.children('section.article').removeAttr('style');
					
					if (normalHieght > 350) {
						container.children('a.buttonLink').removeAttr('style');
					}
					
				//Show the disclaimer
					container.children('section.disclaimer').removeClass('hidden');
				} else {
					container.hide();
				}
			}
		});
	}
	
/**
 * Slide the Wikipedia article into 
 * full view
 * ------------------------------------
*/
	
	$('article.description a.buttonLink').click(function() {
		var button = $(this);
		var article = button.siblings('section.article');
		var beforeHeight = article.height();
		var normalHieght = article.css('max-height', 'none').height();
		article.css('height', beforeHeight);
		
		if (button.text() == 'Read More') {
		//Resize the aritcle container
			article.animate({
				'height' : normalHieght + 'px'
			}, 1000, function() {
			//Change the text of the button
				button.html('<span>Read Less</span>');
			});
		} else {
		//Resize the aritcle container
			article.animate({
				'height' : '350px'
			}, 1000, function() {
			//Reset the style tag after animation
				article.removeAttr('style');
				
			//Change the text of the button
				button.html('<span>Read More</span>');
			});			
		}
	});
	
/**
 * Make an offer for a book
 * ------------------------------------
*/
	
	$('a.buttonLink.buy').click(function() {
		var title = $(this).siblings('span.title, a.title').text();
		var id = $(this).attr('data-fetch');
		
	//Wait... is the user logged in? The login panel will exist if not
		if ($('section.login').length) {
			var queryString = document.location.href.split('?');
			
			if (queryString[1] && queryString[1] != '') {
				queryString = '?' + queryString['1'];
			}
			
			document.location.href = location.substring(0, location.indexOf('book-exchange')) + 'login.php?accesscheck=' + encodeURIComponent(document.location.pathname + queryString) + "&message=required";
		} else {
			var parentDialog = $('<section class="purchase" title="Purchase <i>' + title + '</i>"><div class="loading">Please wait...</div></section>').dialog({
				'height' : 600,
				'modal' : true,
				'resizable' : false,
				'width' : 900,
				'buttons' : {
					'Send Request' : function() {
						var confirmDialog = $('<section class="confirm" title="Confirm Request"></section>')
						.html('<p><span class="ui-icon ui-icon-alert"></span>Are you sure you wish to send a request to purchase this book at the listed price from the seller? Your name and email address will be shared with this person.</p><p>Clicking &quot;Yes&quot; will immediately initiate the transaction.</p>')
						.dialog({
							'height' : 300,
							'modal' : true,
							'resizable' : false,
							'width' : 500,
							'buttons' : {
								'Yes' : function() {
									var unixTime = Math.round(new Date().getTime() / 1000);
									var requestURL = 'http://sga.forwardfour.com/book-exchange/system/server/purchase-request.php?id=' + id + '&key=' + unixTime + '&UID=' + $.cookie('UID') + '&callback=?';
									
									
								//Close the open dialogs
									confirmDialog.dialog('close').remove();
									parentDialog.dialog('close').remove();
									
								//Display a sending request notice
									var message = $('<div class="center"><div class="message">Sending purchase request...</div></div>').appendTo('body');
									
									$.ajax({
										'url' : requestURL,
										'dataType' : 'json',
										'success' : function(data) {
											data = data.message;
										
											if (data == 'success') {
											//Update the message to the user
												message.html('<div class="success">Purchase request sent! The seller will respond via email.</div>');
												
											//Remove the message after a certain period of time
												setTimeout(function() {
													message.fadeOut(400, function() {
														$(this).remove();
													});
												}, 10000);
											} else {
											//Update the message to the user
												message.html('<div class="error">' + data + '</div>');
											}
										}
									});
								}, 'No' : function() {
									$(this).dialog('close').remove();
								}
							}
						});
					}, 'Help' : function() {
						var helpDialog = $('<section class="helpDialog" title="How Does This Work?"></section>')
						.html('<p>Once you click the &quot;Send Request&quot; button, here is what will happen: <ol><li>the seller will be sent an email notifying him or her that you would like to purchase this book</li><li>this person will be prompted to send you a reply via email with a time and place to meet in person</li><li>you meet the seller at the designated time and location to recieve your book</li></ol></p>')
						.dialog({
							'height' : 340,
							'modal' : true,
							'resizable' : false,
							'width' : 640,
							'buttons' : {
								'Close' : function() {
									$(this).dialog('close').remove();
								} 
							}
						});
					}, 'Cancel' : function() {
						$(this).dialog('close').remove();
					}
				},
				'create' : function() {
					var dialog = $(this);
					var location = document.location.href;
					var requestURL = location.substring(0, location.indexOf('book-exchange')) + 'book-exchange/system/server/purchase-data.php?id=' + id;
					
					$.ajax({
						'dataType' : 'json',
						'url' : requestURL,
						'success' : function(data) {
							var HTML = '<aside class="bookInfo">';
							HTML += '<div class="cover"><img src="' + data.imageURL + '" /></div>';
							HTML += '<span class="previewTitle">' + data.title + '</span>';
							HTML += '<span class="buttonLink big"><span>$' + data.price + '</span></span>';
							HTML += '<span class="previewDetails"><strong>ISBN:</strong> ' + data.ISBN + '</span>';
							HTML += '<span class="previewDetails"><strong>Author:</strong> ' + data.author + '</span>';
							
							if (data.edition != '') {
								HTML += '<span class="previewDetails"><strong>Edition:</strong> ' + data.edition + '</span>';
							}
							
						//Conditionally format the condition statement
							if (data.condition == "Excellent") {
								HTML += '<span class="excellent">Excellent Condition</span>';
							} else if (data.condition == "Very Good") {
								HTML += '<span class="veryGood">Very Good Condition</span>';
							} else if (data.condition == "Good") {
								HTML += '<span class="good">Good Condition</span>';
							} else if (data.condition == "Fair") {
								HTML += '<span class="fair">Fair Condition</span>';
							} else if (data.condition == "Poor") {
								HTML += '<span class="poor">Poor Condition</span>';
							}
							
						//Conditionally format the written in statement
							if (data.written == 'Yes') {
								HTML += '<span class="marks">Has Writing or Markings</span>';
							} else {
								HTML += '<span class="marks">Has Writing or Markings</span>';
							}
							
							HTML += '</aside><section class="main">';
							
							if (data.comments != '') {
								HTML += '<h2 class="comments">Seller Comments</h2><div class="comments">' + data.comments + '</div>';
							}
							
							HTML += '<h2 class="classes">Classes That Use This Book</h2><div class="classes"><ul>';
							
						//Split the class data into an array and display each class individually
							var location = document.location.href;
							var name = data.name.split(',');
							var number = data.number.split(',');
							var section = data.section.split(',');
							var classID = data.classID.split(',');
							var icon;
							
							for(var i = 0; i <= name.length - 1; i++) {
								icon = location.substring(0, location.indexOf('book-exchange')) + 'data/book-exchange/icons/' + classID[i] + '/icon_032.png';
								
								HTML += '<li><img src="' + icon + '" title="' + name[i] + '" /><span class="courseDetails">' + number[i] + ' ' + section[i] + '</li>';
							}
							
							HTML += '</ul></div>';
							
						//Write out the seller's information
							var classDef = '';
							
							if (data.emailAddress2 != '' || data.emailAddress3 != '') {
								classDef = ' class="extended"';
							}
							
							HTML += '<h2 class="seller">Seller Information</h2>';
							HTML += '<div class="seller">';
							HTML += '<span class="details"><strong' + classDef + '>Name:</strong> ' + data.firstName + ' ' + data.lastName + '</span>';
							HTML += '<span class="details"><strong' + classDef + '>Email:</strong> <a href="mailto:' + data.emailAddress1 + '">' + data.emailAddress1 + '</a></span>';
							
							if (data.emailAddress2 != '') {
								HTML += '<span class="details"><strong' + classDef + '>Alternate email:</strong> <a href="mailto:' + data.emailAddress2 + '">' + data.emailAddress2 + '</a></span>';
							}
							
							if (data.emailAddress3 != '') {
								HTML += '<span class="details"><strong' + classDef + '>Alternate email:</strong> <a href="mailto:' + data.emailAddress3 + '">' + data.emailAddress3 + '</a></span>';
							}
							
							HTML += '</div></section>';
							
							dialog.html(HTML);
						}
					});
				}
			});
		}
	});
	
/**
 * Make an offer for a book, without
 * previewing its details
 * ------------------------------------
*/
	
	$('a.buttonLink.buyDirect').click(function() {
		var id = $(this).attr('data-fetch');
		var redirect = document.location.pathname + '?id=' + id;
		var location = document.location.href;
		var login = location.substring(0, location.indexOf('book-exchange')) + 'login.php?accesscheck=' + encodeURIComponent(redirect) + "&message=required";
		var unixTime = Math.round(new Date().getTime() / 1000);
		var requestURL = 'http://sga.forwardfour.com/book-exchange/system/server/purchase-request.php?id=' + id + '&key=' + unixTime + '&UID=' + $.cookie('UID') + '&callback=?';
		
	//The login panel will not exist if the user is logged in
		if ($('section.login').length) {
			document.location.href = login;
		} else {
			var confirmDialog = $('<section class="confirm" title="Confirm Request"></section>')
			.html('<p><span class="ui-icon ui-icon-alert"></span>Are you sure you wish to send a request to purchase this book at the listed price from the seller? Your name and email address will be shared with this person.</p><p>Clicking &quot;Yes&quot; will immediately initiate the transaction.</p>')
			.dialog({
				'height' : 300,
				'modal' : true,
				'resizable' : false,
				'width' : 500,
				'buttons' : {
					'Yes' : function() {					
					//Close the open dialogs
						confirmDialog.dialog('close').remove();
						
					//Display a sending request notice
						var message = $('<div class="center"><div class="message">Sending purchase request...</div></div>').appendTo('body');
						
						$.ajax({
							'url' : requestURL,
							'dataType' : 'json',
							'success' : function(data) {
								data = data.message;
							
								if (data == 'success') {
								//Update the message to the user
									message.html('<div class="success">Purchase request sent! The seller will respond via email.</div>');
									
								//Remove the message after a certain period of time
									setTimeout(function() {
										message.fadeOut(400, function() {
											$(this).remove();
										});
									}, 10000);
								} else if (data == 'You are not logged in') {								
									document.location.href = login;
								} else {
								//Update the message to the user
									message.html('<div class="error">Purchase request could not be sent. Please refresh the page and try again.</div>');
								}
							}
						});
					}, 'No' : function() {
						$(this).dialog('close').remove();
					}, 'Help' : function() {
						var helpDialog = $('<section class="helpDialog" title="How Does This Work?"></section>')
						.html('<p>Once you click the &quot;Yes&quot; button, here is what will happen: <ol><li>the seller will be sent an email notifying him or her that you would like to purchase this book</li><li>this person will be prompted to send you a reply via email with a time and place to meet in person</li><li>you meet the seller at the designated time and location to recieve your book</li></ol></p>')
						.dialog({
							'height' : 340,
							'modal' : true,
							'resizable' : false,
							'width' : 640,
							'buttons' : {
								'Close' : function() {
									$(this).dialog('close').remove();
								} 
							}
						});
					}
				}
			});
		}
	});
	
/**
 * Prevent a user from buying their 
 * own book
 * ------------------------------------
*/
	
	$('a.buttonLink.noBuy').click(function() {
		$('<section class="confirm" title="Rest Assured"></section>')
		.html('<p>This is your book you just clicked on. So, you already have it. ;)</p>')
		.dialog({
			'height' : 250,
			'modal' : true,
			'resizable' : false,
			'width' : 500,
			'buttons' : {
				'Gotcha' : function() {
					$(this).dialog('close').remove();
				}
			}
		});
	});
	
/**
 * Provide search suggestions when 
 * entering a search query
 * ------------------------------------
*/
	
	var location = document.location.href;
	var requestURL = location.substring(0, location.indexOf('book-exchange')) + 'book-exchange/system/server/suggestions.php';
	
	$('input.search.full').autocomplete({
		'source' : requestURL,
		'minLength' : 2,
		'select' : function(event, ui) {
			var input = $(this);
			var searchBy = input.parent().parent().children('div.controls').find('div.dropdownWrapper ul li.selected');
			
			if (searchBy.attr('data-value') && searchBy.attr('data\-value') != undefined && searchBy.attr('data\-value') != '') {
				searchBy = searchBy.attr('data\-value');
			} else {
			//If searchBy gathers values from multiple dropdown menus, then the .text() method will combine
			//the text values of all located items. We only need the first item we find
				searchBy = $(searchBy[0]).text();
			}
			
		//If we are searching by course, then we will need to update the hidden input of the flyout menu
		//to be search under the selected course
			if (searchBy == 'course') {
				var courseID = ui.item.ID;
				input.parent().parent().children('div.controls').find('div.menuWrapper input').attr('value', courseID);
			}
			
			input.val(ui.item.label).parent().parent().submit();
		}, 'search' : function(event, ui) {
			var search = $(this);
			var searchBy = search.parent().parent().children('div.controls').find('div.dropdownWrapper ul li.selected');
			
			if (searchBy.attr('data-value') && searchBy.attr('data\-value') != undefined && searchBy.attr('data\-value') != '') {
				searchBy = searchBy.attr('data\-value');
			} else {
			//If searchBy gathers values from multiple dropdown menus, then the .text() method will combine
			//the text values of all located items. We only need the first item we find
				searchBy = $(searchBy[0]).text();
			}
			
			var searchIn = search.parent().parent().children('div.controls').find('div.menuWrapper ul li ul li.selected').attr('data\-value');
			
			if (!searchIn || (searchIn && searchIn == '') || searchIn == undefined) {
				searchIn = search.parent().parent().children('input[type=hidden]').val();
			}
			
			search.autocomplete('option', 'source', requestURL + '?searchBy=' + searchBy + '&category=' + searchIn);
		}
	});	

	$['ui']['autocomplete'].prototype['_renderItem'] = function(ul, item) {
		var details;		

		if (item.total == 1) {
			details = '1 book avaliable for $' + item.price; 
		} else {
			details = item.total + ' books starting at $' + item.price;
		}
		
		return $('<li />').data('item.autocomplete', item).append($('<a title="' + item.label + '"></a>').html('<img src="' + item.image + '" /><span class="title label">' + item.label + '</span><span class="details byLine">' + item.byLine + '</span><span class="details total">' + details + '</span>')).appendTo(ul);
	};
	
/**
 * Update a user's profile
 * ------------------------------------
*/
		
	$('button.updateProfile').click(function() {
		$('<section class="update" title="Update Profile"></section>')
		.html('<form action="index.php" class="updateProfile"><span class="row"><strong>First name:</strong> <input autocomplete="off" class="first noIcon validate[required]" name="first" type="text" /></span><span class="row"><strong>Last name:</strong> <input autocomplete="off" class="last noIcon validate[required]" name="last" type="text" /></span><span class="row"><strong>Email:</strong> <input autocomplete="off" class="emailAddress1 noIcon validate[required,custom[email]]" name="emailAddress1" type="text" /></span><span class="row"><strong>Alternate email:</strong> <input autocomplete="off" class="emailAddress2 noIcon validate[custom[email]]" name="emailAddress2" type="text" /></span><span class="row"><strong>Alternate email:</strong> <input autocomplete="off" class="emailAddress3 noIcon validate[custom[email]]" name="emailAddress3" type="text" /></span><br><br><span class="row notification">Leave blank to keep your current password</span><span class="row"><strong>Password:</strong> <input autocomplete="off" class="password noIcon" id="password" name="password" type="password" /></span><span class="row"><strong>Password (again):</strong> <input autocomplete="off" class="confirm noIcon validate[equals[password]]" name="confirm" type="password" /></span></form>')
		.dialog({
			'height' : 510,
			'modal' : true,
			'resizable' : false,
			'width' : 410,
			'create' : function() {
			//Grab the values from the page...
				var page = $('section.profile');
				
			//... and plug them into the form
				var form = $('section.update');
				form.find('input.first').val(page.find('span.row span.firstName').text());
				form.find('input.last').val(page.find('span.row span.lastName').text());
				form.find('input.emailAddress1').val(page.find('span.row a.emailAddress1').text());
				var emailAddress2 = page.find('span.row a.emailAddress2').text();
				var emailAddress3 = page.find('span.row a.emailAddress3').text();
				
				if (emailAddress2 == 'None given') {
					form.find('input.emailAddress2').val('');
				} else {
					form.find('input.emailAddress2').val(emailAddress2);
				}
				
				if (emailAddress3 == 'None given') {
					form.find('input.emailAddress3').val('');
				} else {
					form.find('input.emailAddress3').val(emailAddress3);
				}
			}, 'open' : function() {
			//Instantiate the jQuery validation engine
				$('section.update form.updateProfile').validationEngine({
					'relative' : true,
					'overflownDIV' : 'section.update',
					'promptPosition' : 'bottomLeft',
					'autoHidePrompt' : 'true',
					'autoHideDelay' : 7000,
					'scroll' : false
				});
			}, 'buttons' : {
				'Update' : function() {
				//Validate the form
					if (!$('form.updateProfile').validationEngine('validate')) {
						return false;
					}
					
					var hash = "(Cn%%fJV5J";
					
				//Grab all of the data from the form
					var form = $('section.update');
					var first = form.find('input.first').val();
					var last = form.find('input.last').val();
					var emailAddress1 = form.find('input.emailAddress1').val();
					var emailAddress2 = form.find('input.emailAddress2').val();
					var emailAddress3 = form.find('input.emailAddress3').val();
					var password = form.find('input.password').val();
					var confirm = form.find('input.confirm').val();
					
				//Are the passwords the same?
					if (password === confirm) {
						if (password != '') {
							password = md5(confirm + '_' + hash);
						} else {
							password = '';
						}
					} else {
						$('<section class="passwordUnmatch" title="Check Your Passwords"></section>')
						.html('<p><span class="ui-icon ui-icon-alert"></span>Your password don\'t match. Try them again!</p>')
						.dialog({
							'height' : 220,
							'modal' : true,
							'resizable' : false,
							'width' : 320,
							'buttons' : {
								'OK' : function() {
									$(this).dialog('close').remove();
								}
							}
						});
						
						return false;
					}
					
				//Update the HTML page
					var page = $('section.profile');
					var form = $('section.update');
					page.find('span.row span.firstName').text(form.find('input.first').attr('value'));
					page.find('span.row span.lastName').text(form.find('input.last').attr('value'));
					page.find('span.row a.emailAddress1').text(form.find('input.emailAddress1').attr('value'));
					page.find('span.row a.emailAddress1').attr('href', 'mailto:' + form.find('input.emailAddress1').attr('value'));
					
					var emailAddress2 = form.find('input.emailAddress2').attr('value');
					var emailAddress3 = form.find('input.emailAddress3').attr('value');
					
					if (emailAddress2 == '') {
						page.find('span.row a.emailAddress2').text('None given').addClass('none').removeAttr('href');
					} else {
						page.find('span.row a.emailAddress2').text(emailAddress2).removeClass('none');
						page.find('span.row a.emailAddress2').attr('href', 'mailto:' + form.find('input.emailAddress2').attr('value'));
					}
					
					if (emailAddress3 == '') {
						page.find('span.row a.emailAddress3').text('None given').addClass('none').removeAttr('href');
					} else {
						page.find('span.row a.emailAddress3').text(emailAddress3).removeClass('none');
						page.find('span.row a.emailAddress3').attr('href', 'mailto:' + form.find('input.emailAddress3').attr('value'));
					}
					
				//Close the dialog
					$(this).dialog('close').remove();
					
				//Send the results to the server
					$.ajax({
						'data' : {
							'action' : 'profile',
							'first' : first,
							'last' : last,
							'emailAddress1' : emailAddress1,
							'emailAddress2' : emailAddress2,
							'emailAddress3' : emailAddress3,
							'password' : password
						}, 'type' : 'POST',
						'url' : 'index.php',
						'success' : function(data) {
							$(this).dialog('close').remove();
							
						//Display a success message
							if (data == 'success') {
								var message = $('<div class="center"><div class="success">Your profile was updated</div></div>').appendTo('body');
							} else {
								var message = $('<div class="center"><div class="error">' + data + '</div></div>').appendTo('body');
							}
							
							setTimeout(function() {
								message.fadeOut();
							}, 10000);
						}
					})
					
				}, 'Cancel' : function() {
					$(this).dialog('close').remove();
				}
			}
		});
	});
	
/**
 * Misc
 * ------------------------------------
*/
	
//Delete a book from the exchange
	$('.deleteBook').click(function() {
		var id = $(this).attr('data-id');
		var location = document.location.href;
		var requestURL = location.substring(0, location.indexOf('book-exchange')) + 'book-exchange/account/?action=delete&id=' + id;
		
		var confirmDialog = $('<section class="confirm" title="Confirm Delete"></section>')
		.html('<p><span class="ui-icon ui-icon-alert"></span>Are you sure you wish to delete this book?<br><br>This action is permanent and cannot be undone. Contine?</p>')
		.dialog({
			'height' : 300,
			'modal' : true,
			'resizable' : false,
			'width' : 500,
			'buttons' : {
				'Yes' : function() {
					document.location.href = requestURL;
				}, 'No' : function() {
					$(this).dialog('close').remove();
				}
			}
		});
	});
	
//Animate the magnifying glass on the search page
	var magnifier = $('img.animatedSearch');
	
	if (magnifier.length) {
		var containerWidth = $(document).width();
		var magnifierWidth = 219;
		
	//We need everything in terms of percents, so calculute the total percentage that the glass takes up
		var magnifierPercent = Math.round((magnifierWidth / containerWidth) * 100);
		
		magnifier.animate({
			'left' : (100 - magnifierPercent - 12) + '%'
		}, 150000);
	}
	
//Activate the jCarousel plugin
	if ($('ul.scrollerContainer').length) {
		$('ul.scrollerContainer').jcarousel({
			'scroll' : 1,
			'auto' : 7,
			'wrap' : 'last'
		});
	}
	
//Clear any alert bubbles
	setTimeout(function() {
		$('.success').fadeOut();
	}, 10000);
	
//Expand the advanced search options
	$('span.expand').click(function() {
		$(this).hide().siblings('div.controls').show();
	});
});