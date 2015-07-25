(function ($) {
	$('.save-post-status').click( function() {
		if ($('input#post_type').val() !== 'reviews' && $('input#post_type').val() !== 'hosts') {
			return;
		}

		var post_status = $('select#post_status').val();

		if (post_status === 'declined') {
			$('#rah-decline-reason').show();
		} else {
			$('#rah-decline-reason').hide();
		}
	});

	$('#publish, #save-post').click( function() {
		if ($('input#post_type').val() !== 'reviews' && $('input#post_type').val() !== 'hosts') {
			return;
		}

		var post_status = $('select#post_status').val();

		if (post_status === 'declined' && $('#rah_declined_reason').val().trim() === '') {
			alert('A reason is required for all denials');
			return false;
		}

		return true;
	});

})(jQuery);
