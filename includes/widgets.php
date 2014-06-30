<?php
function rah_host_widget() {
  register_widget('RAH_Host_Widget');
}

class RAH_Host_Widget extends WP_Widget
{

  function RAH_Host_Widget() {
    parent::__construct(false, 'Host Widget');
  }

  function widget( $args, $instance ) {
    global $post;

    if ( ! is_object( $post ) || $post->post_type !== 'hosts' || ! is_single() ) {
      return;
    }

    $parent = get_post_ancestors( $post );
    if ( $parent ) {
        $group_url = get_permalink( $parent[0] );
        $group_name = get_the_title( $parent[0] );
        $group_image = get_post_meta( $parent[0], '_rah_group_fb_icon', true );
    }
    $group_types = get_the_terms( $post->ID, 'type' );
    $types = implode( ', ', wp_list_pluck( $group_types, 'name' ) );
    $review_count = get_post_meta( $post->ID, '_host_review_count', true );
    ?>
    <div class="host-widet-wraper">
        <a href="<?php echo get_permalink( $post->ID ); ?>">
          <div class="review-avatar">
            <?php echo get_avatar( get_user_id_from_host_id( $post->ID ) ); ?>
          </div>
        </a>
        <header class="entry-header">
          <?php if(get_the_time( get_option( 'date_format' ) )) { ?>
          <div class="entry-meta"> <span class="cat-links">
            <?php the_category(', '); ?>
            </span><!-- .cat-links -->
          </div>
          <!-- .entry-meta -->

          <h1 class="entry-title"><a href="<?php echo get_permalink( $post->ID ); ?>">
            <?php the_title();?>
          </a></h1>
          <!-- .entry-title -->
          <div class="widget-host-type"><span class="dashicons dashicons-cart"></span><?php echo $types; ?></div>
          <div class="entry-meta clearfix">
            <div class="date"><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_time() ); ?>">
              Joined On: <?php the_time( get_option( 'date_format' ) ); ?>
              </a></div>
              <?php if (isset( $group_name ) ) : ?>
            <div class="group"><img src="<?php echo $group_image; ?>" />
              <a href="<?php echo $group_url; ?>" title-"<?php echo esc_attr( $group_name ); ?>">
              <?php echo $group_name; ?>
            </a></div>
        <?php endif; ?>
          </div>
          <div class="widget-ratings-wrapper">
            <?php echo rah_generate_stars( get_post_meta( $post->ID, '_host_rating', true ) ); ?><br />
            <?php printf( _n( '%d Review', '%d Reviews', $review_count, 'interface' ), $review_count ); ?>
          </div>
          <?php if ( !user_is_host( $post->ID ) ) :?>
          <div class="widget-review-button">
            <?php if ( has_user_reviewed_host( $post->ID ) ) : ?>
                <a href="<?php echo get_permalink( $post->ID ); ?>edit"><input type="button" value="Update Your Review" /></a>
            <?php else: ?>
                <a href="<?php echo get_permalink( $post->ID ); ?>new"><input type="button" value="Review This Host" /></a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <!-- .entry-meta -->
        </header>
        <!-- .entry-header -->
        <?php } ?>
      </header>
    </div>
    <?php
  }
}