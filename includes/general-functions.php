<?php

function rah_no_login_php() {
	if ( !isset( $_GET['ck_rah_override'] ) ) {
		?><meta http-equiv="refresh" content="0; url=<?php echo get_bloginfo( 'url' ); ?>" /><?php
	}
}

function rah_set_social_tokens() {
	define( 'FB_API_KEY', get_option( 'social_connect_facebook_api_key' ) );
	define( 'FB_API_SECRET', get_option( 'social_connect_facebook_secret_key' ) );
}

function rah_get_groups_ajax() {
	global $current_user;
	get_currentuserinfo();

	$access_token = get_user_meta( $current_user->ID, 'fb_access_token', true );
	$fb_id = get_user_meta( $current_user->ID, 'social_connect_facebook_id', true );

	$group_name = urlencode( sanitize_text_field( $_POST['group'] ) );

	if ( is_numeric( $group_name ) ) {
		$url = 'https://graph.facebook.com/' . $group_name . '?client_id=' . FB_API_KEY . '&client_secret=' . FB_API_SECRET . '&access_token=' . $access_token;
		$results = json_decode( wp_remote_retrieve_body( wp_remote_get( $url ) ), true );
		if ( !isset( $results['error'] ) ) {
			$name = $results['name'];
			$id = $results['id'];
			echo '<input type="radio" name="group_id" value="' . $id . '" />' . $name . '<br />';
		} else {
			echo '<p class="alerts error">Group Unable to be located. If your group is secret, please check the box above.<p>';
		}
	} else {
		$url = 'https://graph.facebook.com/search?q=' . $group_name . '&type=group&limit=10&client_id=' . FB_API_KEY . '&client_secret=' . FB_API_SECRET . '&access_token=' . $access_token;
		$results = json_decode( wp_remote_retrieve_body( wp_remote_get( $url ) ), true );
		if ( !isset( $results['error'] ) && count( $results['data'] > 0 ) ) {
			foreach ( $results['data'] as $group ) {
				$name = $group['name'];
				$id   = $group['id'];
				echo '<input type="radio" name="group_id" value="' . $id . '" />' . $name . ' - <a target="_blank" href="https://facebook.com/groups/' . $id . '">View on Facebook</a><br />';
			}
		} else {
			echo '<p class="alerts notice">No groups found<p>';
		}
	}
	die();
}

function rah_get_secret_groups_ajax() {
	$sg_query = new WP_Query( "post_type=groups&orderby=title&order=ASC&posts_per_page=-1&meta_key=_rah_secret_group&meta_value=1" );

	if ( $sg_query->have_posts() ) {
		?>
		<p id="existing_secret">
			<select id="existing_groups" name="existing_secret_group">
				<option value="-1">Select A Group</option>
				<?php
				while( $sg_query->have_posts() ) {
					$sg_query->the_post();
					?><option value="<?php the_id(); ?>"><?php the_title(); ?></option><?php
				}
			?>
			</select>&nbsp;<input type="checkbox" id="group_not_listed" name="group_not_listed" value="1">&nbsp;<label for="group_not_listed">My Group Isn't Listed</label>
		</p>
		<p id="new_secret_group" style="display:none;">
			<input type="text" size="50" id="new_secret_group_title" name="new_secret_group_title" value="" placeholder="Your Group Name">
			<textarea name="new_secret_group_description" placeholder="Group Description. You can copy this from your Facebook Group."></textarea>
		</p>
		<?php
	} else {
		?>
		<p id="new_secret_group">
			<input type="hidden" name="group_not_listed" value="1" />
			<input type="text" size="50" id="new_secret_group_title" name="new_secret_group_title" value="" placeholder="Your Group Name">
			<textarea name="new_secret_group_description" placeholder="Group Description. You can copy this from your Facebook Group."></textarea>
		</p>
		<?php
	}
	die();
}

