<?php

function host_registration_form( $atts ) {
	if ( is_user_logged_in() && ! rah_is_registered_host() ) {
		global $current_user;
		get_currentuserinfo();
		?>
		<div class="rah-before-form"></div>
		<div id="postbox" class="rah-form">

			<form id="new_host" name="new_post" method="post" action="<?php echo get_page_link(); ?>">
				<p>
					<h4>Hi <?php echo $current_user->user_firstname; ?>,</h4>
					Please fill out the following information in order to register yourself as a Co-Op Host. Registering as a host allows
					you to receive notifications of new reviews.
				</p>

				<label for="title"><h4>Host Name</h4></label>
				<p><input size="50" id="title" name="host_title" readonly type="text" value="<?php echo $current_user->user_firstname . ' ' . $current_user->user_lastname; ?>" /></p>
				<label for="group_input"><h4>Group Name</h4></label>
				<p>
					<label for="is_private">My Group is marked "Secret"</label><input id="is_secret" name="is_secret" type="checkbox" value="is_secret" value="1" />
					<input id="group_input" size="50" name="host_group" type="text" value="" placeholder="Your Group Name" />
					<input id="group_name" size="50" name="group_name" type="text" value="" placeholder="Your Group Name" style="display: none;" />
					<span class="rah-loading"></span>
					<div class="hidden" id="response"></div>
				</p>

				<label for="group-type"><h4>Group Type</h4></label>
				<?php
				$args = array(
					'orderby'            => 'ID',
					'order'              => 'ASC',
					'show_count'         => 0,
					'hide_empty'         => 0,
					'echo'               => 1,
					'selected'           => 0,
					'name'               => 'cat',
					'id'                 => '',
					'class'              => 'postform',
					'depth'              => 0,
					'tab_index'          => 0,
					'taxonomy'           => 'type',
					'hide_if_empty'      => false );
				?>
				<p><?php wp_dropdown_categories( $args ); ?></p>

				<p>
					<label for="zip_code"><h4>Zip Code</h4></label>
					This is an optional field. Providing your zip code will allow users to search for groups in the near future based off of the hosts location.
					<br />
					<input value= "" type="text" maxlength="5" size="5" name="zip_code" id="zip_code" pattern="[\d]{5}" placeholder="12345" />
					<input type="submit" id="verify_zip_code" value="Check" disabled="disabled" /><span class="rah-loading"></span>
					<span id="city_state"></span>
				</p>

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>


				<input type="hidden" name="type" id="type" value="hosts" />
				<input type="hidden" name="rah-action" value="register-host" />
				<?php wp_nonce_field( 'rah-new-host' ); ?>

			</form>
		</div>
		<div class="rah-after-form"></div>
	<?php
	} elseif ( ! is_user_logged_in() ) {
		?>
		<p>
			<strong><em>You must be logged in<sup>*</sup> to register as a Co-Op Host</em></strong>.
		</p>
		<?php
		sc_render_login_form_social_connect( array( 'display_label' => false ) );
		?>
		<p class="fine-print">
			<sup>*Please login with the Facebook profile associated with your host activities.</sup>
		</p>
		<?php
	} elseif ( rah_is_registered_host() ) {
		global $current_user;
		get_currentuserinfo();

		$host_id          = get_host_id_from_user_id( $current_user->ID );
		$host_status      = get_post_status( $host_id );
		$host_postal_code = get_post_meta( $host_id, '_user_postal_code', true );

		switch( $host_status ) {
			case 'publish':
				$status = 'Approved';
				break;
			case 'pending':
				$status = 'Pending Approval';
				break;
			case 'declined':
				$status = 'Declined';
				break;
			default:
				$status = ' - ';
		}
		$parent = get_post_ancestors( $host_id );

		$group_name  = '';
		$group_link  = '';
		$group_fb_id = '';

		if ( $parent ) {
			$group_url   = get_permalink( $parent[0] );
			$group_name  = get_the_title( $parent[0] );
			$group_fb_id = get_post_meta( $parent[0], '_rah_group_fb_id', true );
			if ( ! empty( $group_fb_id ) ) {
				$group_link  = 'https://facebook.com/' . $group_fb_id;
			}
		}
		?>
		<div class="rah-before-form">
			<p id="host-status">
				<strong>Current Status:&nbsp;</strong><?php echo $status; ?>
			</p>
			<p id="group-association">
				<strong>Current Group:&nbsp;</strong>
				<?php if ( $parent ) : ?>
					<?php echo $group_name; ?>
				<?php endif; ?>
				<?php if ( empty( $parent ) ) : ?>
					No Current Group Association
				<?php endif; ?>
				<a href="#" id="host-edit-group">&nbsp;&nbsp;Edit Profile</a>
			</p>
		</div>
		<div id="postbox" class="rah-form" style="display: none;">
			<form id="new_host" name="new_post" method="post" action="<?php echo get_page_link(); ?>">
				<label for="group_input"><h4>Group Name</h4></label>
				<p>
					<label for="is_private">My Group is marked "Secret"</label><input id="is_secret" name="is_secret" type="checkbox" value="is_secret" value="1" <?php checked( true, empty( $group_fb_id ), true ); ?> />
					<input id="group_input" size="50" name="host_group" type="text" value="<?php echo $group_name; ?>" placeholder="Your Group Name" />
					<input id="group_name" size="50" name="group_name" type="text" value="<?php echo $group_name; ?>" placeholder="Your Group Name" style="display: none;" />
					<span class="rah-loading"></span>
					<div class="hidden" id="response">
						<?php if ( ! empty( $group_fb_id ) ) : ?>
							<input type="hidden" name="group_id" value="<?php echo $group_fb_id; ?>" />
						<?php endif; ?>
					</div>
				</p>

				<label for="group-type"><h4>Group Type</h4></label>
				<?php
				$args = array(
					'orderby'            => 'ID',
					'order'              => 'ASC',
					'show_count'         => 0,
					'hide_empty'         => 0,
					'echo'               => 1,
					'selected'           => 0,
					'name'               => 'cat',
					'id'                 => '',
					'class'              => 'postform',
					'depth'              => 0,
					'tab_index'          => 0,
					'taxonomy'           => 'type',
					'hide_if_empty'      => false );
				?>
				<p><?php wp_dropdown_categories( $args ); ?></p>

				<p>
					<label for="zip_code"><h4>Zip Code</h4></label>
					This is an optional field. Providing your zip code will allow users to search for groups in the near future based off of the hosts location.
					<br />
					<input value="<?php echo $host_postal_code; ?>" type="text" maxlength="5" size="5" name="zip_code" id="zip_code" pattern="[\d]{5}" placeholder="12345" />
					<input type="submit" id="verify_zip_code" value="Check" <?php if ( strlen( $host_postal_code ) < 5 ) : ?>disabled="disabled" <?php endif; ?> /><span class="rah-loading"></span>
					<span id="city_state"></span>
				</p>

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>


				<input type="hidden" name="type" id="type" value="hosts" />
				<input type="hidden" name="rah-action" value="register-host" />
				<?php wp_nonce_field( 'rah-new-host' ); ?>
			</form>
		</div>
		<hr />
		<div>
			<h5>Host Settings</h5>
			<form id="host-settings" method="post" action="<?php echo get_page_link(); ?>">
				<p id="host-settings">
				<?php $no_emails = get_post_meta( $host_id, '_no_review_optout', true ); ?>
				<input type="checkbox" id="host_email_optout" name="host_email_optout" value="1" <?php checked( '1', $no_emails, true ); ?> />
				&nbsp;<label for="host_email_optout"><strong>Do not</strong> send me emails when I get new reviews</label>
				</p>
				<input type="hidden" name="rah-action" value="edit-host" />
				<input type="hidden" name="host_id" value="<?php echo $host_id; ?>" />
				<?php wp_nonce_field( 'edit-host', 'rah_edit_host', false, true ); ?>
				<input type="submit" value="Save Settings" />
			</form>
		</div>
		<div class="rah-after-form"></div>
		<?php
	}
}
add_shortcode( 'host_registration_form', 'host_registration_form' );

