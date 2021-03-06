<?php
function rah_host_widget() {
	register_widget('RAH_Host_Widget');
	register_widget('RAH_User_Widget');
	register_widget('RAH_Stats_Widget');
}

class RAH_Host_Widget extends WP_Widget {

	function RAH_Host_Widget() {
		parent::__construct( false, 'Host Widget' );
	}

	function widget( $args, $instance ) {
		global $post;

		if ( ! is_object( $post ) || $post->post_type !== 'hosts' || ! is_single() ) {
			return;
		}

		$parent = get_post_ancestors( $post );

		if ( $parent ) {
			$group_url   = get_permalink( $parent[0] );
			$group_name  = get_the_title( $parent[0] );
			$group_image = get_post_meta( $parent[0], '_rah_group_fb_icon', true );
		}

		$group_types     = get_the_terms( $post->ID, 'type' );
		$types           = implode( ', ', wp_list_pluck( $group_types, 'name' ) );
		$review_count    = get_post_meta( $post->ID, '_host_review_count', true );
		$host_since      = get_post_meta( $post->ID, '_user_host_since', true );

		$host_location   = get_post_meta( $post->ID, '_user_postal_code', true );
		$location_string = '';

		if ( ! empty( $host_location ) ) {
			$city  = rah_get_postal_city( $host_location );
			$state = rah_get_postal_state( $host_location );

			if ( ! empty( $city ) ) {
				$location_string .= $city . ', ';
			}

			$location_string .= $state;
		}
		?>
		<div class="host-widet-wraper">

			<a href="<?php echo get_permalink( $post->ID ); ?>">
				<div class="review-avatar">
					<?php echo get_avatar( get_user_id_from_host_id( $post->ID ) ); ?>
				</div>
			</a>

			<header class="entry-header">
				<?php if( get_the_time( get_option( 'date_format' ) ) ) { ?>
					<h1 class="entry-title">
						<a href="<?php echo get_permalink( $post->ID ); ?>">
							<?php the_title();?>
						</a>
					</h1>

					<!-- .entry-title -->
					<div class="widget-host-type">
						<span class="dashicons dashicons-cart"></span><?php echo $types; ?>
					</div>

					<?php if ( ! empty( $location_string ) ) : ?>
						<div class="widget-host-location"><span class="dashicons dashicons-location-alt"></span><?php echo $location_string; ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $host_since ) ) : ?>
					<div class="entry-meta clearfix">
						<?php $host_since = str_replace( '/', '/1/', $host_since ); // Needed as strtotime doesn't know what to do with month/year ?>
						<div class="">Host Since:&nbsp;&nbsp;<?php echo date( 'F Y', strtotime( $host_since ) ); ?></div>
					</div>
					<?php endif; ?>

					<div class="entry-meta clearfix">
						<div class="">Joined On:&nbsp;&nbsp;<?php the_time( get_option( 'date_format' ) ); ?></div>
						<?php if (isset( $group_name ) ) : ?>
							<div class="group"><?php if( $group_image ) :?><img src="<?php echo $group_image; ?>" /> &nbsp;<?php endif; ?>
								<a href="<?php echo $group_url; ?>" title-"<?php echo esc_attr( $group_name ); ?>">
									<?php echo $group_name; ?>
								</a>
							</div>
						<?php endif; ?>
					</div>

					<div class="widget-ratings-wrapper">
						<?php echo rah_generate_stars( get_post_meta( $post->ID, '_host_rating', true ) ); ?><br />
						<?php printf( _n( '%d Review', '%d Reviews', $review_count, 'interface' ), $review_count ); ?>
					</div>

					<?php $host_buys = wp_get_post_terms( $post->ID, 'buys' ); ?>
					<?php if ( ! empty( $host_buys ) ) : ?>
					<div class="widget-buys">
						<p><strong>Runs Buys For</strong></p>
						<?php foreach ( $host_buys as $buy ) : ?>
							<?php $image = RAH_URL . 'assets/images/' . $buy->slug . '-logo.jpg'; ?>
							<img class="host-buy-logo" src="<?php echo $image; ?>" />
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<?php if ( !user_is_host( $post->ID ) ) :?>
						<div class="widget-review-button">
							<?php if ( is_user_logged_in() && has_user_reviewed_host( $post->ID ) ) : ?>
								<a href="<?php echo get_permalink( $post->ID ); ?>edit"><input type="button" value="Update Your Review" /></a>
							<?php else: ?>
								<a href="<?php echo get_permalink( $post->ID ); ?>new"><input type="button" value="Review This Host" /></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php } ?>
			</header>
		</div>
		<?php
	}
}

class RAH_User_Widget extends WP_Widget {

	function RAH_User_Widget() {
		parent::__construct( false, 'User Widget' );
	}

	function widget( $args, $instance ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id   = get_current_user_id();
		$user_info = get_userdata( $user_id );
		?>
		<div class="user-widet-wraper">
			<div class="user-avatar"><?php echo get_avatar( $user_id, 50 ); ?></div>
			<strong>Logged In As:</strong> <?php echo $user_info->user_firstname; ?>
			<ul>
				<?php if( current_user_can( 'edit_posts' ) ) :?>
					<li><a href="/wp-admin/">Admin Dashboard</a></li>
				<?php endif; ?>

				<?php if( rah_is_registered_host() ) :?>
					<li><a href="/host-dashboard">Host Dashboard</a></li>
				<?php endif; ?>

				<?php if( rah_is_registered_host() ) :?>
					<?php $host_id   = get_host_id_from_user_id( $user_id ); ?>
					<li><a href="<?php echo get_permalink( $host_id ); ?>">View Profile</a></li>
				<?php endif; ?>

				<li><a href="<?php echo wp_logout_url( get_bloginfo( 'url' ) ); ?>">Log Out</a></li>
			</ul>
		</div>
		<?php
	}
}

class RAH_Stats_Widget extends WP_Widget {

	function RAH_Stats_Widget() {
		parent::__construct( false, 'Stats Widget' );
	}

	function widget( $args, $instance ) {
		$hosts   = wp_count_posts( 'hosts' );
		$groups  = wp_count_postS( 'groups' );
		$reviews = wp_count_posts( 'reviews' );
		?>
		<div class="stats-widet-wrapper">
			<h1 class="widget-title">Current Site Stats</h1>
			<ul>
				<li><strong>Groups:</strong> <?php echo $groups->publish; ?></li>
				<li><strong>Hosts:</strong> <?php echo $hosts->publish; ?></li>
				<li><strong>Reviews:</strong> <?php echo $reviews->publish; ?></li>
			</ul>
		</div>
		<?php
	}
}