function rah_insert_host() {
	if ( isset( $_POST['group_id'] ) && !isset( $_POST['is_secret'] ) ) {
		$group_id = rah_insert_public_group( $_POST['group_id'] );
	} elseif ( isset( $_POST['is_secret'] ) ) {
		if ( isset( $_POST['existing_secret_group'] ) && !empty( $_POST['existing_secret_group'] ) && $_POST['existing_secret_group'] !== -1 && !isset( $_POST['group_not_listed'] ) ) {
			$group_exists = get_post( $_POST['existing_secret_group'] );
			if ( !empty( $group_exists ) && $group_exists->post_type === 'groups' ) {
				$group_id = $_POST['existing_secret_group'];
			}
		} elseif ( isset( $_POST['group_not_listed'] ) ) {
			$group_title = isset( $_POST['new_secret_group_title'] ) ? sanitize_text_field( $_POST['new_secret_group_title'] ) : '';
			$group_description = isset( $_POST['new_secret_group_description'] ) ? sanitize_text_field( $_POST['new_secret_group_description'] ) : '';
			$group_id = rah_insert_secret_group( $group_title, $group_description );
		}
	}

	global $current_user, $wpdb;
	get_currentuserinfo();

	$results = $wpdb->get_results( 'SELECT meta_value FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND user_id = "' . $current_user->ID . '"');

	if ( count( $results ) > 0 ) {
		$id =  $results[0]->meta_value;
		$host = array(
			'post_status'	=> 'pending', // Choose: publish, preview, future, etc.
			'post_type'		=> 'hosts', // Set the post type based on the IF is post_type X
			'ID'            => $id,
			'post_parent'   => $group_id
		);
		wp_update_post( $host );
	} else {
		// Do some minor form validation to make sure there is content
		if ( isset( $_POST['host_title'] ) ) { $title =  $_POST['host_title']; } else { wp_die( 'Please enter a title' ); }

		// Add the content of the form to $post as an array
		$host = array(
			'post_title'	=> sanitize_text_field( $title ),
			'post_status'	=> 'pending', // Choose: publish, preview, future, etc.
			'post_type'		=> 'hosts', // Set the post type based on the IF is post_type X
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		);

		if ( isset( $group_id ) && !empty( $group_id ) ) {
			$host['post_parent'] = $group_id;
		}


		$id = wp_insert_post( $host );
		update_user_meta( $current_user->ID, '_user_host_id', $id );


		// Set the Host ID to be related to the user ID
	}

	wp_set_post_terms( $id, (int)$_POST['cat'], 'type', false);

	if ( isset( $group_id ) && !empty( $group_id ) ) {
		// Set the Host to be associated with the group
		update_post_meta( $id, '_user_group_id', $group_id );
	}

	// Tell the Admins
	$edit_host = admin_url( 'post.php?post=' . $id . '&action=edit&post_type=host' );

	$admin_message  = 'A new host has registered for approval on Host Reviews Board' . "\n\n";
	if ( isset( $group_id ) && !empty( $group_id ) ) {
		$edit_group = admin_url( 'post.php?post=' . $group_id . '&action=edit&post_type=group' );
		$admin_message .= 'Please login and <a href="' . $edit_group . '"" target="_blank">verify this group.</a>' . "\n";
	}
	$admin_message .= 'Please login and <a href="' . $edit_host . '"" target="_blank">verify this host.</a>';

	wp_mail( 'info@hostreviewsboard.com', 'New Review to Moderate', $admin_message );

	wp_redirect( '/host-dashboard' );
	die();
}

function rah_insert_public_group( $group_id ) {
	global $wpdb;
	$results = $wpdb->get_results( 'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_rah_group_fb_id" AND meta_value = "' . $group_id . '"');

	if ( count( $results ) > 0 ) {
		return $results[0]->post_id;
	} else {

		global $current_user;
		get_currentuserinfo();

		$access_token = get_user_meta( $current_user->ID, 'fb_access_token', true );
		$group_id = trim( $group_id );

		if ( empty( $group_id ) ) {
			return false;
		}

		$url = 'https://graph.facebook.com/'. $group_id . '?client_id=' . FB_API_KEY . '&client_secret=' . FB_API_SECRET . '&access_token=' . $access_token;

		$results = json_decode( wp_remote_retrieve_body( wp_remote_get( $url ) ), true );

		// Add the content of the form to $post as an array
		$group = array(
			'post_title'	 => $results['name'],
			'post_content'	 => $results['description'],
			'post_status'	 => 'pending', // Choose: publish, preview, future, etc.
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_type'		 => 'groups' // Set the post type based on the IF is post_type X
		);

		$id = wp_insert_post( $group );

		// Add some group meta
		update_post_meta( $id, '_rah_group_fb_id', $results['id'] );
		update_post_meta( $id, '_rah_group_fb_icon', $results['icon'] );

	}

	return $id;
}

