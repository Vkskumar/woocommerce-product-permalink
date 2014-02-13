<?php
/**
 * Abstract Product Permalink Class
 *
 * Filters for hijacking the product permalinks
 *
 * @package   WooCommerce: Product Permalinks
 * @author    Timothy Wood @codearachnid <tim@imaginesimplicity.com>
 * @license   GPLv3
 * @link      http://codearachnid.github.io/woocommerce-product-permalink/
 * @copyright Copyright (C) 2014, Imagine Simplicity LLC, All Rights Reserved.
 */

if( !class_exists( 'WC_Product_Permalink' ) ){
	class WC_Product_Permalink{

		public static $option_flush_key = 'woocommerce_product_permalink_flush_rewrite_rules';
		private $query_vars = array();

		function __construct(){
			
			add_filter( 'woocommerce_register_post_type_product', array( $this, 'woocommerce_register_post_type_product' ) );
			add_filter( 'query_vars', array( $this, 'query_vars' ) );
			add_filter( 'admin_init', array( $this, 'admin_init' ) );

		}

		/**
		 * init query vars for forcing strict product type permalinks
		 * @param  array $query_vars
		 * @return array
		 */
		public function query_vars( $query_vars ) {
			return array_merge(
				(array) $query_vars, 
				(array) apply_filters( 'WC_Product_Permalink/query_vars', $this->query_vars ) 
				);
		}

		/**
		 * destroy default woocommerce cpt registration slug
		 * @param  array $post_type_args
		 * @return array
		 */
		public function woocommerce_register_post_type_product( $post_type_args ){
			$post_type_args['rewrite'] = false;	
			return apply_filters( 'WooCommerce_Product_Type_Permalink/woocommerce_register_post_type_product', $post_type_args );
		}

		protected static function set_404(){
			global $wp_query;
			// figure out how to gracefully exit
			$wp_query->is_404 = apply_filters( 'WooCommerce_Product_Type_Permalink/is_404', true );
			status_header(404);
			include get_404_template();
			exit;
		}

		/**
		 * try to flush if in admin
		 */
		public function admin_init() {
		    $this->flush_rewrites();
		}

		/**
		 * public method to flush rewrites and update the database to 
		 * prevent hot loading rewrites
		 * @return null
		 */
		public function flush_rewrites(){
			if ( 'yes' == get_option( self::$option_flush_key ) ) {
		        flush_rewrite_rules();
		        update_option( self::$option_flush_key, 'no');
		    }
		}

		/**
		 * on plugin deactivate ensure the permalinks are flushed and db is cleaned up
		 */
		public function deactivate(){
			flush_rewrite_rules();
			delete_option( self::$option_flush_key );
		}

	}
}