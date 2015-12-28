<?php

function rah_add_host_og() {
	if ( ! is_single() ) {
		return;
	}

	global $post;

	if ( $post->post_type !== 'hosts' ) {
		return;
	}

	$user_id = get_user_id_from_host_id( $post->ID );

	$og_tags = array(
		'title'       => 'Host Reviews for ' . $post->post_title,
		'site_name'   => 'Host Reviews Board',
		'url'         => get_permalink( $post->ID ),
		'description' => 'Host reivews, ratings, and feedback for ' . $post->post_title,
		'image'       => get_user_meta( $user_id, '_social_connect_avatar_url', true ),
		'type'        => 'profile'
	);

	$user_data = get_userdata( $user_id );
	$profile_tags = array(
		'first_name' => $user_data->first_name,
		'last_name'  => $user_data->last_name
	);

	foreach ( $og_tags as $key => $value ) {
		$value = ( $key === 'url' || $key === 'image' ) ? $value : esc_attr( $value );
		echo '<meta property="og:' . $key . '" content="' . $value . '" />';
	}

	foreach ( $profile_tags as $key => $value ) {
		echo '<meta property="profile:' . $key . '" content="' . esc_attr( $value ) . '" />';
	}

}
add_action( 'wp_head', 'rah_add_host_og', 2 );

function rah_add_group_og() {
	global $post;

	if ( ! is_single() ) {
		return;
	}

	if ( $post->post_type !== 'groups' ) {
		return;
	}

	$og_tags = array(
		'title'       => 'Host Reviews for ' . $post->post_title,
		'site_name'   => 'Host Reviews Board',
		'url'         => get_permalink( $post->ID ),
		'description' => 'Host list, reivews, ratings, and feedback for ' . $post->post_title,
		'image'       =>  get_post_meta( $post->ID, '_rah_group_fb_icon', true )
	);

	foreach ( $og_tags as $key => $value ) {
		$value = ( $key === 'url' || $key === 'image' ) ? $value : esc_attr( $value );
		echo '<meta property="og:' . $key . '" content="' . $value . '" />';
	}

}
add_action( 'wp_head', 'rah_add_group_og', 2 );