function rah_insert_secret_group( $group_title, $group_description ) {
	global $current_user;
	get_currentuserinfo();

	// Add the content of the form to $post as an array
	$group = array(
		'post_title'	 => $group_title,
		'post_content'	 => $group_description,
		'post_status'	 => 'pending', // Choose: publish, preview, future, etc.
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_type'		 => 'groups' // Set the post type based on the IF is post_type X
	);

	$id = wp_insert_post( $group );

	// Add some group meta
	update_post_meta( $id, '_rah_secret_group', '1' );

	return $id;
}

function rah_is_registered_host() {
	global $current_user, $wpdb;
	get_currentuserinfo();

	$results = $wpdb->get_results( 'SELECT meta_value FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND user_id = "' . $current_user->ID . '"');

	if ( count( $results ) > 0 ) {
		return true;
	}

	return false;

}

function get_user_id_from_host_id( $host_id ) {
	global $wpdb;
	$results = $wpdb->get_results( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND meta_value = "' . $host_id . '"');

	return $results[0]->user_id;
}

function get_host_id_from_user_id( $user_id ) {
	return get_user_meta( $user_id, '_user_host_id', true );
}

function user_is_host( $host_id ) {
    global $current_user;
    get_currentuserinfo();
    $user_host_id = (int)get_user_meta( $current_user->ID, '_user_host_id', true );
    return ( $user_host_id === $host_id );
}

function is_the_host() {
	if ( !is_single() ) {
		return;
	}

	global $post, $current_user;
	get_currentuserinfo();

	$host_id = get_host_id_from_user_id( $current_user->ID );

	if ( empty( $host_id ) ) {
		return false;
	}

	$host_id = (int)$host_id;

	switch( $post->post_type ) {
		case 'reviews':
			if ( $post->post_parent === $host_id ) {
				return true;
			}
		break;

		case 'hosts':
			if ( $post->ID === $host_id ) {
				return true;
			}
		break;
	}

	return false;
}

function host_has_replied( $post_id ) {
	if ( empty( $post_id ) ) {
		return false;
	}

	$args = array(
			'post_id' => $post_id,
		);

	$comments = get_comments( $args );

	if ( empty( $comments ) ) {
		return false;
	}

	$post_data = get_post( $post_id );

	global $current_user;
	get_currentuserinfo();
	$host_id = get_host_id_from_user_id( $current_user->ID );

	if ( empty( $host_id ) ) {
		return false;
	}

	$host_id = (int)$host_id;

	foreach ( $comments as $comment ) {
		if ( $host_id === $post_data->post_parent ) {
			return true;
		}
	}

	return false;
}

function has_user_reviewed_host( $host_id ) {
	$user_id = get_current_user_id();
	$args = array(
	'orderby'          => 'post_date',
	'author'           => $user_id,
	'order'            => 'DESC',
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'reviews',
	'post_mime_type'   => '',
	'post_parent'      => $host_id,
	'post_status'      => 'all',
	'suppress_filters' => true );

	$posts = get_posts( $args );
	if ( count( $posts ) > 0 ) {
		return $posts[0]->ID;
	}

	return false;

}

function rah_generate_stars( $number ) {
	if ( $number === 0 ) {
		return 'N/A';
	}

	$whole = floor( $number );
	$fraction = $number - $whole;
	$i = 0;
	$total = 5;
	$output = '';
	while( $i < $whole ) {
		$output .= '<span class="ratings dashicons dashicons-star-filled"></span>';
		$i++;
	}

	if ( $fraction > 0 ) {
		$output .= '<span class="ratings dashicons dashicons-star-half"></span>';
		$i++;
	}

	if ( $i < 5) {
		while ( $i < 5 ) {
			$output .= '<span class="ratings dashicons dashicons-star-empty"></span>';
			$i++;
		}
	}

	return $output;
}

function rah_recalculate_host_ratings( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'reviews' ) {
		return;
	}

    if ( ( $new_status == 'publish' && $old_status != 'publish' ) ||
         ( $old_status == 'publish' && $new_status != 'publish' ) ) {
    	rah_run_recalculation( $post->post_parent );
    }
}

