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

// todo don't run on central
if ( is_admin() && in_array( get_current_blog_id(), apply_filters( 'wcp_allowed_site_ids', array( 206 ) ) ) ) {     // testing.wordcamp.org
	require_once( __DIR__ . '/classes/wordcamp-payments.php' );
	require_once( __DIR__ . '/classes/payment-request.php' );
	require_once( __DIR__ . '/classes/network-admin-tools.php' );

	$GLOBALS['wordcamp_payments']   = new WordCamp_Payments();
	$GLOBALS['wcp_payment_request'] = new WCP_Payment_Request();

	if ( is_network_admin() ) {
		$GLOBALS['wcp_network_admin_tools'] = new WCP_Network_Admin_Tools();
	}

	register_activation_hook( __FILE__, array( $GLOBALS['wcp_payment_request'], 'activate' ) );
}
