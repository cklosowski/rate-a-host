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

function rah_insert_host() {
	if ( isset( $_POST['group_id'] ) && !isset( $_POST['is_secret'] ) ) {
		$group_id = rah_insert_group( $_POST['group_id'] );
	}
	global $current_user, $wpdb;
	get_currentuserinfo();

	$results = $wpdb->get_results( 'SELECT meta_value FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND user_id = "' . $current_user->ID . '"');

	if ( count( $results ) > 0 ) {
		$id =  $results[0]->meta_value;
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

		if ( isset( $group_id ) ) {
			$host['post_parent'] = $group_id;
		}


		$id = wp_insert_post( $host );

		// Set the Host ID to be related to the user ID
		update_user_meta( $current_user->ID, '_user_host_id', $id );
	}

	wp_set_post_terms( $id, (int)$_POST['cat'], 'type', false);

	if ( isset( $group_id ) ) {
		// Set the Host to be associated with the group
		update_post_meta( $id, '_user_group_id', $group_id );
	}

	wp_redirect('/host-dashboard/success');
	die();
}

function rah_insert_group( $group_id ) {
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

function user_is_host( $host_id ) {
    global $current_user;
    get_currentuserinfo();
    $user_host_id = (int)get_user_meta( $current_user->ID, '_user_host_id', true );
    return ( $user_host_id === $host_id );
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

	if ( $new_status == 'publish' && $old_status != 'publish' ) {
		$user_id = get_user_id_from_host_id( $post->ID );
		$user_info = get_userdata( $user_id );

		$message  = 'Hi ' . $user_info->first_name . ',' . "\n";
		$message .= 'We\'ve looked over your application and have approved your account. Your members can start reviewing you at:' . "\n";
		$message .= get_permalink( $post->ID ) . 'new/';
		$message .= "\n\n";
		$message .= 'Thanks,' . "\n";
		$message .= 'The Host Reviews Board Team';

		wp_mail( $user_info->user_email, 'Host Account Approved', $message );
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

add_filter( 'manage_users_columns', 'rah_add_user_id_column' );
add_filter( 'manage_groups_posts_columns' , 'rah_add_user_id_column' );
add_filter( 'manage_reviews_posts_columns', 'rah_add_user_id_column' );
add_filter( 'manage_hosts_posts_columns', 'rah_add_user_id_column' );
function rah_add_user_id_column( $columns ) {
    $columns['fb_link'] = 'Facebook Link';
    return $columns;
}

add_action( 'manage_users_custom_column', 'rah_show_user_id_column_content', 10, 3 );
function rah_show_user_id_column_content( $value, $column_name, $user_id ) {
    $fb_id = get_user_meta( $user_id, 'social_connect_facebook_id', true );
    $fb_link = 'https://facebook.com/' . $fb_id;

	if ( 'fb_link' == $column_name ) {
		return '<a href="' . $fb_link . '" target="_blank">View User on Facebook</a>';
	}

    return $value;
}

add_action( 'manage_posts_custom_column' , 'custom_columns', 10, 2 );
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

	echo '<a href="' . $fb_link . '" target="_blank">View ' . $type . ' on Facebook</a>';
}

function rah_filter_wp_mail_from_name( $from_name ) {
	return 'Host Reviews Board';
}
add_filter( 'wp_mail_from_name', 'rah_filter_wp_mail_from_name' );