function host_review_form( $atts ) {
	if ( is_user_logged_in() ) {
		global $current_user, $post;
		get_currentuserinfo();
		$user_host_id = (int)get_host_id_from_user_id( $current_user->ID );
		if ( $user_host_id === $post->ID ) {
			?>You can't rate yourself.<?php
		} else if ( !has_user_reviewed_host( $post->ID ) ) :
		?>
		<div class="rah-before-form"></div>
		<div id="postbox" class="rah-form">

			<form id="review_host" name="new_post" method="post" action="<?php echo get_permalink( $post->ID); ?>submit">
				<h3>Reviewing</h3>
				<p>
					<input size="50" id="name" name="host_name" readonly type="text" value="<?php the_title(); ?>" />
				</p>

				<h3>Review Title</h3>
				<p>
					<input size="50" id="title" name="title" type="text" value="" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" />
				</p>

				<p>
					<strong><label for="reinvoices">This host re-invoices within 180 days if requested</label></strong><br />
					<input type="radio" name="reinvoices" value="yes" />&nbsp;Yes<br />
					<input type="radio" name="reinvoices" value="no" />&nbsp;No<br />
					<input type="radio" name="reinvoices" value="na" />&nbsp;Not Applicable
				</p>

				<h3>Efficiency</h3>
				<p>
					<label for="">Did the host invoice and pay for your buy in a timeframe you felt was appropriate?</label>
					<input type="number" name="star_ratings[invoicing_and_ordering_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>
				<p>
					<label for="post_order">Did the host sort, pack, and invoice for shipping in an appropriate amount of time after the <strong><em>entire</em></strong> order was received?</label>
					<input type="number" name="star_ratings[sorting_and_packing_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>
				<p>
					<label for="shipping">Was the order mailed, after shipping was paid, in a timeframe that was appropriate?</label>
					<input type="number" name="star_ratings[shipping_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>

				<h3>Communication/Resolution</h3>
				<p>
					<label for="communication">How well did the host communicate updates and information about the buys from open until shipment to you?</label>
					<input type="number" name="star_ratings[communication_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>
				<p>
					<label for="post_order">Did you feel the host was friendly, helpful, and professional through all steps of your buy?</label>
					<input type="number" name="star_ratings[professionalism_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>
				<p>
					<label for="issues">If there was a problem, or issue, did the host seem willing to work towards a resolution (including re-invoicing if requested), and follow through with the promised resolution?</label><br />
					<input type="number" name="star_ratings[issue_resolution_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
					<span class="issues-na-wrapper">
						<input type="checkbox" id="issues_na" name="issues_na" value="1" /><label class="issues-label" for="issues_na">I did not have any issues</label>
					</span>
				</p>

				<h3>Recommends Host</h3>
				<p>
					<label for="recommendation">How willing are you to buy from this host again, or recommend this host to other buyers?</label>
					<input type="number" name="star_ratings[recommends_host_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" value="1" />
				</p>

				<h3>Additional Comments</h3>
				<p>Please add any additional comments you would like for other buyers to see. You can add praises to wonderful hosts, examples of problems (high fees, substandard products, communication issues),
				 how a host went above and beyond, or just an overall statement about your host!</p>
				<textarea name="comments" id="comments"></textarea>

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>

				<input type="hidden" name="type" id="type" value="reviews" />
				<input type="hidden" name="action" value="review-host" />
				<?php wp_nonce_field( 'rah-new-review' ); ?>

			</form>
		</div>
		<div class="rah-after-form"></div>
		<?php else: ?>
			You can only review a Host Once. If you need to change your review, please <a href="<?php echo get_permalink( $post->ID ); ?>edit">edit</a> your current one.
		<?php endif; ?>
	<?php
	} elseif ( ! is_user_logged_in() ) {
		?>
		<p>
			<strong><em>You must be logged in<sup>*</sup> to review a Host</em></strong>.
		</p>
		<?php
		sc_render_login_form_social_connect( array( 'display_label' => false ) );
	}
}
add_shortcode( 'host_review_form', 'host_review_form' );

