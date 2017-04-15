<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
WP_CLI::add_command( 'hrb', 'HRB_CLI_Tools' );

/**
 * Work with EDD through WP-CLI
 *
 * EDD_CLI Class
 *
 * Adds CLI support to EDD through WP-CL
 *
 * @since   1.0
 */
class HRB_CLI_Tools extends WP_CLI_Command {

	public function migrate_users( $args, $assoc_args ) {
		global $wpdb;

		$args = array(
			'number' => '-1',
			'fields' => 'all',
		);
		$users = get_users( $args );

		// Import the users.
		$progress = new \cli\progress\Bar( 'Migrating Users', count( $users ) );
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {

				$fb_id  = get_user_meta( $user->ID, 'social_connect_facebook_id', true );
				if ( empty( $fb_id ) ) {
					continue;
				}

				$already_migrated = rah_get_facebook_user_id( $user->ID );
				if ( ! empty( $already_migrated ) ) {
					continue;
				}

				$profile = new stdClass;
				$profile->identifier = $fb_id;
				wsl_store_hybridauth_user_profile( $user->ID, 'Facebook', $profile );
				$progress->tick();

			}

			$progress->finish();

		}
	}

	public function generate_avatars( $args, $assoc_args ) {
		global $wpdb;

		$args = array(
			'number' => '-1',
			'fields' => 'all',
		);
		$users = get_users( $args );

		// Import the users.
		$progress = new \cli\progress\Bar( 'Migrating Users', count( $users ) );
		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {

				$fb_id = rah_get_facebook_user_id( $user->ID );
				if ( empty( $fb_id ) ) {
					continue;
				}

				$image_url = 'https://graph.facebook.com/' . $fb_id . '/picture?width=150&height=150';
				$sql       = "UPDATE `{$wpdb->prefix}wslusersprofiles` SET photourl = %s WHERE user_id = %d";
				$wpdb->query( $wpdb->prepare( $sql, $image_url, $user->ID ) );

				update_user_meta( $user->ID, 'wsl_current_provider', 'Facebook' );
				update_user_meta( $user->ID, 'wsl_current_user_image', $image_url );

				$progress->tick();

			}

			$progress->finish();

		}
	}
}
}
