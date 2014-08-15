<?php

class WordCamp_Payments {
	const VERSION = '0.1.0';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_assets' ) );
		add_action( 'transition_post_status', array( $this, 'notify_central_of_new_requests' ), 10, 3 );
	}

	/**
	 * Enqueue scripts and stylesheets
	 */
	public function enqueue_assets() {
		wp_register_script(
			'wordcamp-payments',
			plugins_url( 'javascript/wordcamp-payments.js', __DIR__ ),
			array( 'jquery', 'jquery-ui-datepicker', 'media-upload', 'media-views' ),
			self::VERSION,
			true
		);

		// Can remove this when #18909-core is committed
		wp_register_style(
			'jquery-ui',
			plugins_url( 'css/jquery-ui.min.css', __DIR__ ),
			array(),
			'1.11.1'
		);

		// https://github.com/x-team/wp-jquery-ui-datepicker-skins
		wp_register_style(
			'wp-datepicker-skins',
			plugins_url( 'css/wp-datepicker-skins.css', __DIR__ ),
			array( 'jquery-ui' ),
			'1712f05a1c6a76ef0ac0b0a9bf79224e52e461ab'
		);

		wp_register_style(
			'wordcamp-payments',
			plugins_url( 'css/wordcamp-payments.css', __DIR__ ),
			array( 'wp-datepicker-skins' ),
			self::VERSION
		);


		// todo if on one of our screens
			wp_enqueue_script( 'wordcamp-payments' );
			wp_enqueue_style( 'wordcamp-payments' );

			// todo if on ticket edit screen
				wp_enqueue_media();
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

		if ( WCP_Payment_Request::POST_TYPE != $post->post_type ) {
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
