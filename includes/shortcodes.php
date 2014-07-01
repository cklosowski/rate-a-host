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
					you to recieve notificaitons of new reviews, manage your presense, and respond to reviews.
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
				<h5><label for="name">Reviewing</label></h5>
				<p>
					<input size="50" id="name" name="host_name" readonly type="text" value="<?php the_title(); ?>" />
				</p>

				<h5><label for="title">Review Summary</label></h5>
				<p>
					<input size="50" id="title" name="title" type="text" value="" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" />
				</p>

				<h5><label for="buys">Buys</label></h5>
				<p>
					<input type="number" name="buys_rating" id="buys" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<p>
					Do they ask the group to gauge interest? Do they open the buys when stated? Are the buy documents clear and easy to understand? Do they provide good pictures of the items for sale?
					Anything that relates to the buy process, before it closes.
					</p>
				</p>

				<h5><label for="post-buy">Post-Buy</label></h5>
				<p>
					<input type="number" name="post_buy_rating" id="post-buy" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<p>
					Do they update the group about vendor-to-host shipping status? Do they post when a shipment has arrived? Are invoices promptly delivered. How long does it take to sort and ship?
					Are items shipped within the host's alloted timeframe?
					</p>
				</p>

				<h5><label for="issues">Issue Resolution</label></h5>
				<p>
					<input type="number" name="issues_rating" id="issues" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<p>
					How does the host react to seller/vendor issues? Do they promptly state when there are issues? Are they clear or transparent about the issues that to arrise? Do they resolve them in a
					reasonablie amount of time.
					</p>
				</p>

				<h5><label for="personality">Personality</label></h5>
				<p>
					<input type="number" name="personality_rating" id="personality" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" />
					<p>
					Is the host considerate, open to questions, responds in stated timeframes (or within reason)?
					</p>
				</p>

				<h5><label for="comments">Additional Comments</label></h5>
				<p>
					<textarea name="comments" id="comments"></textarea>
				</p>

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
				<h5><label for="name">Reviewing</label></h5>
				<p>
					<input size="50" id="name" name="host_name" readonly type="text" value="<?php the_title(); ?>" />
				</p>

				<h5><label for="title">Review Summary</label></h5>
				<p>
					<input size="50" id="title" name="title" type="text" value="<?php echo $post_data->post_title; ?>" />
				</p>

				<p>
					<strong><label for="xpost">This rating is for a cross-post</label></strong>&nbsp;<input id="xpost" type="checkbox" name="xpost" value="yes" <?php checked( 'yes', $xpost, true ); ?> />
				</p>

				<h5><label for="buys">Buys</label></h5>
				<p>
					<input type="number" name="buys_rating" id="buys" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['buys_rating']; ?>" />
					<p>
					Do they ask the group to gauge interest? Do they open the buys when stated? Are the buy documents clear and easy to understand? Do they provide good pictures of the items for sale?
					Anything that relates to the buy process, before it closes.
					</p>
				</p>

				<h5><label for="post-buy">Post-Buy</label></h5>
				<p>
					<input type="number" name="post_buy_rating" id="post-buy" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['post_buy_rating']; ?>" />
					<p>
					Do they update the group about vendor-to-host shipping status? Do they post when a shipment has arrived? Are invoices promptly delivered. How long does it take to sort and ship?
					Are items shipped within the host's alloted timeframe?
					</p>
				</p>

				<h5><label for="issues">Issue Resolution</label></h5>
				<p>
					<input type="number" name="issues_rating" id="issues" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['issues_rating']; ?>" />
					<p>
					How does the host react to seller/vendor issues? Do they promptly state when there are issues? Are they clear or transparent about the issues that to arrise? Do they resolve them in a
					reasonablie amount of time.
					</p>
				</p>

				<h5><label for="personality">Personality</label></h5>
				<p>
					<input type="number" name="personality_rating" id="personality" class="rating" data-empty-value="0" data-min="1" data-max="5" data-clearable="Clear" value="<?php echo $ratings['personality_rating']; ?>" />
					<p>
					Is the host considerate, open to questions, responds in stated timeframes (or within reason)?
					</p>
				</p>

				<h5><label for="comments">Additional Comments</label></h5>
				<p>
					<textarea name="comments" id="comments"><?php echo $post_data->post_content; ?></textarea>
				</p>

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
	$ip_ban = rah_check_rate_limit();
	if ( is_user_logged_in() && ! $ip_ban ) {
		global $current_user, $post;
		get_currentuserinfo();

		// Do some minor form validation to make sure there is content
		if ( isset( $_POST['title'] ) ) { $title =  $_POST['title']; } else { wp_die( 'Please enter a title' ); }

		$buys_rating = $_POST['buys_rating'];
		$post_buy_rating = $_POST['post_buy_rating'];
		$issues_rating = $_POST['issues_rating'];
		$personality_rating = $_POST['personality_rating'];
		$post_content = isset( $_POST['comments'] ) ? sanitize_text_field( $_POST['comments'] ) : '';
		$xpost = isset( $_POST['xpost'] ) ? 'yes' : 'no';

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

		$star_ratings = array( 'buys_rating'        => $buys_rating,
			                   'post_buy_rating'    => $post_buy_rating,
			                   'issues_rating'      => $issues_rating,
			                   'personality_rating' => $personality_rating );

		// Set the Host to be associated with the group
		update_post_meta( $id, '_review_star_ratings', $star_ratings );
		update_post_meta( $id, '_review_xpost', $xpost );

		?>
		<h4>Thanks for your review! We will look it over and, if approved, it will be published. If we see any issues, we'll let you know.</h4>
		<?php
	} else {
		?><h4>Slow down there!</h4><?php
	}
}
add_shortcode( 'host_review_submit', 'host_review_submit' );