function rah_run_recalculation( $host_id ) {
	$args = array(
		'orderby'          => 'post_date',
		'order'            => 'DESC',
		'include'          => '',
		'exclude'          => '',
		'meta_key'         => '',
		'meta_value'       => '',
		'post_type'        => 'reviews',
		'post_mime_type'   => '',
		'post_parent'      => $host_id,
		'post_status'      => 'publish',
		'posts_per_page'   => -1,
		'suppress_filters' => true );

    $host_reviews = get_posts( $args );

    $total_reviews = 0;
    $total_points = 0;
    foreach ( $host_reviews as $review ) {
    	$review_ratings = get_post_meta( $review->ID, '_review_star_ratings', true );
    	$number_of_ratings = 0;
    	$post_total = 0;
    	foreach ( $review_ratings as $review ) {
    		if ( !empty ( $review ) ) {
    			$post_total += (int)$review;
    			$number_of_ratings++;
    		}

    	}
    	$post_total = round( ( $post_total/$number_of_ratings ) * 2, 0 ) / 2;
    	$total_points += $post_total;
    	$total_reviews++;
    }
    if ( $total_points > 0 ) {
 		$host_rating = round( ( $total_points/$total_reviews ) * 2, 0 ) / 2;
    } else {
    	$host_rating = 0;
    }
    update_post_meta( $host_id, '_host_rating', $host_rating );
    update_post_meta( $host_id, '_host_review_count', $total_reviews );
}

function rah_get_group_rating_stats( $group_id ) {
	$hosts = get_children( array( 'post_parent' => $group_id, 'post_type' => 'hosts', 'posts_per_page' => -1, 'post_status' => 'publish' ), ARRAY_A );
	$host_count = is_array( $hosts ) ? count( $hosts ) : 0;
	$group_rating = 0;
	$group_reviews = 0;
	$total_score = 0;
	$total_hosts = 0;
	if ( !empty( $hosts ) ) {
		foreach ( $hosts as $host ) {
			$host_reviews = get_post_meta( $host['ID'], '_host_review_count', true );
			if ( $host_reviews > 0 ) {
				$group_reviews += $host_reviews;
				$total_score += get_post_meta( $host['ID'], '_host_rating', true );
				$total_hosts++;
			}
		}
		if ( $total_hosts > 0 ) {
			$group_rating = round( ( $total_score/$total_hosts ) * 2, 0 ) / 2;
		}
	}

	return array( 'group_rating' => $group_rating, 'group_reviews' => $group_reviews );
}

function rah_send_host_email( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'hosts' ) {
		return;
	}

	// Approved
	if ( $new_status == 'publish' && $old_status != 'publish' ) {
		$user_id = get_user_id_from_host_id( $post->ID );
		$user_info = get_userdata( $user_id );

		if ( !empty( $user_info->user_email ) ) {
			$message  = 'Hi ' . $user_info->first_name . ',' . "\n";
			$message .= 'We\'ve looked over your application and have approved your account. Your members can start reviewing you at:' . "\n";
			$message .= get_permalink( $post->ID ) . 'new/';
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $user_info->user_email, 'Host Account Approved', $message );
		}
	}

	// Declined
	if ( $new_status == 'declined' && $old_status != 'declined' ) {
		// The the author
		$user_id = get_user_id_from_host_id( $post->ID );
		$user_info = get_userdata( $user_id );

		if ( !empty( $user_info->user_email ) ) {
			if ( isset( $_POST['_review_declined_reason'] ) && !empty( $_POST['_review_declined_reason'] ) ) {
				$declined_reason = sanitize_text_field( $_POST['_review_declined_reason'] );
			} else {
				$declined_reason = 'Please contact us for further information reguarding this rejection.';
			}
			$message  = 'Hi ' . $user_info->first_name . ',' . "\n";
			$message .= 'Your request to be listed as a host has been deined for the following reason(s):' . "\n";
			$message .= $declined_reason;
			$message .= "\n";
			$message .= 'You can edit your request to be a host, and re-submit it for approval if you wish.';
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $user_info->user_email, 'Host Account Declined', $message );
		}
	}
}

