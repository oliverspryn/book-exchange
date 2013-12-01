(function($) {
	$(function() {
	//Listen for a book restore request
		$('button.restore').click(function() {
			var button = $(this);
			
		//Build the contents of the confirm dialog
			var HTML = '<div class="modal hide fade confirm-dialog" role="dialog">';
			HTML += '<div class="modal-header">';
			HTML += '<button type="button" class="close" data-dismiss="modal">×</button>';
			HTML += '<h3>Restore Book to the Exchange</h3>';
			HTML += '</div>';
			HTML += '<div class="modal-body confirm-dialog-details">';
			HTML += '<p>This action will renew your book\'s time slot on the book exchange, and will display it for another ' + button.attr('data-expire') + ' ' + (parseInt(button.attr('data-expire')) == 1 ? 'month' : 'months') + ' from today.<br><br>Do you wish to continue?</p>';
			HTML += '</div>';			
			HTML += '<div class="modal-footer">';
			HTML += '<span class="validate"></span>';
			HTML += '<button class="btn btn-primary confirm">Yes</button>';
			HTML += '<button class="btn close-dialog" data-dismiss="modal">No</button>';
			HTML += '</div>';
			HTML += '</div>';
			
		//Create the Twitter Bootstrap dialog
			var modal = $(HTML);
			var submit = modal.find('button.confirm');
			var validation = modal.find('span.validate');
			
			modal.modal();
			
		//Send the renewal request
			submit.click(function() {
				submit.attr('disabled', 'disabled').html('Please wait...');
				validation.text('');
				
			//Send the process request to the server
				$.ajax({
					'data' : {
						'ID' : button.attr('data-id')
					}, 
					'type' : 'POST',
					'url' : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/system/ajax/restore.php',
					'success' : function(data) {
					//If the transaction was successful, close the dialog and disable the purchase button
						if (data == 'success') {
						//Hide the modal dialog
							modal.modal('hide');
							
						//Increment the total book counter near the top of the page						
							if (!button.parent().hasClass('expired') && !button.parent().hasClass('sold')) {
								var total = $('span.saleTotal');
								var value = parseInt(total.text());
								total.text(++value);
								
								value == 1 ? total.next('span').text('Book for Sale') : total.next('span').text('Books for Sale');
							}
							
						//Reset the visual display of the book in the list which was just restored
							button.parent().removeAttr('class');
							button.siblings('h3').children('span.notice').remove();
							button.remove();
							
						//Create a success message
							var message = $('<span class="success"/>');
							message.appendTo('body').text('Your book\'s time slot has been exteneded.');
							
							setTimeout(function() {
								message.fadeOut(500, function() {
									message.remove();
								});
							}, 5000);
						} else {
						//Populate the message alert with text
							validation.text(data);
							
						//Only after it contains text can jQuery evaluate whether or not it is visible (responsive CSS styles may hide it)
							if (validation.is(':hidden')) {
								alert(data);
							}
							
						//Restore the submit button
							submit.removeAttr('disabled').html('Yes');
						}
					}
				});
			});
		});
		
	//Listen for a book delete request
		$('button.delete').click(function() {
			var button = $(this);
			
		//Build the contents of the confirm dialog
			var HTML = '<div class="modal hide fade confirm-dialog" role="dialog">';
			HTML += '<div class="modal-header">';
			HTML += '<button type="button" class="close" data-dismiss="modal">×</button>';
			HTML += '<h3>Delete a Book</h3>';
			HTML += '</div>';
			HTML += '<div class="modal-body confirm-dialog-details">';
			HTML += '<p>This action will delete this book from the exchange. Your book will be permanently deleted and cannot be restored.<br><br>Do you wish to continue?</p>';
			HTML += '</div>';			
			HTML += '<div class="modal-footer">';
			HTML += '<span class="validate"></span>';
			HTML += '<button class="btn btn-danger confirm">Yes</button>';
			HTML += '<button class="btn close-dialog" data-dismiss="modal">No</button>';
			HTML += '</div>';
			HTML += '</div>';
			
		//Create the Twitter Bootstrap dialog
			var modal = $(HTML);
			var submit = modal.find('button.confirm');
			var validation = modal.find('span.validate');
			
			modal.modal();
			
		//Send the renewal request
			submit.click(function() {
				submit.attr('disabled', 'disabled').html('Please wait...');
				validation.text('');
				
			//Send the process request to the server
				$.ajax({
					'data' : {
						'ID' : button.attr('data-id')
					}, 
					'type' : 'POST',
					'url' : document.location.href.substring(0, document.location.href.indexOf('book-exchange')) + 'wp-content/plugins/book-exchange/app/system/ajax/delete.php',
					'success' : function(data) {
					//If the transaction was successful, close the dialog and disable the purchase button
						if (data == 'success') {
						//Hide the modal dialog
							modal.modal('hide');
							
						//Decrement the total book counter near the top of the page						
							if (!button.parent().hasClass('expired') && !button.parent().hasClass('sold')) {
								var total = $('span.saleTotal');
								var value = parseInt(total.text());
								total.text(--value);
								
								value == 1 ? total.next('span').text('Book for Sale') : total.next('span').text('Books for Sale');
							}
							
						//Show the "Sell some books" prompt, if the user deleted the last of the books
							if (!button.parent().siblings('li').length) {
								!button.parent().parent().siblings('div.none').show();
							}
							
						//Reset the book from the list
							button.parent().remove();
							
						//Create a success message
							var message = $('<span class="success"/>');
							message.appendTo('body').text('Your book has been deleted.');
							
							setTimeout(function() {
								message.fadeOut(500, function() {
									message.remove();
								});
							}, 5000);
						} else {
						//Populate the message alert with text
							validation.text(data);
							
						//Only after it contains text can jQuery evaluate whether or not it is visible (responsive CSS styles may hide it)
							if (validation.is(':hidden')) {
								alert(data);
							}
							
						//Restore the submit button
							submit.removeAttr('disabled').html('Yes');
						}
					}
				});
			});
		});
	});
})(jQuery)