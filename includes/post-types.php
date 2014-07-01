<?php
/**
 * Post Type Functions
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers and sets up the Host custom post type
 *
 * @since 1.0
 * @return void
 */
function rah_setup_post_types() {

	$hosts_labels =  apply_filters( 'rah_hosts_labels', array(
		'name' 				=> 'Hosts',
		'singular_name' 	=> 'Host',
		'add_new' 			=> __( 'Add Host', 'rah-hosts' ),
		'add_new_item' 		=> __( 'Add Host', 'rah-hosts' ),
		'edit_item' 		=> __( 'Edit Host', 'rah-hosts' ),
		'new_item' 			=> __( 'New Host', 'rah-hosts' ),
		'all_items' 		=> __( 'All Hosts', 'rah-hosts' ),
		'view_item' 		=> __( 'View Host', 'rah-hosts' ),
		'search_items' 		=> __( 'Search Hosts', 'rah-hosts' ),
		'not_found' 		=> __( 'No hosts found', 'rah-hosts' ),
		'not_found_in_trash'=> __( 'No hosts found in Trash', 'rah-hosts' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Hosts', 'rah-hosts' )
	) );

	$hosts_args = array(
		'labels' 			=> $hosts_labels,
		'public' 			=> true,
		'publicly_queryable'=> true,
		'show_ui' 			=> true,
		'menu_position'		=> 20,
		'menu_icon' 		=> 'dashicons-businessman',
		'show_in_menu' 		=> true,
		'query_var' 		=> true,
		'map_meta_cap'		=> true,
		'has_archive' 		=> true,
		'hierarchical' 		=> false,
		'supports' 			=> apply_filters( 'rah_hosts_supports', array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'hosts', apply_filters( 'rah_host_post_type_args', $hosts_args  ) );

	register_taxonomy(
		'type',
		'hosts',
		array(
			'label' => __( 'Host Types' ),
			'rewrite' => array( 'slug' => 'type' ),
			'hierarchical' => true
		)
	);

	$reviews_labels =  apply_filters( 'rah_reviews_labels', array(
		'name' 				=> 'Reviews',
		'singular_name' 	=> 'Review',
		'add_new' 			=> __( 'Add Review', 'rah-hosts' ),
		'add_new_item' 		=> __( 'Add Review', 'rah-hosts' ),
		'edit_item' 		=> __( 'Edit Review', 'rah-hosts' ),
		'new_item' 			=> __( 'New Review', 'rah-hosts' ),
		'all_items' 		=> __( 'All Reviews', 'rah-hosts' ),
		'view_item' 		=> __( 'View Review', 'rah-hosts' ),
		'search_items' 		=> __( 'Search Reviews', 'rah-hosts' ),
		'not_found' 		=> __( 'No reviews found', 'rah-hosts' ),
		'not_found_in_trash'=> __( 'No reviews found in Trash', 'rah-hosts' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Reviews', 'rah-hosts' )
	) );

	$reviews_args = array(
		'labels' 			=> $reviews_labels,
		'public' 			=> true,
		'publicly_queryable'=> true,
		'show_ui' 			=> true,
		'menu_position'		=> 20,
		'menu_icon' 		=> 'dashicons-star-filled',
		'show_in_menu' 		=> true,
		'query_var' 		=> true,
		'map_meta_cap'		=> true,
		'has_archive' 		=> true,
		'hierarchical' 		=> false,
		'supports' 			=> apply_filters( 'rah_reviews_supports', array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'reviews', apply_filters( 'rah_review_post_type_args', $reviews_args ) );

	$groups_labels =  apply_filters( 'rah_groups_labels', array(
		'name' 				=> 'Groups',
		'singular_name' 	=> 'Group',
		'add_new' 			=> __( 'Add Groups', 'rah-hosts' ),
		'add_new_item' 		=> __( 'Add Group', 'rah-hosts' ),
		'edit_item' 		=> __( 'Edit Group', 'rah-hosts' ),
		'new_item' 			=> __( 'New Group', 'rah-hosts' ),
		'all_items' 		=> __( 'All Groups', 'rah-hosts' ),
		'view_item' 		=> __( 'View Group', 'rah-hosts' ),
		'search_items' 		=> __( 'Search Groups', 'rah-hosts' ),
		'not_found' 		=> __( 'No groups found', 'rah-hosts' ),
		'not_found_in_trash'=> __( 'No groups found in Trash', 'rah-hosts' ),
		'parent_item_colon' => '',
		'menu_name' 		=> __( 'Groups', 'rah-hosts' )
	) );

	$groups_args = array(
		'labels' 			=> $groups_labels,
		'public' 			=> true,
		'publicly_queryable'=> true,
		'show_ui' 			=> true,
		'menu_position'		=> 20,
		'menu_icon' 		=> 'dashicons-groups',
		'show_in_menu' 		=> true,
		'query_var' 		=> true,
		'map_meta_cap'		=> true,
		'has_archive' 		=> true,
		'hierarchical' 		=> false,
		'supports' 			=> apply_filters( 'rah_groups_supports', array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'groups', apply_filters( 'rah_groups_post_type_args', $groups_args ) );
}

function rah_delete_host( $post_id ) {
	if ( 'hosts' !== get_post_type( $post_id ) ) {
		return;
	}

	global $wpdb;

	$results = $wpdb->get_results( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND meta_value = "' . $post_id . '"');

	delete_user_meta( $results[0]->user_id, '_user_host_id' );
}


function rah_register_meta_boxes() {
	global $post, $ppp_options;

	if ( $post->post_type !== 'reviews' ) {
		return;
	}

	add_meta_box( 'rah_review_metabox', 'Rating Information', 'rah_review_metabox_callback', 'reviews', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'rah_register_meta_boxes', 12 );

/**
 * Display the Metabox for Post Promoter Pro
 * @return void
 */
function rah_review_metabox_callback() {
	global $post;
	$star_ratings = get_post_meta( $post->ID, '_review_star_ratings', true );
	$xpost = get_post_meta( $post->ID, '_review_xpost', true );
	$reinvoices = get_post_meta( $post->ID, '_review_reinvoices', true );
	?>
	<p>For Cross Post:&nbsp;<?php echo $xpost; ?></p>
	<p>Re-invoices:&nbsp;<?php echo $reinvoices; ?></p>

	<?php
	foreach ( $star_ratings as $key => $rating ) {
		?><div class="rating-loop-wrapper"><?php
		$rating_title = ucwords( str_replace( array( '_', 'rating', 'and' ), array( ' ', '', '&' ), $key ) );
		?>
		<div class="review-shortname"><?php echo trim( $rating_title ); ?>:</div>
		<div class="review-stars"><?php echo rah_generate_stars( $rating ); ?></div>
		</div><?php
  	}
}

function rah_modify_query_order( $query ) {
	if ( !isset( $query->query['post_type'] ) ){
		return;
	}

	if ( $query->query['post_type'] !== 'hosts' && $query->query['post_type'] !== 'groups' ) {
		return;
	}


	if ( $query->is_archive() && $query->is_main_query() ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'rah_modify_query_order' );