function host_review_edit_form( $atts ) {
	global $current_user, $post;
	get_currentuserinfo();
	$user_host_id = (int)get_host_id_from_user_id( $current_user->ID );
	if ( $user_host_id === $post->ID ) {
		?>You can't rate yourself.<?php
	} else if ( is_user_logged_in() && has_user_reviewed_host( $post->ID ) ) {
		global $current_user, $post;
		get_currentuserinfo();
		$review_id = has_user_reviewed_host( $post->ID );
		$post_data = get_post( $review_id );
		$ratings = get_post_meta( $review_id, '_review_star_ratings', true );
		$xpost = get_post_meta( $review_id, '_review_xpost', true );
		$reinvoices = get_post_meta( $review_id, '_review_reinvoices', true );
		$no_issues = get_post_meta( $review_id, '_review_issues_na', true );
		?>
		<div class="rah-before-form">
			<?php if( $post_data->post_status == 'pending' ) : ?>
				<p class="alerts notice"><strong><em>This review is currently in moderation. Any further edits may delay it's publishing</em></strong></p>
			<?php endif; ?>
			<?php if( $post_data->post_status == 'publish' ) : ?>
				<p class="alerts confirm"><strong><em>This review is live, any edits will cause it to be sent for moderation.</em></strong></p>
			<?php endif; ?>
		</div>
		<div id="postbox" class="rah-form">

			<form id="edit_review" name="new_post" method="post" action="<?php echo get_permalink( $post->ID); ?>submit">
				<h3>Reviewing</h3>
				<p>
					<input size="50" id="name" name="host_name" readonly type="text" value="<?php the_title(); ?>" />
				</p>

				<h3>Review Title</h3>
				<p>
					<input size="50" id="title" name="title" type="text" value="<?php echo $post_data->post_title; ?>" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" <?php checked( 'yes', $xpost, true ); ?> />
				</p>

				<p>
					<strong><label for="reinvoices">This host re-invoices within 180 days if requested</label></strong><br />
					<input type="radio" name="reinvoices" value="yes" <?php checked( 'yes', $reinvoices, true ); ?> />&nbsp;Yes<br />
					<input type="radio" name="reinvoices" value="no" <?php checked( 'no', $reinvoices, true ); ?> />&nbsp;No<br />
					<input type="radio" name="reinvoices" value="na" <?php checked( 'na', $reinvoices, true ); ?> />&nbsp;Not Applicable
				</p>

				<h3>Efficiency</h3>
				<p>
					<input type="number" name="star_ratings[invoicing_and_ordering_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['invoicing_and_ordering_rating'] ) ? $ratings['invoicing_and_ordering_rating'] : 1; ?>" />
					<label for="">Did the host invoice and pay for your buy in a timeframe you felt was appropriate?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[sorting_and_packing_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['sorting_and_packing_rating'] ) ? $ratings['sorting_and_packing_rating'] : 1; ?>" />
					<label for="post_order">Did the host sort, pack, and invoice for shipping in an appropriate amount of time after the <strong><em>entire</em></strong> order was received?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[shipping_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['shipping_rating'] ) ? $ratings['shipping_rating'] : 1; ?>" />
					<label for="shipping">Was the order mailed, after shipping was paid, in a timeframe that was appropriate?</label>
				</p>

				<h3>Communication/Resolution</h3>
				<p>
					<input type="number" name="star_ratings[communication_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['communication_rating'] ) ? $ratings['communication_rating'] : 1; ?>" />
					<label for="communication">How well did the host communicate updates and information about the buys from open until shipment to you?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[professionalism_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['professionalism_rating'] ) ? $ratings['professionalism_rating'] : 1; ?>" />
					<label for="post_order">Did you feel the host was friendly, helpful, and professional through all steps of your buy?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[issue_resolution_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['issue_resolution_rating'] ) ? $ratings['issue_resolution_rating'] : 1; ?>" />
					<input type="checkbox" <?php checked( $no_issues, '1', true ); ?> id="issues_na" name="issues_na" value="1" />&nbsp;<label for="issues_na">Not Applicable</label><br />
					<label for="issues">If there was a problem, or issue, did the host seem willing to work towards a resolution (including re-invoicing if requested), and follow through with the promised resolution?</label>
				</p>

				<h3>Recommends Host</h3>
				<p>
					<input type="number" name="star_ratings[recommends_host_rating]" class="rating" data-empty-value="1" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo ! empty( $ratings['recommends_host_rating'] ) ? $ratings['recommends_host_rating'] : 1; ?>" />
					<label for="recommendation">How willing are you to buy from this host again, or recommend this host to other buyers?</label>
				</p>

				<h3>Additional Comments</h3>
				<p>Please add any additional comments you would like for other buyers to see. You can add praises to wonderful hosts, examples of problems (high fees, substandard products, communication issues),
				 how a host went above and beyond, or just an overall statement about your host!</p>
				<textarea name="comments" id="comments"><?php echo $post_data->post_content; ?></textarea>

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>

				<input type="hidden" name="type" id="type" value="reviews" />
				<input type="hidden" name="action" value="review-host" />
				<input type="hidden" name="existing_post_id" value="<?php echo $review_id; ?>" />
				<?php wp_nonce_field( 'rah-new-review' ); ?>

			</form>
		</div>
		<div class="rah-after-form"></div>
	<?php
	} elseif ( ! is_user_logged_in() ) {
		?>
		<p>
			<strong><em>You must be logged in<sup>*</sup> to review a Host</em></strong>.
		</p>
		<?php
		sc_render_login_form_social_connect( array( 'display_label' => false ) );
	}
}
add_shortcode( 'host_review_edit_form', 'host_review_edit_form' );

