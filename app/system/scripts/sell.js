(function($) {
	$(function() {
	//Initialize the Validation Engine
		$('form').validationEngine({
			'autoHidePrompt' : true,
			'autoHideDelay' : 5000,
			'validationEventTrigger' : 'submit',
			'custom_error_messages' : {
				'.cover-input' : {
					'required' : {
						'message' : 'Please select a book cover'
					}
				}
			}
		});
		
	//Initialize the Sell Books Assistant plugin
		$('input#ISBN10').FFI_BE_Sell_Books_Assistant();
		
	//Initialize TinyMCE
		tinymce.init({
			menubar  : false,
			plugins  : [ 'autolink contextmenu image link lists table textcolor' ],
			selector : 'textarea',
			toolbar  : 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist | undo redo | forecolor backcolor'
		});
	});
})(jQuery);