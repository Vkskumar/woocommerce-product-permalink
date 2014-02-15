<?php
/**
 * Filters for hijacking the product permalinks by product type
 *
 * @package   WooCommerce: Product Type Permalinks
 * @author    Timothy Wood @codearachnid <tim@imaginesimplicity.com>
 * @license   GPLv3
 * @link      http://codearachnid.github.io/woocommerce-product-type-permalink/
 * @copyright Copyright (C) 2014, Imagine Simplicity LLC, All Rights Reserved.
 */

if( !class_exists( 'WC_Product_Type_Permalink' ) ){
	class WC_Product_Type_Permalink extends WC_Product_Permalink{

		private $product_types;
		private $query_var = 'strict_product_type';

		function __construct(){

			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_filter( 'WC_Product_Permalink/query_vars', array( $this, 'custom_query_vars' ) );
			add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 3 );
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings' ) );
			
			$this->product_types = self::product_types();

			// add_filter( 'admin_init', array( $this, 'create_rewrites' ), 10, 3 );
			$this->create_rewrites();

		}

		public static function product_types(){
			$product_types = array();

			if( get_option('woocommerce_product_type_permalink_enabled') == 'yes' ) {
				// assuming default woocommerce product types:
				// * simple
				// * variable
				// * grouped
				// * external
				$default = array(
					'simple'   => 'simple-product',
					'variable' => 'variable-product',
					'grouped'  => 'grouped-product',
					'external' => 'external-product'
					);
				$configured_types = get_option('woocommerce_product_type_permalink_types');
				$product_types = !empty($configured_types) ? $configured_types : $default;
			}

			return apply_filters( 'WC_Product_Type_Permalink/product_types', $product_types );
		}

		function add_settings( $settings ){
			$settings[] = include( 'wc-settings-products-type-permalink.php' );
			return $settings;
		}

		/**
		 * init query vars for forcing strict product type permalinks
		 * @param  array $query_vars
		 * @return array
		 */
		public function custom_query_vars( $query_vars ) {
			array_push( $query_vars, $this->query_var );
			return $query_vars;
		}


		/**
		 * add custom permastructures for products
		 */
		public function create_rewrites( $force = false ){

			foreach( $this->product_types as $product_type => $slug ){
				$key = 'product_' . $product_type;
				add_rewrite_tag( '%' . $key . '%', $slug, $this->query_var . '=' . $product_type . '&product=' );
				add_permastruct( $key, '%' . $key . '%/%postname%/', array(
					'with_front' => false // matches the existing default structure of WooCommerce
					));
			}

			parent::force_flush( $force );

		}

		/**
		 * format pretty permalinks for products
		 */
		public function post_type_link( $post_link, $post, $leavename ){

			// exit gracefully if not a product or if pretty permalinks are disabled
			if( empty( $post->post_type ) || 'product' != $post->post_type || '' == get_option('permalink_structure') )
				return $post_link;

			global $the_product;

			if ( empty( $the_product ) || $the_product->id != $post->ID )
				$the_product = get_product( $post );

			if( array_key_exists( $the_product->product_type, $this->product_types ) ){
				$post_link = str_replace("{$post->post_type}/", trailingslashit( $this->product_types[ $the_product->product_type ] ), $post_link);
			}

			return apply_filters( 'WooCommerce_Product_Type_Permalink/post_type_link', $post_link, $post, $leavename );
		}

		/**
		 * force strict permalinks for defined product types
		 */
		public function template_redirect(){
			global $post, $the_product;
			$type_query_var = get_query_var( $this->query_var );

			// the product isn't setup properly
			if ( empty( $the_product ) || $the_product->id != $post->ID )
				$the_product = get_product( $post );

			if( ! empty( $type_query_var ) && ! empty($the_product->product_type) ){				
				
				// set is_404 since product type doesn't match the permalink
				if( $type_query_var != $the_product->product_type ) {

					parent::set_404();

				}
				
			}
		}

		public function activate(){
			// let the plugin know we need to flush the permalinks
			update_option( parent::$option_flush_key, 'yes' );
		}

	}
}