var mobileView = window.matchMedia( "(max-width: 768px)" );

(function ($) {
	function moveHostWidget() {
		if (mobileView.matches) {
			$('.host-widet-wraper').prependTo('#primary');
		} else {
			$('.host-widet-wraper').insertAfter('.user-widet-wraper');
		}
	}

	// Visual changes

	$(document).ready(function () {
		moveHostWidget();
	});

	$(window).resize(function() {
		moveHostWidget();
	});

	// End Visual changes
	$('#host-edit-group').click( function() {
		$('#postbox').show();
		return false;
	});

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
		$('.rah-form form .alerts').remove();
		var checked = $(this).prop('checked');
		if ( checked ) {
			$('#group_input').hide();
			$('.rah-loading').css('display', 'inline-block');

			var data = {
				'action': 'rah_secret_group_listing'
			};

			$.post('/wp-admin/admin-ajax.php', data, function(response) {
				$('#response').html(response);
				$('.rah-loading').css('display', 'none');
			});
		} else {
			$('#group_input').show();
			$('#group_name').hide();
			$('#response').html('');
		}
	});

	$('body').on('change', '#group_not_listed', function() {
		$('.rah-form form .alerts').remove();
		var checked = $(this).prop('checked');
		if ( checked ) {
			$('#existing_groups').attr('disabled', 'disabled');
			$('#new_secret_group').show();
		} else {
			$('#existing_groups').removeAttr('disabled');
			$('#new_secret_group').hide();
		}
	});

	$('#zip_code').on('keyup', function() {
		var length = $(this).val().length;
		var is_long_enough = ( length == 5 );
		var verify_button = $('#verify_zip_code');

		if ( is_long_enough ) {
			verify_button.removeAttr('disabled');
		} else {
			verify_button.attr('disabled', 'disabled');
		}
	});

	$('#verify_zip_code').on('click', function(e) {
		e.preventDefault();
		var data = {
			'action': 'rah_verify_zip',
			'zip': $('#zip_code').val(),
		};
		if (data.zip.length < 5 ) {
			return false;
		}
		$(this).hide().next('.rah-loading').css('display', 'inline-block');
		$('#city_state').text('');
		$.post('/wp-admin/admin-ajax.php', data, function(response) {
			if ( response != '0' ) {
				$('#city_state').text(response);
			} else {
				$('#city_state').text('No City or State information found for postal code');
			}
			$('#verify_zip_code').show().next('.rah-loading').hide();
		});
	});

	$('#issues_na').change( function() {
		var checked = $(this).prop('checked');
		var stars   = $(this).parent().prev('div.rating-input');
		if ( checked ) {
			stars.hide();
		} else {
			stars.show();
		}
	});

	$('.rah-form form').submit( function() {
		$('.rah-form form .alerts').remove();
		var formId = $(this).attr( 'id' );

		if ( formId == 'new_host' ) {
			var errors = 0;
			var isSecret = $('#is_secret').prop('checked');
			var group_id = $('input[name="group_id"]:checked').val();
			var month    = $('select[name="host_since_month"]').val();
			var year     = $('select[name="host_since_year"]').val();

			if ( isSecret === false && typeof group_id === 'undefined' ) {
				$('#group_input').before('<p class="alerts error">Group URL is Required if your group is not secret.</p>');
				errors = errors + 1;
			} else if( isSecret === true ) {
				if ( $('#group_not_listed').prop('checked') ) {
					if ( $('#new_secret_group_title').val().trim() === '' ) {
						$('#new_secret_group').before('<p class="alerts error">A Group Name Is Required</p>');
						errors = errors + 1;
					}
				} else {
					if ( $('#existing_groups').val() === '-1' ) {
						$('#existing_secret').before('<p class="alerts error">Please select a group name from the list</p>');
						errors = errors + 1;
					}
				}
			}

			if ( month == -1 || year == -1 ) {
				$('#host_since_month').before('<p class="alerts error">Please add the month and year you became a host.</p>');
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

	var Search_Hosts = {
		init : function() {
			this.process_distance_search();
		},

		process_distance_search : function() {
			$('#submit-host-search').click( function(e) {
				$('#zip_code').css('border', '1px solid rgba(0, 0, 0, 0.05)').css('background-color', '#f9f9f9');

				var zip      = $('#zip_code').val();
				var distance = $('#distance').val();
				var nonce    = $('input[name="_wpnonce"]').val();
				var action   = 'rah_search_hosts_distance';

				if ( zip == '' || zip.length < 5 ) {
					$('#zip_code').css('border', '1px solid #993333').css('background-color', '#FFCCCC');
					return false;
				}

				$('.rah-loading').css('display', 'inline-block');
				$(this).attr('disabled','disabled');

				var data = {
					'action': action,
					'zip': zip,
					'distance': distance,
					'nonce': nonce,
				}

				$.post('/wp-admin/admin-ajax.php', data, function(response) {
					ga('send', 'event', { eventCategory: 'search', eventAction: 'host', eventLabel: 'distance', eventValue: distance });
					if ( response.error ) {
						alert( response.error);
						return false;
					} else {
						$('#submit-host-search').removeAttr('disabled');
						$('.rah-loading').css('display', 'none');
						$('#chosen-distance').text(distance);
						$('#chosen-zip').text(zip);
						$('.results-set').html(response);
						$('.search-results').show();
					}
				});

			});
		},
	};
	Search_Hosts.init();


})(jQuery);
