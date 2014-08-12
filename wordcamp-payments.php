<?php
/*
Plugin Name: WordCamp Payments
Plugin URI:  http://wordcamp.org/
Description: Provides tools for collecting and processing payment requests from WordCamp organizers.
Author:      tellyworth, iandunn
Version:     0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

class WordCamp_Payments {

	public function __construct() {
		$this->bootstrap();

		add_action( 'wp_enqueue_scripts',     array( $this, 'enqueue_assets' ) );
		add_action( 'transition_post_status', array( $this, 'notify_central_of_new_requests' ), 10, 3 );
	}

	protected function bootstrap() {
		require_once( __DIR__ . '/payment.php' );
		require_once( __DIR__ . '/network-admin-tools.php' );

		if ( is_admin() ) {
			$GLOBALS['wcp_payment'] = new WCP_Payment();
		}

		if ( is_network_admin() ) {
			$GLOBALS['wcp_network_admin_tools'] = new WCP_Network_Admin_Tools();
		}

		register_activation_hook( __FILE__, array( $GLOBALS['wcp_payment'], 'activate' ) );
	}

	public function enqueue_assets() {
		// todo enqueue js
	}

	/**
	 * Notify WordCamp Central that a new request has been made.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param WP_Post $post
	 */
	public function notify_central_of_new_requests( $new_status, $old_status, $post ) {
		/** @var WP_User $requester */

		if ( WCP_Payment::POST_TYPE != $post->post_type ) {
			return;
		}

		if ( 'publish' != $new_status || 'publish' == $old_status ) {
			return;
		}

		$requester = get_user_by( 'id', $post->post_author );
		$wordcamp  = $_POST['wordcamp'];

		$message = sprintf(
			"A new payment request has been made.

			WordCamp: %s
			Item: %s
			Due Date: %s
			Requester: %s

			View details: %s",

			is_a( $wordcamp, 'WP_Post' ) ? $wordcamp->post_title : '',
			$post->post_title,
			$_POST['due_by'],
			$requester->get( 'display_name' ),
			admin_url( 'post.php?post='. $post->ID .'&action=edit' )
		);
		$message = str_replace( "\t", '', $message );

		$headers = array(
			'Reply-To: ' . $requester->get( 'user_email' ),
		);

		wp_mail( 'support@wordcamp.org', 'New Payment Request: ' . $post->post_title, $message, $headers );
	}
}

if ( is_admin() && in_array( get_current_blog_id(), apply_filters( 'wcp_allowed_site_ids', array( 206 ) ) ) ) {     // testing.wordcamp.org
	// todo don't run on central
	$GLOBALS['wordcamp_payments'] = new WordCamp_Payments();
}
