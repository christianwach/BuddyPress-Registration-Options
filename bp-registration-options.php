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
 * @package BP_Registration_Options
 */

define( 'BP_REGISTRATION_OPTIONS_VERSION', '4.5.0a' );

/**
 * Plugin class.
 */
class BP_Registration_Options {

	/**
	 * Current version.
	 *
	 * @since unknown
	 * @var string
	 */
	public $version = '';

	/**
	 * Plugin basename.
	 *
	 * @since unknown
	 * @var string
	 */
	public $basename = '';

	/**
	 * Plugin directory server path.
	 *
	 * @since unknown
	 * @var string
	 */
	public $directory_path = '';

	/**
	 * Constructor.
	 *
	 * @since unknown
	 */
	public function __construct() {

		// Define plugin constants.
		$this->version        = BP_REGISTRATION_OPTIONS_VERSION;
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );

		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		require_once $this->directory_path . 'includes/utility.php';
		require_once $this->directory_path . 'includes/admin.php';
		require_once $this->directory_path . 'includes/core.php';
		require_once $this->directory_path . 'includes/compatibility.php';

		add_action( 'init', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Activation hook for the plugin.
	 *
	 * @since unknown
	 */
	public function activate() {
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook for the plugin.
	 *
	 * @since unknown
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Load our textdomain
	 *
	 * @since unknown
	 */
	public function load_textdomain() {

		load_plugin_textdomain(
			'bp-registration-options',
			false,
			basename( dirname( __FILE__ ) ) . '/languages/'
		);

	}
}

/**
 * Loads BP Registration Options files only if BuddyPress is present.
 *
 * @since unknown
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
