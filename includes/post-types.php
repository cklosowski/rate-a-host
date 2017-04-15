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
			'name'               => 'Hosts',
			'singular_name'      => 'Host',
			'add_new'            => __( 'Add Host', 'rah-hosts' ),
			'add_new_item'       => __( 'Add Host', 'rah-hosts' ),
			'edit_item'          => __( 'Edit Host', 'rah-hosts' ),
			'new_item'           => __( 'New Host', 'rah-hosts' ),
			'all_items'          => __( 'All Hosts', 'rah-hosts' ),
			'view_item'          => __( 'View Host', 'rah-hosts' ),
			'search_items'       => __( 'Search Hosts', 'rah-hosts' ),
			'not_found'          => __( 'No hosts found', 'rah-hosts' ),
			'not_found_in_trash' => __( 'No hosts found in Trash', 'rah-hosts' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Hosts', 'rah-hosts' )
		) );

	$hosts_args = array(
		'labels'             => $hosts_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-businessman',
		'show_in_menu'       => true,
		'query_var'          => true,
		'map_meta_cap'       => true,
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'rah_hosts_supports', array( 'title', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'hosts', apply_filters( 'rah_host_post_type_args', $hosts_args  ) );

	register_taxonomy(
		'type',
		'hosts',
		array(
			'label'        => __( 'Host Types' ),
			'rewrite'      => array( 'slug' => 'type' ),
			'hierarchical' => true
		)
	);

	register_taxonomy(
		'buys',
		'hosts',
		array(
			'label'        => __( 'Buys' ),
			'rewrite'      => array( 'slug' => 'buys' ),
			'hierarchical' => false,
		)
	);

	$reviews_labels =  apply_filters( 'rah_reviews_labels', array(
			'name'               => 'Reviews',
			'singular_name'      => 'Review',
			'add_new'            => __( 'Add Review', 'rah-hosts' ),
			'add_new_item'       => __( 'Add Review', 'rah-hosts' ),
			'edit_item'          => __( 'Edit Review', 'rah-hosts' ),
			'new_item'           => __( 'New Review', 'rah-hosts' ),
			'all_items'          => __( 'All Reviews', 'rah-hosts' ),
			'view_item'          => __( 'View Review', 'rah-hosts' ),
			'search_items'       => __( 'Search Reviews', 'rah-hosts' ),
			'not_found'          => __( 'No reviews found', 'rah-hosts' ),
			'not_found_in_trash' => __( 'No reviews found in Trash', 'rah-hosts' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Reviews', 'rah-hosts' )
		) );

	$reviews_args = array(
		'labels'              => $reviews_labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-star-filled',
		'show_in_menu'        => true,
		'query_var'           => true,
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'hierarchical'        => false,
		'exclude_from_search' => true,
		'supports'            => apply_filters( 'rah_reviews_supports', array( 'comments', 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'reviews', apply_filters( 'rah_review_post_type_args', $reviews_args ) );

	$groups_labels =  apply_filters( 'rah_groups_labels', array(
			'name'               => 'Groups',
			'singular_name'      => 'Group',
			'add_new'            => __( 'Add Groups', 'rah-hosts' ),
			'add_new_item'       => __( 'Add Group', 'rah-hosts' ),
			'edit_item'          => __( 'Edit Group', 'rah-hosts' ),
			'new_item'           => __( 'New Group', 'rah-hosts' ),
			'all_items'          => __( 'All Groups', 'rah-hosts' ),
			'view_item'          => __( 'View Group', 'rah-hosts' ),
			'search_items'       => __( 'Search Groups', 'rah-hosts' ),
			'not_found'          => __( 'No groups found', 'rah-hosts' ),
			'not_found_in_trash' => __( 'No groups found in Trash', 'rah-hosts' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Groups', 'rah-hosts' )
		) );

	$groups_args = array(
		'labels'             => $groups_labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-groups',
		'show_in_menu'       => true,
		'query_var'          => true,
		'map_meta_cap'       => true,
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => apply_filters( 'rah_groups_supports', array( 'title', 'editor', 'thumbnail', 'author', 'revisions', 'custom-fields' ) ),
	);
	register_post_type( 'groups', apply_filters( 'rah_groups_post_type_args', $groups_args ) );
}

function rah_delete_host( $post_id ) {
	if ( 'hosts' !== get_post_type( $post_id ) ) {
		return;
	}

	global $wpdb;

	$results = $wpdb->get_results( 'SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_key = "_user_host_id" AND meta_value = "' . $post_id . '"' );

	delete_user_meta( $results[0]->user_id, '_user_host_id' );
}


function rah_register_meta_boxes() {
	global $post, $ppp_options;

	add_meta_box( 'rah_review_metabox', 'Rating Information', 'rah_review_metabox_callback', 'reviews', 'normal', 'high' );
	add_meta_box( 'rah_host_metabox', 'Host Information', 'rah_host_metabox_callback', 'hosts', 'normal', 'high' );

}
add_action( 'add_meta_boxes', 'rah_register_meta_boxes', 12 );

/**
 * Display the Metabox for Post Promoter Pro
 *
 * @return void
 */
function rah_review_metabox_callback() {
	global $post;
	$star_ratings = get_post_meta( $post->ID, '_review_star_ratings', true );
	$xpost = get_post_meta( $post->ID, '_review_xpost', true );
	$reinvoices = get_post_meta( $post->ID, '_review_reinvoices', true );
	$issues_na = get_post_meta( $post->ID, '_review_issues_na', true );
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

function rah_host_metabox_callback() {
	global $post;

	$postal_code = get_post_meta( $post->ID, '_user_postal_code', true );
	$host_since  = get_post_meta( $post->ID, '_user_host_since', true );

	$user_id     = get_user_id_from_host_id( $post->ID );
	$host_fb_id  = rah_get_facebook_user_id( $user_id );
	$fb_user_link  = 'https://facebook.com/' . $host_fb_id;
?>
	<p>Postal Code:&nbsp;<?php echo ! empty( $postal_code ) ? $postal_code : 'Not Provided'; ?></p>
	<p>Host Since:&nbsp;<?php echo ! empty( $host_since ) ? $host_since : 'Not Provided'; ?></p>
	<p>Host Link:&nbsp;<a href="<?php echo $fb_user_link; ?>" target="_blank">View Host on Facebook</a></p>
	<p>Group Page:&nbsp;<a href="<?php echo admin_url( 'post.php?action=edit&post=' . $post->post_parent ); ?>">View Group</a></p>
<?php
}

function rah_modify_query_order( $query ) {
	if ( !isset( $query->query['post_type'] ) ) {
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

function rah_custom_right_now_icons() {
?>
<style>
#dashboard_right_now a.reviews-count:before,
#dashboard_right_now span.reviews-count:before {
  content: "\f155";
}
#dashboard_right_now a.hosts-count:before,
#dashboard_right_now span.hosts-count:before {
  content: "\f338";
}

#dashboard_right_now a.groups-count:before,
#dashboard_right_now span.groups-count:before {
  content: "\f307";
}

</style>
<?php
}

function rah_glance_items( $items = array() ) {

	$post_types = array( 'reviews', 'hosts', 'groups' );

	foreach ( $post_types as $type ) {

		if ( ! post_type_exists( $type ) ) continue;

		$num_posts = wp_count_posts( $type );

		if ( $num_posts ) {

			$published = intval( $num_posts->publish );
			$post_type = get_post_type_object( $type );

			$text = _n( '%s ' . $post_type->labels->singular_name, '%s ' . $post_type->labels->name, $published, 'rah-txt' );
			$text = sprintf( $text, number_format_i18n( $published ) );

			if ( current_user_can( $post_type->cap->edit_posts ) ) {
				$items[] = sprintf( '<a class="%1$s-count" href="edit.php?post_type=%1$s">%2$s</a>', $type, $text ) . "\n";
			} else {
				$items[] = sprintf( '<span class="%1$s-count">%2$s</span>', $type, $text ) . "\n";
			}
		}
	}

	return $items;
}
add_filter( 'dashboard_glance_items', 'rah_glance_items', 10, 1 );

function rah_declined_post_status() {
	register_post_status( 'declined', array(
			'label'                     => _x( 'Declined', 'reviews' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Declined <span class="count">(%s)</span>', 'Declined <span class="count">(%s)</span>' ),
		) );

	register_post_status( 'declined', array(
			'label'                     => _x( 'Declined', 'hosts' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Declined <span class="count">(%s)</span>', 'Declined <span class="count">(%s)</span>' ),
		) );
}
add_action( 'init', 'rah_declined_post_status' );


function rah_inactive_host_status() {
	register_post_status( 'inactive', array(
			'label'                     => _x( 'Inactive', 'hosts' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>' ),
		) );
}
add_action( 'init', 'rah_inactive_host_status' );

function rah_append_post_status_list_declined() {
	global $post;
	if ( $post->post_type !== 'reviews' && $post->post_type !== 'hosts' ) {
		return;
	}

	$complete = '';
	$label    = '';
	if ( $post->post_status === 'declined' ) {
		$complete = ' selected="selected"';
		$label = '<span id="post-status-display">Declined</span>';
		echo '<script>jQuery(document).ready(function($){ $(\'.misc-pub-section label\').append(\'' . $label . '\'); });</script>';
	}

	echo '
	<script>
		jQuery(document).ready(function($){
			$(\'select#post_status\').append(\'<option value="declined" ' . $complete . '>Declined</option>\');
		});
	</script>
	';
}
add_action( 'admin_footer-post.php', 'rah_append_post_status_list_declined' );

function rah_add_declined_reason_box() {
	global $post;
	if ( $post->post_type !== 'reviews' && $post->post_type !== 'hosts' ) {
		return;
	}

	$declined_reason = get_post_meta( $post->ID, '_review_declined_reason', true );
	$reason_text = !empty( $declined_reason ) ? $declined_reason : '';
	$show = $post->post_status === 'declined' ? '' : ' hidden';
?>
	<div class="misc-pub-section rah-declined-reason<?php echo $show; ?>" id="rah-decline-reason">
		<span style="color: #888;" class="dashicons dashicons-flag"></span>&nbsp;Decline Reason
		<textarea style="width: 100%" id="rah_declined_reason" name="_review_declined_reason"><?php echo $reason_text; ?></textarea>

	</div>
	<?php
}
add_action( 'post_submitbox_misc_actions', 'rah_add_declined_reason_box' );

function rah_append_post_status_list_inactive() {
	global $post;

	if ( $post->post_type !== 'hosts' ) {
		return;
	}

	$inactive = '';
	$label    = '';
	if ( $post->post_status === 'inactive' ) {
		$inactive = ' selected="selected"';
		$label    = '<span id="post-status-display">Inactive</span>';
		echo '<script>jQuery(document).ready(function($){ $(\'.misc-pub-section label\').append(\'' . $label . '\'); });</script>';
	}

	echo '
	<script>
		jQuery(document).ready(function($){
			$(\'select#post_status\').append(\'<option value="inactive" ' . $inactive . '>Inactive</option>\');
		});
	</script>
	';
}
add_action( 'admin_footer-post.php', 'rah_append_post_status_list_inactive' );


function rah_save_meta_boxes( $post_id ) {
	$post = get_post( $post_id );
	if ( $post->post_type !== 'reviews' && $post->post_type !== 'hosts' ) {
		return;
	}

	$post_declined = $post->post_status === 'declined' ? true : false;

	if ( $post_declined ) {
		$declined_text = isset( $_POST['_review_declined_reason'] ) ? sanitize_text_field( $_POST['_review_declined_reason'] ) : '';
		update_post_meta( $post->ID, '_review_declined_reason', $declined_text );
	}


}
add_action( 'save_post', 'rah_save_meta_boxes', 10, 1 );
