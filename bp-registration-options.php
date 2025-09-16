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

// Exit if accessed directly.
defined('ABSPATH') || exit;

// Set our version here.
define( 'BP_REGISTRATION_OPTIONS_VERSION', '4.5.0a' );

// Store reference to this file.
if ( ! defined( 'BP_REGISTRATION_OPTIONS_FILE' ) ) {
	define( 'BP_REGISTRATION_OPTIONS_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'BP_REGISTRATION_OPTIONS_URL' ) ) {
	define( 'BP_REGISTRATION_OPTIONS_URL', plugin_dir_url( BP_REGISTRATION_OPTIONS_FILE ) );
}

// Store PATH to this plugin's directory.
if ( ! defined( 'BP_REGISTRATION_OPTIONS_PATH' ) ) {
	define( 'BP_REGISTRATION_OPTIONS_PATH', plugin_dir_path( BP_REGISTRATION_OPTIONS_FILE ) );
}

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

		// Bail if dependencies not met.
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Initialise this plugin.
		$this->initialise();

	}

	/**
	 * Initialise this plugin.
	 *
	 * @since 4.5.0
	 */
	private function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap plugin.
		$this->include_files();
		$this->register_hooks();

		/**
		 * Broadcast that this plugin is active.
		 *
		 * @since 4.5.0
		 */
		do_action( 'bp_registration_options_loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Include files.
	 *
	 * @since 4.5.0
	 */
	private function include_files() {

		require BP_REGISTRATION_OPTIONS_PATH . 'includes/utility.php';
		require BP_REGISTRATION_OPTIONS_PATH . 'includes/admin.php';
		require BP_REGISTRATION_OPTIONS_PATH . 'includes/core.php';
		require BP_REGISTRATION_OPTIONS_PATH . 'includes/compatibility.php';

	}

	/**
	 * Registers hook callbacks.
	 *
	 * @since 4.5.0
	 */
	private function register_hooks() {

		register_activation_hook( __FILE__, array( &$this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', 'bp_registration_options_compat_init' );

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

	/**
	 * Checks plugin dependencies.
	 *
	 * @since 4.5.0
	 *
	 * @return bool
	 */
	private function check_dependencies() {

		// Bail if neither exists.
		if ( ! function_exists( 'buddypress' ) && ! function_exists( 'bbpress' ) ) {
			return false;
		}

		// Maybe get BuddyPress instance.
		$bp  = '';
		if ( function_exists( 'buddypress' ) ) {
			$bp = buddypress();
		}

		// Maybe get bbPress instance.
		$bbp = '';
		if ( function_exists( 'bbpress' ) ) {
			$bbp = bbpress();
		}

		// Check versions.
		if ( $this->should_init( $bp, $bbp ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Checks if we should init our settings and code.
	 *
	 * @since 4.2.8
	 * @since 4.4.0 Added BuddyBoss checking.
	 *
	 * @param object|string $bp  BuddyPress instance, if available.
	 * @param object|string $bbp bbPress instance, if available.
	 * @return bool
	 */
	private function should_init( $bp = '', $bbp = '' ) {

		$should_init = ( is_object( $bp ) && version_compare( $bp->version, '2.5.0', '>=' ) ) ||
			( is_object( $bbp ) && version_compare( $bbp->version, '2.0.0', '>=' ) );

		if ( defined( 'BP_PLATFORM_VERSION' ) ) {
			$should_init = version_compare( BP_PLATFORM_VERSION, '1.3.5', '>=' );
		}

		/**
		 * Filter the init flag.
		 *
		 * @since 4.2.8
		 *
		 * @param bool $should_init True if plugin initialisation should proceed.
		 */
		$should_init = (bool) apply_filters( 'bprwg_should_init', $should_init );

		return $should_init;

	}

	/**
	 * Loads the BP Registration Options Compatibility features.
	 *
	 * @since 4.2.8
	 *
	 * @return BP_Registration_Compatibility The compatibility object.
	 */
	public function compat_init() {
		return new BP_Registration_Compatibility();
	}

}

/**
 * Loads plugin if not yet loaded and return reference.
 *
 * @since 4.5.0
 *
 * @return BP_Registration_Options $plugin The plugin reference.
 */
function bp_registration_options() {

	// Instantiate plugin if not yet instantiated.
	static $plugin;
	if ( ! isset( $plugin ) ) {
		$plugin = new BP_Registration_Options();
	}

	// --<
	return $plugin;

}

add_action( 'plugins_loaded', 'bp_registration_options' );

/**
 * Loads the BP Registration Options Compatibility features.
 *
 * @since 4.2.8
 *
 * @return BP_Registration_Compatibility The compatibility object.
 */
function bp_registration_options_compat_init() {
	return bp_registration_options()->compat_init();
}

