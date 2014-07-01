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
					Hi <?php echo $current_user->user_firstname; ?>,<br />
					Please fill out the following information in order to register yourself as a Co-Op Host. Registering as a host allows
					you to receive notifications of new reviews.
				</p>

				<label for="title">Host Name:</label>
				<p><input size="50" id="title" name="host_title" readonly type="text" value="<?php echo $current_user->user_firstname . ' ' . $current_user->user_lastname; ?>" /></p>
				<label for="group">Group:</label>
				<p>
					<label for="is_private">My Group is marked "Secret"</label><input id="is_secret" type="checkbox" value="is_secret" value="1" />
					<input id="group_input" size="50" id="group" name="host_group" type="text" value="" placeholder="http://facebook.com/group/your-group-name" /><span class="rah-loading"></span>
					<div class="hidden" id="response"></div>
				</p>

				<label for="group-type">Group Type</label>
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

				<p><input type="submit" value="Submit" tabindex="6" id="submit" name="submit" /></p>


				<input type="hidden" name="type" id="type" value="hosts" />
				<input type="hidden" name="action" value="register-host" />
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
		?>
		<p>
			<strong><em>At this time we only support registering as the host for a single group.</em></strong>
		</p>
		<p class="fine-print">
			<sup>*If you signed up and chose the incorrect group, please contact us.</sup>
		</p>
		<?php
	}
}
add_shortcode( 'host_registration_form', 'host_registration_form' );

