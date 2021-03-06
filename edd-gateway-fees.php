<?php
/**
 * Plugin Name:         Easy Digital Downloads - Gateway Fees
 * Plugin URI:          https://easydigitaldownloads.com/downloads/gateway-fees/
 * Description:         Lets store owners charge fees for using gateways
 * Author:              Easy Digital Downloads
 * Author URI:          https://easydigitaldownloads.com
 *
 * Version:             1.5.2
 * Requires at least:   3.7
 * Tested up to:        4.9.2
 *
 * Text Domain:         edd_gf
 * Domain Path:         /edd_gf/languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2015 Easy Digital Downloads, LLC
 * @author              Easy Digital Downloads
 * @package             EDD GF
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
/** Check if Easy Digital Downloads is active */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
class EDD_GF {
	/**
	 * @var EDD_GF The one true EDD_GF
	 * @since 1.0
	 */
	private static $instance;
	public $id = 'edd_gf';
	public $basename;
	// Setup objects for each class
	public $discounts;
	/**
	 * Main edd_gf Instance
	 *
	 * Insures that only one instance of edd_gf exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @uses edd_gf::setup_globals() Setup the globals needed
	 * @uses edd_gf::includes() Include the required files
	 * @uses edd_gf::setup_actions() Setup the hooks and actions
	 * @see EDD()
	 * @return The one true edd_gf
	 */
	public static function instance() {
		if ( !isset( self::$instance ) && !( self::$instance instanceof EDD_GF ) ) {
			self::$instance = new EDD_GF;
			self::$instance->define_globals();
			self::$instance->includes();
		}
		return self::$instance;
	}
	public function define_globals() {
		$this->title    = __( 'Gateway Fees', 'edd_gf' );
		$this->file     = __FILE__;
		$this->basename = apply_filters( 'edd_edd_gf_plugin_basename', plugin_basename( $this->file ) );
		// Plugin Name
		if ( !defined( 'edd_gf_plugin_name' ) ) {
			define( 'edd_gf_plugin_name', 'Gateway Fees' );
		}
		// Plugin Version
		if ( !defined( 'edd_gf_plugin_version' ) ) {
			define( 'edd_gf_plugin_version', '1.5.2' );
		}
		// Plugin Root File
		if ( !defined( 'edd_gf_plugin_file' ) ) {
			define( 'edd_gf_plugin_file', __FILE__ );
		}
		// Plugin Folder Path
		if ( !defined( 'edd_gf_plugin_dir' ) ) {
			define( 'edd_gf_plugin_dir', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) . '/' );
		}
		// Plugin Folder URL
		if ( !defined( 'edd_gf_plugin_url' ) ) {
			define( 'edd_gf_plugin_url', plugin_dir_url( edd_gf_plugin_file ) );
		}
		// Plugin Assets URL
		if ( !defined( 'edd_gf_assets_url' ) ) {
			define( 'edd_gf_assets_url', edd_gf_plugin_url . 'assets/' );
		}
		if ( !class_exists( 'EDD_License' ) ) {
			require_once edd_gf_plugin_dir . 'assets/lib/EDD_License_Handler.php';
		}
		$license = new EDD_License( __FILE__, edd_gf_plugin_name, edd_gf_plugin_version, 'EDD Team' );
	}
	public function includes() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		require_once edd_gf_plugin_dir . 'classes/class-setup.php';
		require_once edd_gf_plugin_dir . 'classes/class-settings.php';
		require_once edd_gf_plugin_dir . 'classes/class-frontend.php';
	}

	public function load_textdomain() {
		$locale        = apply_filters( 'plugin_locale', get_locale(), 'edd_gf' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'edd_gf', $locale );

		$mofile_local  = trailingslashit( edd_gf_plugin_dir . 'languages' ) . $mofile;
		$mofile_global = WP_LANG_DIR . '/edd_gf/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( 'edd_gf', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( 'edd_gf', $mofile_local );
		}
		else{
			load_plugin_textdomain( 'edd_gf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}
}
/**
 * The main function responsible for returning the one true EDD_GF
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $EDD_GF = EDD_GF(); ?>
 *
 * @since 2.0
 * @return object The one true EDD_GF Instance
 */
function EDD_GF() {
	return EDD_GF::instance();
}
if ( class_exists( 'Easy_Digital_Downloads' ) ){
	EDD_GF();
}
