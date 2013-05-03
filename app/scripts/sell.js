(function($) {
	$(function() {
	//Initialize the Validation Engine
		$('form').validationEngine();
		
	//Initialize the Sell Books Assistant plugin
		$('input#ISBN10').FFI_BE_Sell_Books_Assistant($('input#ISBN13'), $('input#title'), $('input#author'), $('input#edition'), $('div#suggestions'));
		
	//Initialize TinyMCE
		tinyMCE.init({
			mode : 'textareas',
			skin : 'o2k7',
			skin_variant : 'silver',
			theme : 'advanced',
			
			plugins :'inlinepopups,spellchecker,tabfocus,autosave,autolink',
			theme_advanced_buttons1 : 'bold,italic,underline,separator,strikethrough,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,separator,undo,redo',
			theme_advanced_buttons2 : '',
			theme_advanced_buttons3 : '',
			theme_advanced_resizing : true,
			theme_advanced_statusbar_location : 'bottom',
			theme_advanced_toolbar_location : 'top',
			theme_advanced_toolbar_align : 'left'
		});
		
	//Enable the cancel button to leave the form
		$('button.cancel').click(function() {
			var URL = document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'book-exchange';
			document.location.href = URL;
		});
	});
})(jQuery);