function host_review_form( $atts ) {
	if ( is_user_logged_in() ) {
		global $current_user, $post;
		get_currentuserinfo();
		if ( !has_user_reviewed_host( $post->ID ) ) :
		?>
		<div class="rah-before-form"></div>
		<div id="postbox" class="rah-form">

			<form id="review_host" name="new_post" method="post" action="<?php echo get_permalink( $post->ID); ?>submit">
				<h3>Reviewing</h3>
				<p>
					<input size="50" id="name" name="host_name" readonly type="text" value="<?php the_title(); ?>" />
				</p>

				<h3>Review Summary</h3>
				<p>
					<input size="50" id="title" name="title" type="text" value="" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" />
				</p>

				<p>
					<strong><label for="reinvoices">This host re-invoices within 45 days if requested</label></strong><br />
					<input type="radio" name="reinvoices" value="yes" />&nbsp;Yes<br />
					<input type="radio" name="reinvoices" value="no" />&nbsp;No<br />
					<input type="radio" name="reinvoices" value="na" />&nbsp;Not Applicable
				</p>

				<h3>Efficiency</h3>
				<p>
					<input type="number" name="star_ratings[invoicing_and_ordering_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="" />
					<label for="">Did the host invoice and pay for your buy in a timeframe you felt was appropriate?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[sorting_and_packing_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="post_order">Did the host sort, pack, and invoice for shipping in an appropriate amount of time after the <strong><em>entire</em></strong> order was received?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[shipping_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="shipping">Was the order mailed, after shipping was paid, in a timeframe that was appropriate?</label>
				</p>

				<h3>Communication/Resolution</h3>
				<p>
					<input type="number" name="star_ratings[communication_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="communication">How well did the host communicate updates and information about the buys from open until shipment to you?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[professionalism_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="post_order">Did you feel the host was friendly, helpful, and professional through all steps of your buy?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[issue_resolution_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="issues">If there was a problem, or issue, did the host seem willing to work towards a resolution (including re-invoicing if requested), and follow through with the promised resolution?</label>
				</p>

				<h3>Recommends Host</h3>
				<p>
					<input type="number" name="star_ratings[recommends_host_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<label for="recommendation">How willing are you to buy from this host again, or recommend this host to other buyers?</label>
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
	if ( is_user_logged_in() ) {
		global $current_user, $post;
		get_currentuserinfo();
		$review_id = has_user_reviewed_host( $post->ID );
		$post_data = get_post( $review_id );
		$ratings = get_post_meta( $review_id, '_review_star_ratings', true );
		$xpost = get_post_meta( $review_id, '_review_xpost', true );
		$reinvoices = get_post_meta( $review_id, '_review_reinvoices', true );
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

				<h3>Review Summary</h3>
				<p>
					<input size="50" id="title" name="title" type="text" value="<?php echo $post_data->post_title; ?>" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" <?php checked( 'yes', $xpost, true ); ?> />
				</p>

				<p>
					<strong><label for="reinvoices">This host re-invoices within 45 days if requested</label></strong><br />
					<input type="radio" name="reinvoices" value="yes" <?php checked( 'yes', $reinvoices, true ); ?> />&nbsp;Yes<br />
					<input type="radio" name="reinvoices" value="no" <?php checked( 'no', $reinvoices, true ); ?> />&nbsp;No<br />
					<input type="radio" name="reinvoices" value="na" <?php checked( 'na', $reinvoices, true ); ?> />&nbsp;Not Applicable
				</p>

				<h3>Efficiency</h3>
				<p>
					<input type="number" name="star_ratings[invoicing_and_ordering_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['invoicing_and_ordering_rating']; ?>" />
					<label for="">Did the host invoice and pay for your buy in a timeframe you felt was appropriate?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[sorting_and_packing_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['sort_and_packing_rating']; ?>" />
					<label for="post_order">Did the host sort, pack, and invoice for shipping in an appropriate amount of time after the <strong><em>entire</em></strong> order was received?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[shipping_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['shipping_rating']; ?>" />
					<label for="shipping">Was the order mailed, after shipping was paid, in a timeframe that was appropriate?</label>
				</p>

				<h3>Communication/Resolution</h3>
				<p>
					<input type="number" name="star_ratings[communication_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['communication_rating']; ?>" />
					<label for="communication">How well did the host communicate updates and information about the buys from open until shipment to you?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[professionalism_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['professionalism_rating']; ?>" />
					<label for="post_order">Did you feel the host was friendly, helpful, and professional through all steps of your buy?</label>
				</p>
				<p>
					<input type="number" name="star_ratings[issue_resolution_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['issue_resolution_rating']; ?>" />
					<label for="issues">If there was a problem, or issue, did the host seem willing to work towards a resolution (including re-invoicing if requested), and follow through with the promised resolution?</label>
				</p>

				<h3>Recommends Host</h3>
				<p>
					<input type="number" name="star_ratings[recommends_host_rating]" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['recommends_host_rating']; ?>" />
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
	//$ip_ban = rah_check_rate_limit();
	$ip_ban = false;
	if ( is_user_logged_in() && ! $ip_ban ) {
		global $current_user, $post;
		get_currentuserinfo();

		// Do some minor form validation to make sure there is content
		if ( isset( $_POST['title'] ) ) { $title =  $_POST['title']; } else { wp_die( 'Please enter a title' ); }

		$star_ratings = $_POST['star_ratings'];
		$post_content = isset( $_POST['comments'] ) ? sanitize_text_field( $_POST['comments'] ) : '';
		$xpost = isset( $_POST['xpost'] ) ? 'yes' : 'no';
		$reinvoices = isset( $_POST['reinvoices'] ) ? $_POST['reinvoices'] : 'na';

		// Add the content of the form to $post as an array
		$review = array(
			'post_title'	=> sanitize_text_field( $title ),
			'post_status'	=> 'pending', // Choose: publish, preview, future, etc.
			'post_type'		=> 'reviews', // Set the post type based on the IF is post_type X
			'post_content'   => $post_content,
			'comment_status' => 'closed',
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

		// Set the Host to be associated with the group
		update_post_meta( $id, '_review_star_ratings', $star_ratings );
		update_post_meta( $id, '_review_xpost', $xpost );
		update_post_meta( $id, '_review_reinvoices', $reinvoices );

		?>
		<h4>Thanks for your review! We will look it over and, if approved, it will be published. If we see any issues, we'll let you know.</h4>
		<?php
	} else {
		?><h4>Slow down there!</h4><?php
	}
}
add_shortcode( 'host_review_submit', 'host_review_submit' );