<?php
/**
 * Registration Options for BuddyPress
 *
 * Plugin Name:       Registration Options for BuddyPress
 * Description:       Prevent users and bots from accessing the BuddyPress or bbPress areas of your website(s) until they are approved.
 * Plugin URI:        https://github.com/christianwach/bp-registration-options
 * GitHub Plugin URI: https://github.com/christianwach/bp-registration-options
 * Version:           4.5.0a
 * Author:            Brian Messenlehner
 * Author URI:        https://apppresser.com/brian-messenlehner/
 * Licence:           GPLv3
 * Licence URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Text Domain:       bp-registration-options
 * Domain Path:       /languages
 *
 * @package BP-Registration-Options
 */

define( 'BP_REGISTRATION_OPTIONS_VERSION', '4.5.0a' );

/**
 * Loads BP Registration Options files only if BuddyPress is present.
 */
function bp_registration_options_init() {

	$bp  = '';
	$bbp = '';

	// Not using bp_includes because we want to be able to be run with just bbPress as well.
	if ( function_exists( 'buddypress' ) ) {
		$bp = buddypress();
	}

	if ( function_exists( 'bbpress' ) ) {
		$bbp = bbpress();
	}

	if ( bp_registration_should_init( $bp, $bbp ) ) {
		require_once dirname( __FILE__ ) . '/bp-registration-options.php';
		$bp_registration_options = new BP_Registration_Options();

		add_action( 'init', 'bp_registration_options_compat_init' );
	}
}
add_action( 'plugins_loaded', 'bp_registration_options_init' );

/**
 * Loads the BP Registration Options Compatibility features.
 *
 * @since 4.2.8
 */
function bp_registration_options_compat_init() {
	return new BP_Registration_Compatibility();
}

/**
 * Checks if we should init our settings and code.
 *
 * @since 4.2.8
 * @since 4.4.0 Added BuddyBoss checking.
 *
 * @param object|string $bp  BuddyPress instance, if available.
 * @param object|string $bbp bbPress instance, if available.
 *
 * @return bool
 */
function bp_registration_should_init( $bp = '', $bbp = '' ) {

	$should_init = ( is_object( $bp ) && version_compare( $bp->version, '2.5.0', '>=' ) ) ||
		( is_object( $bbp ) && version_compare( $bbp->version, '2.0.0', '>=' ) );

	if ( defined( 'BP_PLATFORM_VERSION' ) ) {
		$should_init = version_compare( BP_PLATFORM_VERSION, '1.3.5', '>=' );
	}

	$should_init = (bool) apply_filters( 'bprwg_should_init', $should_init );

	return $should_init;
}