function rah_send_user_review_email( $new_status, $old_status, $post ) {
	if ( $post->post_type !== 'reviews' ) {
		return;
	}

	// Approved
	if ( $new_status == 'publish' && $old_status != 'publish' ) {
		// The the author
		$user_info = get_userdata( $post->post_author );
		$parent = wp_get_post_parent_id( $post->ID );
		$parent_post = get_post( $parent );

		if ( !empty( $user_info->user_email ) ) {
			$message  = 'Hi ' . $user_info->first_name . ',' . "\n";
			$message .= 'Your review for ' . $parent_post->post_title . ' has been approved.';
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $user_info->user_email, 'Host Review Approved', $message );
		}

		// Tell the host
		$host_user_id = get_user_id_from_host_id( $parent );
		$host_info = get_userdata( $host_user_id );
		if ( !empty( $host_info->user_email ) ) {
			$ratings = get_post_meta( $post->ID, '_review_star_ratings', true );
			$xpost = get_post_meta( $post->ID, '_review_xpost', true );
			$reinvoices = get_post_meta( $post->ID, '_review_reinvoices', true );
			$message  = 'Hi ' . $host_info->first_name . ',' . "\n";
			$message .= 'You have recieved a new review with the following results:' . "\n";
			$message .= 'Title: ' . $post->post_title . "\n";
			$message .= 'For Cross Post: ' . $xpost . "\n";
			$message .= 'Reinvoices before 45 days: ' . $reinvoices . "\n";
			foreach ( $ratings as $key => $rating ) {
				$message .= ucwords( str_replace( array( '_', 'rating', 'and' ), array( ' ', '', '&' ), $key ) ) . ': ' . $rating . "\n";
			}
			$message .= 'Comments: ' . $post->post_content;
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $host_info->user_email, 'You\'ve Recieved a new Host Review', $message );
		}

	}

	// Declined
	if ( $new_status == 'declined' && $old_status != 'declined' ) {
		// The the author
		$user_info = get_userdata( $post->post_author );
		$parent = wp_get_post_parent_id( $post->ID );
		$parent_post = get_post( $parent );

		if ( !empty( $user_info->user_email ) ) {
			if ( isset( $_POST['_review_declined_reason'] ) && !empty( $_POST['_review_declined_reason'] ) ) {
				$declined_reason = sanitize_text_field( $_POST['_review_declined_reason'] );
			} else {
				$declined_reason = 'Please contact us for further information reguarding this rejection.';
			}
			$message  = 'Hi ' . $user_info->first_name . ',' . "\n";
			$message .= 'Your review for ' . $parent_post->post_title . ' has been deined for the following reason(s):' . "\n";
			$message .= $declined_reason;
			$message .= "\n";
			$message .= 'You can edit your review to correct this, and re-submit it for approval if you wish.';
			$message .= "\n\n";
			$message .= 'Thanks,' . "\n";
			$message .= 'The Host Reviews Board Team';

			wp_mail( $user_info->user_email, 'Host Review Denied', $message );
		}
	}
}

