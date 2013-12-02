/**
 * Reload IndexDen Index plugin
 *
 * This plugin is manage the client-side task of reloading the 
 * IndexDen index. This job is accomplished by querying a specific
 * script on the server (which will do the actual job) serveral
 * times until the batch of data has been completely indexed.
 *
 * @author    Oliver Spryn
 * @copyright Copyright (c) 2013 and Onwards, ForwardFour Innovations
 * @license   MIT
 * @namespace FFI_BE
 * @since     3.0
*/

(function($) {
	$.fn.FFI_BE_Reload_Index = function(options) {
	//Merge the passed options with defaults
		$.extend($.fn.FFI_BE_Reload_Index, options);

	//Initialize a few variables needed throughout this process
		$.fn.FFI_BE_Reload_Index.button = $(this);
		$.fn.FFI_BE_Reload_Index.indexed = 0;
		$.fn.FFI_BE_Reload_Index.indicator = $($.fn.FFI_BE_Reload_Index.defaults.progressIndicator);
		
	//Listen for a button click to initiate the transaction
		return $.fn.FFI_BE_Reload_Index.button.click(function(e) {
			$.fn.FFI_BE_Reload_Index.button.attr('disabled', 'disabled');

		//If clicking this button was going to do anything, stop it
			e.preventDefault();
			e.stopPropagation();

		//Confirm the user's action
			var check = confirm('WARNING: This is a dangerous operation. All of the data in the remote IndexDen database, which is used for searching for books, will be deleted and reloaded with a new set of data from the local database.\n\nDuring this process, the Book Exchange\'s searching mechanism will be unavailable. This process may take several minutes to complete. It is recommended that you wait until the website traffic has slowed down to minimize user impact. Do you wish to continue?');

			if (check) {
				alert('The reloading process will now start.\n\nIMPORTANT: Do NOT leave this page until you are told that this process has completed successfully. Leaving this page will cause the process to stop and will result in a corrupt IndexDen database.');

				$.fn.FFI_BE_Reload_Index.indexed = 0;

				$.fn.FFI_BE_Reload_Index.indicator.text('Indexed so far: 0 books');
				$.fn.FFI_BE_Reload_Index.reload();
			} else {
				$.fn.FFI_BE_Reload_Index.button.removeAttr('disabled');
			}
		});
	};

/**
 * This function will call the server-size processor script over and
 * over again until the server confirms that all items have been indexed.
 * It will also give the user updates as to how many items have been 
 * indexed, as the data becomes available.
 * 
 * @access public
 * @return void
 * @since  3.0
*/

	$.fn.FFI_BE_Reload_Index.reload = function() {
		$.ajax({
			type    : 'GET',
			url     : $.fn.FFI_BE_Reload_Index.defaults.processor,
			success : function(data) {
				var info;

				try {
					info = $.parseJSON(data);
				} catch (e) {
					alert(e);
				}
				
				var completed = info.completed;
				var indexed = parseInt(info.indexed);
				$.fn.FFI_BE_Reload_Index.indexed += indexed;

			//Is there more to index?
				if (!completed) {
					$.fn.FFI_BE_Reload_Index.reload();
					$.fn.FFI_BE_Reload_Index.indicator.text('Indexed so far: ' + $.fn.FFI_BE_Reload_Index.indexed + ($.fn.FFI_BE_Reload_Index.indexed == 1 ? ' book' : ' books'));
				} else {
					$.fn.FFI_BE_Reload_Index.indicator.text('Indexed so far: ' + $.fn.FFI_BE_Reload_Index.indexed + ' of ' + $.fn.FFI_BE_Reload_Index.indexed + ($.fn.FFI_BE_Reload_Index.indexed == 1 ? ' book' : ' books'));
					$.fn.FFI_BE_Reload_Index.validate();
				}
			}
		});
	}

/**
 * This function will ensure that the total number of indexed items
 * which were counted on the client-side match the number of items
 * which were actually indexed. If the two values do not match, the
 * indexing process is restarted.
 * 
 * @access public
 * @return void
 * @since  3.0
*/

	$.fn.FFI_BE_Reload_Index.validate = function() {
	//Wait a few seconds, just to make sure everything is indexed
		setTimeout(function() {
			$.fn.FFI_BE_Reload_Index.indicator.text('Validating data integrity...');

			$.ajax({
				type    : 'GET',
				url     : $.fn.FFI_BE_Reload_Index.defaults.validator,
				success : function(data) {
				//Does the size of the index match the number of items we recorded?
					if (parseInt(data) == $.fn.FFI_BE_Reload_Index.indexed) {
						$.fn.FFI_BE_Reload_Index.button.removeAttr('disabled');
						$.fn.FFI_BE_Reload_Index.indicator.text('');

						alert('The IndexDen index has been successfully reloaded. The searching mechanism is now functional again.\n\nYou may now leave this page.');
					} else {
						$.fn.FFI_BE_Reload_Index.indexed = 0;
						$.fn.FFI_BE_Reload_Index.reload();
					}
				}
			});
		}, $.fn.FFI_BE_Reload_Index.defaults.validationWait);
	}
	
/**
 * Plugin default settings
 *
 * @access public
 * @type   object<string>
*/
	
	$.fn.FFI_BE_Reload_Index.defaults = {
		'processor'         : document.location.href.substring(0, document.location.href.indexOf('wp-admin')) + 'wp-content/plugins/book-exchange/admin/processing/reload.php',
		'progressIndicator' : 'p.reload-progress',
		'validator'         : document.location.href.substring(0, document.location.href.indexOf('wp-admin')) + 'wp-content/plugins/book-exchange/admin/processing/size.php',
		'validationWait'    : 3000
	};
})(jQuery);

(function($) {
	$(function() {
		$('button.reload-index').FFI_BE_Reload_Index();
	});
})(jQuery);