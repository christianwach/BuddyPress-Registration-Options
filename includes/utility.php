<?php
/**
 * BP-Registration-Options utility functions.
 *
 * @package BP_Registration_Options
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checks if a User is pending.
 *
 * @param int $user_id The User ID.
 * @return bool $pending True if the User is pending, false otherwise.
 */
function bp_registration_is_current_user_pending( $user_id = 0 ) {

	$pending = false;

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$pending_users    = bp_registration_get_pending_users();
	$pending_user_ids = wp_list_pluck( $pending_users, 'user_id' );

	if ( in_array( $user_id, $pending_user_ids ) ) {
		$pending = true;
	}

	return $pending;

}

/**
 * Checks if registration is moderated.
 *
 * @return bool True if registration is moderated, false otherwise.
 */
function bp_registration_is_moderated() {

	$moderate = get_option( 'bprwg_moderate' );

	if ( empty( $moderate ) || ! $moderate ) {
		return false;
	}

	return true;

}

/**
 * Checks if the network is private.
 *
 * @return bool True if the network is private, false otherwise.
 */
function bp_registration_is_private_network() {

	$private_network = get_option( 'bprwg_privacy_network' );

	if ( empty( $private_network ) || ! $private_network ) {
		return false;
	}

	return true;

}

/**
 * Queries for all existing approved members that still have an IP address saved as user meta.
 *
 * Helper method to help clear up saved personal data for GDPR compliance.
 *
 * @since 4.3.5
 *
 * @return WP_User_Query User query.
 */
function bp_registration_get_user_ip_query() {

	$args = array(
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query' => array(
			array(
				'key'     => '_bprwg_ip_address',
				'compare' => 'exists',
			),
			array(
				'key'   => '_bprwg_is_moderated',
				'value' => 'false',
			),
		),
		'fields'     => 'ID',
	);

	return new WP_User_Query( $args );

}

/**
 * Checks whether or not we have existing users with saved IP addresses.
 *
 * @since 4.3.5
 *
 * @return bool
 */
function bp_registration_has_users_with_ips() {

	$users = bp_registration_get_user_ip_query();

	return ( $users->get_total() > 0 );

}

/**
 * Iterates over results of found users with IP addresses saved, and removes meta key.
 *
 * @since 4.3.5
 */
function bp_registration_delete_ip_addresses() {

	$users = bp_registration_get_user_ip_query();

	if ( $users->get_total() > 0 ) {
		foreach ( $users->get_results() as $user_id ) {
			delete_user_meta( $user_id, '_bprwg_ip_address' );
		}
	}

}

/**
 * Processes an email message.
 *
 * @param string $message The message.
 * @param bool   $do_process True if processing should happen.
 * @param string $message_type The type of message.
 * @param int    $moderated_user_id The ID of the moderated User.
 * @return string $message The modified message.
 */
function bp_registration_process_email_message( $message, $do_process, $message_type = '', $moderated_user_id = 0 ) {

	if ( false === apply_filters( 'bprwg_do_wpautop', $do_process, $message_type, $moderated_user_id ) ) {
		return $message;
	}

	return wpautop( $message );

}
