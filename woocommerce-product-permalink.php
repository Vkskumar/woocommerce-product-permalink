<?php
/*
Plugin Name: WooCommerce: Product Permalinks
Plugin URI: http://codearachnid.github.io/woocommerce-product-permalink/
Description: An addon for WooCommerce to create customized product permalinks.
Version: 1.0
Author: Timothy Wood @codearachnid
Author URI: http://www.imaginesimplicity.com
Text Domain: woocommerce-product-permalink
Domain Path: /lang/
License: GPLv3

WooCommerce: Product Permalinks
Copyright (C) 2014, Imagine Simplicity LLC, All Rights Reserved.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Don't load directly
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );

if( !class_exists('WC_Product_Permalink_Factory') ){
	class WC_Product_Permalink_Factory{

		const VERSION = '1.0';
		const MIN_WC_VERSION = '2.1';

		/**
		 * @var The single instance of the class
		 * @since 1.0
		 */
		private static $_this = null;
		private $factory;

		/**
		 * Static Singleton Factory Method
		 *
		 * @return static $_this instance
		 * @readlink http://eamann.com/tech/the-case-for-singletons/
		 */
		public static function instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$_this ) {
				self::$_this = new self;
			}

			return self::$_this;
		}

		/**
		 * WooCommerce Constructor.
		 * @access public
		 * @return WooCommerce
		 */
		public function __construct() {

			// ensure the required files are loaded
			$this->load_classes();

			// Loaded action
			do_action( 'WC_Product_Permalink_Factory/loaded' );
			
		}

		private function includes() {

			// assumes plugin_dir_path( __FILE__ )
			// include_once( 'inc/wc-product-type-settings.php');
			include_once( 'inc/product-permalinks.php' );
			include_once( 'inc/product-type-permalinks.php' );

			do_action( 'WC_Product_Permalink_Factory/includes' );

		}

		public function load_classes(){

			// ensure the required files are loaded
			$this->includes();

			// Load custom product permalink classes
			$load_classes = apply_filters( 'WC_Product_Permalink_Factory/load_classes', array( 'WC_Product_Permalink', 'WC_Product_Type_Permalink' ) );
			foreach ( $load_classes as $class ) {
				$class = new $class();
				$this->factory[ get_class( $class ) ] = $class;
			}

			return $this->factory;
		}

		/**
		 * on plugin activate ensure the permalinks are flushed
		 */
		public static function activate(){

			$factory = self::instance()->load_classes();
			foreach( $factory as $class ) {
				if( method_exists( $class, 'activate' ) )
					$class->activate();
			}
			do_action( 'WC_Product_Permalink_Factory/activate' );
		}

		/**
		 * on plugin deactivate ensure the permalinks are flushed and db is cleaned up
		 */
		public static function deactivate(){
			WC_Product_Permalink::deactivate();
			do_action( 'WC_Product_Permalink_Factory/deactivate' );
		}

		/**
		 * Check the minimum WP version
		 *
		 * @static
		 * @return bool Whether the test passed
		 */
		public static function prerequisites() {;
			$pass = TRUE;
			$pass = $pass && version_compare( WC_VERSION, self::MIN_WC_VERSION, '>=' );
			return $pass;
		}

		/**
		 * Display fail notices
		 *
		 * @static
		 * @return void
		 */
		public static function fail_notices() {
			printf( '<div class="error"><p>%s</p></div>',
				sprintf( __( 'WooCommerce: Product Permalinks requires WooCommerce v%s or higher.', 'woocommerce-product-permalink' ),
					self::MIN_WC_VERSION
				) );
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 2.1
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 2.1
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0' );
		}
	}
}

/**
 * Returns the main instance of WC_Product_Permalink_Factory to prevent the need to use globals.
 *
 * @since  1.0
 * @return object WC_Product_Permalink_Factory
 */
function WC_Product_Permalink() {
	return WC_Product_Permalink_Factory::instance();
}


/**
 * Instantiate class and set up WordPress actions.
 *
 * @return void
 */
function load_wc_product_permalink_plugin() {
	// we assume class_exists( 'WPPluginFramework' ) is true
	if ( apply_filters( 'WC_Product_Permalink_Factory/pre_check', WC_Product_Permalink_Factory::prerequisites() ) ) {

		// when plugin is activated let's load the instance to get the ball rolling
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

			add_action( 'init', array( 'WC_Product_Permalink_Factory', 'instance' ) );

		}

	} else {

		// let the user know prerequisites weren't met
		add_action( 'admin_head', array( 'WC_Product_Permalink_Factory', 'fail_notices' ), 0, 0 );

	}
}
add_action( 'plugins_loaded', 'load_wc_product_permalink_plugin' );
register_activation_hook( __FILE__, array( 'WC_Product_Permalink_Factory', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Product_Permalink_Factory', 'deactivate' ) );