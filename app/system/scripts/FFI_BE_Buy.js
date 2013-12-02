/**
 * Book Exchange in-place book purchasing plugin
 *
 * This plugin is designed to allow a user to click a button
 * to purchase a book and have this plugin initiate an in-place
 * modal dialog to complete the purchase. If the user is not 
 * logged in, the modal will include a login form to provide
 * a seamless, one-step procedure for buying a book.
 *
 * This plugin requires the TinyMCE 4 Editor to be loaded on
 * the page BEFORE this plugin is loaded. This script does
 * not have the ability to load and initialize TinyMCE which
 * was loaded from a CDN.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI_BE
 * @since     3.0.0
*/

(function($) {
	$.fn.FFI_BE_Buy = function(options) {
	//Merge the passed options with defaults
		$.extend($.fn.FFI_BE_Buy.defaults, options);

		return this.on('click', $.fn.FFI_BE_Buy.defaults.targetObject, function() {
			if (!$(this).hasClass('disabled')) {
			//The button which triggered the event
				$.fn.FFI_BE_Buy.button = $(this);
				
			//Several properties of the button
				$.fn.FFI_BE_Buy.ID = $.fn.FFI_BE_Buy.button.attr('data-id');
				$.fn.FFI_BE_Buy.title = $.fn.FFI_BE_Buy.htmlEntitiesDecode($.fn.FFI_BE_Buy.button.attr('data-title'));
				$.fn.FFI_BE_Buy.author = $.fn.FFI_BE_Buy.htmlEntitiesDecode($.fn.FFI_BE_Buy.button.attr('data-author'));
				$.fn.FFI_BE_Buy.image = $.fn.FFI_BE_Buy.htmlEntitiesDecode($.fn.FFI_BE_Buy.button.attr('data-image'));
				$.fn.FFI_BE_Buy.price = $.fn.FFI_BE_Buy.button.attr('data-price');
				
			//The modal dialog and some of its components
				$.fn.FFI_BE_Buy.comments = null;
				$.fn.FFI_BE_Buy.modal = null;
				$.fn.FFI_BE_Buy.password = null;
				$.fn.FFI_BE_Buy.submit = null;
				$.fn.FFI_BE_Buy.username = null;
				$.fn.FFI_BE_Buy.validationPrompt = null;
				
			//Bootstrap this plugin by calling its instance methods
				$.fn.FFI_BE_Buy.buildDialog();
				$.fn.FFI_BE_Buy.submitHandler();
			}
		});
	};
	
/**
 * Build the modal dialog and initialize the TinyMCE editor.
 * 
 * @access public
 * @return void
 * @since  3.0.0
*/
	
	$.fn.FFI_BE_Buy.buildDialog = function() {
	//Generate the dialog HTML
		var HTML = '<div class="modal hide fade purchase-dialog" role="dialog">';
		HTML += '<div class="modal-header">';
		HTML += '<button type="button" class="close" data-dismiss="modal">×</button>';
		HTML += '<h3>Purchase Request</h3>';
		HTML += '</div>';
		HTML += '<div class="modal-body book-purchase-details">';
		HTML += '<img src="' + $.fn.FFI_BE_Buy.image + '">';
		HTML += '<h4>' + $.fn.FFI_BE_Buy.title + '</h4>';
		HTML += '<h5>by ' + $.fn.FFI_BE_Buy.author + '</h5>';
		HTML += '<p class="price">$' + $.fn.FFI_BE_Buy.price + '.00</p>';
		HTML += '</div>';
		
		if ($.fn.FFI_BE_Buy.defaults.showComments) {
			HTML += '<div class="modal-body comments">';
			HTML += '<h4>Share comments with the merchant (optional):</h4>';
			HTML += '<textarea name="content" />';
			HTML += '</div>';
		}
		
		if ($.fn.FFI_BE_Buy.defaults.showLogin) {
			if ($.fn.FFI_BE_Buy.placeholderSupported()) {
				HTML += '<div class="modal-footer login">';
			} else {
				HTML += '<div class="modal-footer login fallback">';
				HTML += '<h4 class="placeholder">Username &amp; password:</h4>';
			}
		
			HTML += '<input class="username" placeholder="Username" type="text">';
			HTML += '<input class="password" placeholder="Password" type="password">';
			HTML += '</div>';
		}
		
		HTML += '<div class="modal-footer">';
		HTML += '<span class="validate"></span>';
		HTML += '<button class="btn btn-primary confirm">' + ($.fn.FFI_BE_Buy.defaults.showLogin ? 'Login &amp;' : 'Confirm') + ' Purchase</button>';
		HTML += '<button class="btn close-dialog" data-dismiss="modal">Close</button>';
		HTML += '</div>';
		HTML += '</div>';
		
	//Create the Twitter Bootstrap dialog and initialize TinyMCE
		$.fn.FFI_BE_Buy.modal = $(HTML);
		
		$.fn.FFI_BE_Buy.modal.on('shown', function() {
			if ($.fn.FFI_BE_Buy.defaults.showComments) {
				$.fn.FFI_BE_Buy.comments = $.fn.FFI_BE_Buy.modal.find('textarea');

				if ($.fn.FFI_BE_Buy.comments.parent().is(':visible')) {
					tinymce.init({
						selector: 'textarea',
						plugins: [
						     'autolink contextmenu image link table'
					    ],
						menubar: false,
						statusbar: false,
						toolbar: false
					});
			
					tinymce.activeEditor.focus();
				}
			}
		}).modal();
		
	//Share a few components of the dialog with the plugin
		if ($.fn.FFI_BE_Buy.defaults.showLogin) {
			$.fn.FFI_BE_Buy.password = $.fn.FFI_BE_Buy.modal.find('input.password');
			$.fn.FFI_BE_Buy.username = $.fn.FFI_BE_Buy.modal.find('input.username');
		}
		
		$.fn.FFI_BE_Buy.submit = $.fn.FFI_BE_Buy.modal.find('button.confirm');
		$.fn.FFI_BE_Buy.validationPrompt = $.fn.FFI_BE_Buy.modal.find('span.validate');
	};

/**
 * Determine whether or not the user's browser supports 
 * the "placeholder" attribute on input elements.
 *
 * @access public
 * @return bool   Whether the user's browser supports placeholders
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.placeholderSupported = function () {
		var test = document.createElement('input');
		return ('placeholder' in test);
	}
	
/**
 * Validate the user's input (if any) and submit the request to 
 * the server. The user will be alerted of any errors which the
 * server encounters during validation and purchase processing.
 *
 * @access public
 * @return void
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.submitHandler = function() {
		$.fn.FFI_BE_Buy.submit.click(function() {
		//Validate the login (if available)
			if ($.fn.FFI_BE_Buy.defaults.showLogin && !$.fn.FFI_BE_Buy.validate()) {
				$.fn.FFI_BE_Buy.msg('Please enter your login credentials');
				return;
			}
			
		//Clear the dialog's message alert
			$.fn.FFI_BE_Buy.clearMsg();
			
		//Disable the submit button
			$.fn.FFI_BE_Buy.submit.attr('disabled', 'disabled').addClass('disabled').html('Please wait...');
			
		//Save the TinyMCE content to the textarea
			if (tinymce.activeEditor != null) {
				tinymce.activeEditor.save();
			}
			
		//Generate the POST data object for the purchase request
			var POST = {
				'id' : $.fn.FFI_BE_Buy.ID
			};
			
			if ($.fn.FFI_BE_Buy.defaults.showComments) {
				POST.comments = $.fn.FFI_BE_Buy.comments.val();
			}
			
			if ($.fn.FFI_BE_Buy.defaults.showLogin) {
				POST.username = $.fn.FFI_BE_Buy.username.val();
				POST.password = $.fn.FFI_BE_Buy.password.val();
			}
			
		//Send the process request to the server
			$.ajax({
				'data' : POST, 
				'type' : 'POST',
				'url' : $.fn.FFI_BE_Buy.defaults.processURL,
				'success' : function(data) {
				//If the transaction was successful, close the dialog and disable the purchase button
					if (data == 'success') {
					//Hide the modal dialog
						$.fn.FFI_BE_Buy.modal.modal('hide');
						
					//Update the UI of the book quick view object to indicate that it has been purchased
						$.fn.FFI_BE_Buy.button.attr('disabled', 'disabled').addClass('disabled').html('Purchased');
						
						$.fn.FFI_BE_Buy.button.parent().parent().addClass('purchased');
						$.fn.FFI_BE_Buy.button.siblings('p.price').html('<em>Purchased</em>');
						$('span.purchase').attr('disabled', 'disabled').addClass('disabled').html('Purchased'); //Book details page has multple buttons
						
					//Update the user's login status
						if ($.fn.FFI_BE_Buy.defaults.showLogin) {
							$.fn.FFI_BE_Buy.defaults.showLogin = false;
						}
						
					//Create a success message
						var message = $('<span class="success"/>');
						message.appendTo('body').text('Your purchase request is on its way! Keep an eye on your inbox. ;-)');
						
						setTimeout(function() {
							message.fadeOut(500, function() {
								message.remove();
							});
						}, 5000);
					} else {
					//Show the message from the server
						$.fn.FFI_BE_Buy.msg(data);
						
					//Restore the submit button
						$.fn.FFI_BE_Buy.submit.removeAttr('disabled').removeClass('disabled').html(($.fn.FFI_BE_Buy.defaults.showLogin ? 'Login &amp;' : 'Confirm') + ' Purchase');
					}
				}
			});
		});
	};
	
/**
 * Validate the user's username and password. This method will not
 * validate whether or not they are correct, but if they have been
 * filled in.
 *
 * @access public
 * @return bool   Whether or not both the username and password have been provided
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.validate = function() {
		return $.fn.FFI_BE_Buy.username.val() != '' && $.fn.FFI_BE_Buy.password.val() != '';
	};
	
/**
 * Update the value of the dialog's message alert
 *
 * @access public
 * @param  string text The text to fill in as the dialog's validation message
 * @return void
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.msg = function(text) {
	//Populate the message alert with text
		$.fn.FFI_BE_Buy.validationPrompt.text(text);
		
	//Only after it contains text can jQuery evaluate whether or not it is visible (responsive CSS styles may hide it)
		if ($.fn.FFI_BE_Buy.validationPrompt.is(':hidden')) {
			alert(text);
		}
	};
	
/**
 * Clear the dialog's message alert.
 *
 * @access public
 * @return void
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.clearMsg = function() {
		$.fn.FFI_BE_Buy.validationPrompt.text('');
	};
	
/**
 * Decode all applicable characters from HTML entities.
 *
 * @access public
 * @param  string input The string to be decoded from HTML entities
 * @return string       The input string decoded from HTML entities
 * @since  3.0.0
*/

	$.fn.FFI_BE_Buy.htmlEntitiesDecode = function(input) {
		return $('<div/>').html(input).text();
	};

/**
 * Plugin default settings.
 *
 * @access public
 * @type   object<bool|string>
*/

	$.fn.FFI_BE_Buy.defaults = {
		'processURL'   : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/system/ajax/purchase.php',
		'showComments' : true,                            //Whether or not to show the comments section
		'showLogin'    : true,                            //Whether or not to show the login section
		'targetObject' : 'button.purchase, span.purchase' //The target object which will trigger a purchase
	};
})(jQuery);