function rah_check_rate_limit() {
	$user_ip = $_SERVER['REMOTE_ADDR'];
	$ip_hash = md5( $user_ip );
	$last_logs = get_option( '_rah_ip_logs', array() );

	if ( empty( $current_counts  ) ) {
		add_option( '_rah_ip_logs', $last_logs, '', 'no' );
	}

	if ( isset( $last_logs[$ip_hash] ) ) {
		if ( ( time() - $last_logs[$ip_hash] ) < 600 ) {
			return true;
		}
	}

	$last_logs[$ip_hash] = time();
	update_option( '_rah_ip_logs', $last_logs );
	return false;

}

function rah_add_user_id_column( $columns ) {
	$columns['fb_link'] = 'Facebook Link';
	return $columns;
}
add_filter( 'manage_users_columns', 'rah_add_user_id_column' );
add_filter( 'manage_groups_posts_columns' , 'rah_add_user_id_column' );
add_filter( 'manage_reviews_posts_columns', 'rah_add_user_id_column' );
add_filter( 'manage_hosts_posts_columns', 'rah_add_user_id_column' );

function rah_show_user_id_column_content( $value, $column_name, $user_id ) {
	$fb_id = get_user_meta( $user_id, 'social_connect_facebook_id', true );
	$fb_link = 'https://facebook.com/' . $fb_id;

	if ( 'fb_link' == $column_name ) {
		return '<a href="' . $fb_link . '" target="_blank">View User on Facebook</a>';
	}

	return $value;
}
add_action( 'manage_users_custom_column', 'rah_show_user_id_column_content', 10, 3 );


function custom_columns( $column, $post_id ) {
	if ( $column !== 'fb_link' ) {
		return;
	}

	$post_type = get_post_type( $post_id );
	if ( $post_type !== 'reviews' && $post_type !== 'groups' && $post_type !== 'hosts' ) {
		return;
	}

	switch( $post_type ) {
		case 'reviews':
			$type = 'Review Author';
			$post_data = get_post( $post_id );
			$fb_id = get_user_meta( $post_data->post_author, 'social_connect_facebook_id', true );
			break;
		case 'groups':
			$type = 'Group';
		    $fb_id = get_post_meta( $post_id, '_rah_group_fb_id', true );
		    break;
		case 'hosts':
			$type = 'Host';
			$user_id = get_user_id_from_host_id( $post_id );
			$fb_id = get_user_meta( $user_id, 'social_connect_facebook_id', true );
			break;

	}

    $fb_link = 'https://facebook.com/' . $fb_id;
    if ( $post_type === 'groups' && empty( $fb_id ) ) {
    	echo 'Group is Secret';
    } else {
		echo '<a href="' . $fb_link . '" target="_blank">View ' . $type . ' on Facebook</a>';
	}
}
add_action( 'manage_posts_custom_column' , 'custom_columns', 10, 2 );

function rah_filter_wp_mail_from_name( $from_name ) {
	return 'Host Reviews Board';
}
add_filter( 'wp_mail_from_name', 'rah_filter_wp_mail_from_name' );


function rah_notify_approval_to_reviewer ( $comment_id ) {
	$comment = get_comment( $comment_id );
	if ( !$comment ) {
		return;
	}

	if ( $comment->comment_approved == 1 ) {
		$review = get_post( $comment->comment_post_ID );
		if ( $review->post_type !== 'reviews' ) {
			return;
		}

		$host = get_post( $review->post_parent );
		$author = get_userdata( $review->post_author );
		$message  = '';
		$message .= 'Hi,' . "\n" . 'We just wanted to let you know that ' . stripslashes( $host->post_title ) . ' has replied to your review';
		$message .= "\n";
		$message .= 'Their reply was:' . "\n\n";
		$message .= stripslashes( $comment->comment_content );
		$message .= "\n\n";
		$message .= 'As a reminder, you can update any of your reviews at anytime.';
		$message .= "\n\n";
		$message .= 'Thanks,' . "\n";
		$message .= 'The Host Reviews Board Team';

		wp_mail(
			$author->user_email,
			stripslashes( $host->post_title ) . ' has replied to your host review.',
			$message
		);
	}
}
add_action( 'wp_set_comment_status', 'rah_notify_approval_to_reviewer' );
add_action( 'edit_comment', 'rah_notify_approval_to_reviewer' );

