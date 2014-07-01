(function ($) {

	$('.rah-form input#group_input').on( 'input', function() {
		var data = {
			'action': 'rah_group_listing',
			'group': $(this).val()      // We pass php values differently!
		};
		if (data.group.length === 0 ) {
			return false;
		}
		$('.rah-loading').css('display', 'inline-block');
		$.post('/wp-admin/admin-ajax.php', data, function(response) {
			$('#response').html(response);
			$('.rah-loading').css('display', 'none');
		});
	});

	$('input#is_secret').click( function() {
		var checked = $(this).prop('checked');
		if ( checked ) {
			$('#group_input').hide();
			$('#response').hide();
		} else {
			$('#group_input').show();
			$('#response').show();
		}
	});

	$('.rah-form form').submit( function() {
		$('.rah-form form .alerts').remove();
		var formId = $(this).attr( 'id' );

		if ( formId == 'new_host' ) {
			var errors = 0;
			var isSecret = $('#is_secret').prop('checked');
			var group_id = $('input[name=group_id]:checked').val();
			if ( isSecret === false && typeof group_id === 'undefined' ) {
				$('#group_input').before('<p class="alerts error">Group URL is Required if your group is not secret.</p>');
				errors = errors + 1;
			}

			if ( errors > 0 ) {
				return false;
			}

		} else if ( formId == 'review_host' || formId == 'edit_review' ) {
			var title = $('#title').val().trim();
			var comments = $('#comments').val().trim();
			var totalPoints = 0;
			$('.rah-form form .rating').each(function(i,n) {
				totalPoints += parseInt($(n).val(),10);
			});

			var errors = 0;
			if ( typeof title === 'undefined' || title.length === 0 ) {
				$('#title').before('<p class="alerts error">A Review Summary is Required</p>');
				errors = errors + 1;
			}

			if ( totalPoints === 0 ) {
				var r = confirm("Ooops! You did not enter any star ratings. Submit review?");
				if (r !== true) {
					errors = errors + 1;
					$('#xpost').after('<p class="alerts notice">Please add some ratings</p>');
				}
			}

			if ( errors > 0 ) {
				window.scrollTo(0, 0);
				return false;
			}
		}
	});


})(jQuery);