function host_review_submit( $atts ) {
	global $post;
	//$ip_ban = rah_check_rate_limit();
	$ip_ban = false;
	if ( has_user_reviewed_host( $post->ID ) && ! isset( $_POST['existing_post_id'] ) ) {
		?>
		We understand you are excited to give your host feedback, but you've already submitted a review for this host.<br />
		If you need to make changes, please wait a few minutes and <a href="<?php the_permalink(); ?>edit">edit your review here</a>.
		<?php
	} elseif ( is_user_logged_in() && ! $ip_ban ) {
		global $current_user;
		get_currentuserinfo();

		// Do some minor form validation to make sure there is content
		if ( isset( $_POST['title'] ) ) { $title =  $_POST['title']; } else { wp_die( 'Please enter a title' ); }

		$star_ratings = $_POST['star_ratings'];
		$post_content = isset( $_POST['comments'] ) ? sanitize_text_field( $_POST['comments'] ) : '';
		$xpost = isset( $_POST['xpost'] ) ? 'yes' : 'no';
		$reinvoices = isset( $_POST['reinvoices'] ) ? $_POST['reinvoices'] : 'na';
		$no_issues = isset( $_POST['issues_na'] ) ? $_POST['issues_na'] : false;

		// Add the content of the form to $post as an array
		$review = array(
			'post_title'	=> sanitize_text_field( $title ),
			'post_status'	=> 'pending', // Choose: publish, preview, future, etc.
			'post_type'		=> 'reviews', // Set the post type based on the IF is post_type X
			'post_content'   => $post_content,
			'comment_status' => 'open',
			'ping_status'    => 'closed',
			'post_parent'   => $post->ID
		);

		if ( isset( $_POST['existing_post_id'] ) ) {
			$id = $_POST['existing_post_id'];
			$review['ID'] = $id;
			wp_update_post( $review );
		} else {
			$id = wp_insert_post( $review );
		}

		if ( !empty( $no_issues ) ) {
			$star_ratings['issue_resolution_rating'] = 0;
		}
		// Set the Host to be associated with the group
		update_post_meta( $id, '_review_star_ratings', $star_ratings );
		update_post_meta( $id, '_review_xpost', $xpost );
		update_post_meta( $id, '_review_reinvoices', $reinvoices );
		update_post_meta( $id, '_review_issues_na', $no_issues );

		if ( !empty( $current_user->user_email ) ) {
			$message  = 'Hi ' . $current_user->user_firstname . ',' . "\n";
			$message .= 'We\'ve recieved your review for ' . get_the_title( $post->ID ) . '.' . "\n";
			$message .= 'It will be reviewed soon, and if approved you will be notified via email.';
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $current_user->user_email, 'Host Review Recieved', $message );
		}

		// Tell the Admins
		$edit_url = admin_url( 'post.php?post=' . $id . '&action=edit&post_type=review' );
		$admin_message  = 'A new review has been submitted for approval on Host Reviews Board' . "\n\n";
		$admin_message .= 'Please login and <a href="' . $edit_url . '"" target="_blank">moderate this review</a>';

		wp_mail( 'info@hostreviewsboard.com', 'New Review to Moderate', $admin_message );
		?>
		<script>ga('send', 'event', { eventCategory: 'review', eventAction: 'submitted'});</script>
		<h4>Thanks for your review! We will look it over and, if approved, it will be published. If we see any issues, we'll let you know.</h4>
		<?php
	} else {
		?><h4>Slow down there!</h4><?php
	}
}
add_shortcode( 'host_review_submit', 'host_review_submit' );

function host_search_callback() {
	?>
	<h3><?php _e( 'Search for hosts near you', 'rah' ); ?></h3>
	<div class="search-fields">
		<label for="zip_code"><?php _e( 'Zip Code', 'rah' ); ?></label> <input value= "" type="number" maxlength="5" size="5" name="zip_code" id="zip_code" pattern="[\d]{5}" placeholder="12345" />
		<label for="distance"><?php _e( 'Within', 'rah' ); ?></label>
		<select name="distance" id="distance">
			<option value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="150">150</option>
			<option value="300">300</option>
		</select>
		<?php _e( 'Miles', 'rah' ); ?>
		<input type="submit" id="submit-host-search" value="<?php _e( 'Search', 'rah' ); ?>" />
		<span class="rah-loading"></span>
		<?php wp_nonce_field( 'rah-search-hosts' ); ?>
	</div>
	<div class="search-results" style="display: none;">
		<h4 class="search-meta">Showing <span class="user-input" id="found-hosts"></span> hosts within <span class="user-input" id="chosen-distance"></span> miles of <span class="user-input" id="chosen-zip"></span></h4>
		<div class="results-set"></div>
	</div>
	<?php
}
add_shortcode( 'host_search', 'host_search_callback' );
