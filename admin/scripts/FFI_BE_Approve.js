/**
 * Book Exchange approval plugin
 *
 * This plugin is designed to automatically submit information
 * to the server when a user clicks a button to either accept or
 * reject a book cover in the Book Cover Approval section in the 
 * Wordpress Administration.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI_BE
 * @since     3.0
*/

(function($) {
	$.FFI_BE_Approve = function(options) {
	//Merge the passed options with defaults
		//$.extend($.FFI_BE_Approve.defaults, options);
		
	//Select each of the buttons and text input controls
		$.FFI_BE_Approve.approve = $('button.approve');
		$.FFI_BE_Approve.inappropriate = $('button.inappropriate');
		$.FFI_BE_Approve.unavailable = $('button.unavailable');
		$.FFI_BE_Approve.altURL = $('input.alternative-url-input');
		$.FFI_BE_Approve.altBtn = $('button.alternative-url');
		
	//Handle each of the button clicks
		$.FFI_BE_Approve.approve.click(function(e) {
			$.FFI_BE_Approve.approveHandler(e);
		});
		
		$.FFI_BE_Approve.inappropriate.click(function(e) {
			$.FFI_BE_Approve.inappropriateHandler(e);
		});
		
		$.FFI_BE_Approve.unavailable.click(function(e) {
			$.FFI_BE_Approve.unavailableHandler(e);
		});
		
		$.FFI_BE_Approve.altURL.change(function(e) {
			$.FFI_BE_Approve.altURLHandler(e);
		});
		
		$.FFI_BE_Approve.altBtn.click(function(e) {
			$.FFI_BE_Approve.altBtnHandler(e);
		});
		
		$(document).mousemove(function(e) {
			$.FFI_BE_Approve.mouseX = e.pageX;
			$.FFI_BE_Approve.mouseY = e.pageY;
		});
	};
	
	$.FFI_BE_Approve.approveHandler = function(e) {
		var target = $(e.target);
	
	//Disable all of the buttons for this book cover
		$.FFI_BE_Approve.disableSiblings(target);
		
	//Send the request to the sever
		$.ajax({
			'data'    : {
				'id'    : $.FFI_BE_Approve.approve.attr('data-id'),
				'state' : 'approved'
			},
			'url'     : $.FFI_BE_Approve.defaults.processing,
			'success' : function() {
				
			}
		});
		$.FFI_BE_Approve.whoot('Cover has been approved!');
	}
	
	$.FFI_BE_Approve.inappropriateHandler = function(e) {
		var target = $(e.target);
	
		$.FFI_BE_Approve.disableSiblings(target);
		$.FFI_BE_Approve.changePreview(target, $.FFI_BE_Approve.defaults.inappropriateURL);
	}
	
	$.FFI_BE_Approve.unavailableHandler = function(e) {
		var target = $(e.target);
	
		$.FFI_BE_Approve.disableSiblings(target);
		$.FFI_BE_Approve.changePreview(target, $.FFI_BE_Approve.defaults.unavailableURL);
	}
	
	$.FFI_BE_Approve.altURLHandler = function(e) {
		var target = $(e.target);
		var apply = target.siblings('button');
		
		if (target.val() != '' && $.FFI_BE_Approve.URLValid(target.val())) {
			$.FFI_BE_Approve.changePreview(target, target.val());
			apply.removeAttr('disabled');
		} else {
			$.FFI_BE_Approve.changePreview(target, '');
			apply.attr('disabled', 'disabled');
		}
	}
	
	$.FFI_BE_Approve.altBtnHandler = function(e) {
		var target = $(e.target);
	
		$.FFI_BE_Approve.disableSiblings(target);
	}
	
	$.FFI_BE_Approve.disableSiblings = function(target) {
		target.attr('disabled', 'disabled').text('Please wait...');
		target.parent().parent().find('p input, p button').attr('disabled', 'disabled');
	}
	
	$.FFI_BE_Approve.enableSiblings = function(target) {
		target.parent().parent().find('p input, p button').removeAttr('disabled');
	}
	
	$.FFI_BE_Approve.changePreview = function(target, newURL) {
		var image = target.parent().parent().find('img');
		
		if (newURL != '') {
			image.attr('data-old-url', image.attr('src'));
			image.attr('src', newURL);
		} else {
			image.attr('src', image.attr('data-old-url'));
		}
	}
	
	//http://stackoverflow.com/a/2723190/663604
	$.FFI_BE_Approve.URLValid = function(URL) {
		 return /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(URL);
	}
	
	$.FFI_BE_Approve.whoot = function(message) {
		var funTip = $('<span />').text(message).css({
			'background'    : '#00FF66',
			'border'        : '1px solid #003300',
			'border-radius' : '5px',
			'color'         : '#000000',
			'font-size'     : '13px',
			'font-weight'   : 'bolder',
			'left'          : $.FFI_BE_Approve.mouseX + 20 + 'px',
			'padding'       : '2px 5px 2px 5px',
			'position'      : 'absolute',
			'text-align'    : 'center',
			'top'           : $.FFI_BE_Approve.mouseY - 20 + 'px'
		}).appendTo('body');
		
		funTip.animate({
			'opacity' : 0,
			'top'     : '-=100'
		}, 5000, 'linear', function() {
			funTip.remove();
		});
	}
	
/**
 * Plugin default settings
 *
 * @access public
 * @type   object<int|string>
*/
	
	$.FFI_BE_Approve.defaults = {
		'processor' : document.location.href.substring(0, document.location.href.indexOf('wp-admin')) + 'wp-content/plugins/book-exchange/admin/processing/approve.php',
		inappropriateURL : document.location.href.substring(0, document.location.href.indexOf('wp-admin')) + 'wp-content/plugins/book-exchange/app/images/book-covers/inappropriate-preview.jpg',
		unavailableURL : document.location.href.substring(0, document.location.href.indexOf('wp-admin')) + 'wp-content/plugins/book-exchange/app/images/book-covers/unavailable-preview.jpg'
	};
})(jQuery);

(function($) {
	$(function() {
		$.FFI_BE_Approve();
	});
})(jQuery);