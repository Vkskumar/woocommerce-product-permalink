<?php
/**
 * Filters for hijacking the product permalinks
 *
 * @package   WooCommerce: Product Type Permalinks
 * @author    Timothy Wood @codearachnid <tim@imaginesimplicity.com>
 * @license   GPLv3
 * @link      http://codearachnid.github.io/woocommerce-product-type-permalink/
 * @copyright Copyright (C) 2014, Imagine Simplicity LLC, All Rights Reserved.
 */

if( !class_exists( 'WooCommerce_Product_Type_Permalink' ) ){
	class WooCommerce_Product_Type_Permalink{

		const VERSION = '1.0.0';
		const MIN_WOO_VERSION = 'x.x';
		private static $_this = null;
		private $option_flush_key = 'woocommerce_post_type_permalink_flush_rewrite_rules';
		private $product_types;
		private $query_var = 'strict_product_type';

		function __construct(){

			add_filter( 'query_vars', array( $this, 'query_vars' ) );
			add_filter( 'init', array( $this, 'init' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
			add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 3 );
			add_filter( 'woocommerce_register_post_type_product', array( $this, 'woocommerce_register_post_type_product' ) );

			$this->product_types = array( 
				'simple' => 'simple-product', 
				'variable' => 'variable-product', 
				'grouped' => 'grouped-product',
				'external' => 'external-product'
				);

		}

		/**
		 * init query vars for forcing strict product type permalinks
		 * @param  array $query_vars
		 * @return array
		 */
		public function query_vars( $query_vars ) {
			array_push( $query_vars, $this->query_var );
			return $query_vars;
		}


		/**
		 * add custom permastructures for products
		 */
		public function init(){

			// assuming default woocommerce product types:
			// * simple
			// * variable
			// * grouped
			// * external
			foreach( $this->product_types as $product_type => $slug ){
				$key = 'product_' . $product_type;
				add_rewrite_tag( '%' . $key . '%', $slug, $this->query_var . '=' . $product_type . '&product=' );
				add_permastruct( $key, '%' . $key . '%/%postname%', array(
					'with_front' => false
					));
			}
		}

		/**
		 * format pretty permalinks for products
		 */
		public function post_type_link( $permalink, $post, $leavename ){

			// exit gracefully if not a product or if pretty permalinks are disabled
			if( empty( $post->post_type ) || 'product' != $post->post_type || '' == get_option('permalink_structure') )
				return $permalink;

			global $wp_rewrite, $woocommerce, $the_product, $wp_query;
			$post_link = '';

			if ( empty( $the_product ) || $the_product->id != $post->ID )
				$the_product = get_product( $post );

			if( array_key_exists( $the_product->product_type, $this->product_types ) ){
				$slug = get_page_uri($the_product->id);
				$post_link = trailingslashit( $this->product_types[ $the_product->product_type ] ) . get_page_uri( $the_product->id );
				$permalink = home_url( user_trailingslashit( $post_link ) );
			}

			return apply_filters( 'WooCommerce_Product_Type_Permalink/post_type_link', $permalink, $post, $leavename );
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

		/**
		 * force strict permalinks for defined product types
		 */
		public function template_redirect(){
			$strict_product_type = get_query_var( $this->query_var );
			if( !empty( $strict_product_type ) ){
				global $post, $woocommerce, $the_product, $wp_query;

				if ( empty( $the_product ) || $the_product->id != $post->ID )
					$the_product = get_product( $post );

				// set is_404 since product type doesn't match the permalink
				if( $strict_product_type != $the_product->product_type ) {

					// figure out how to gracefully exit
					$wp_query->is_404 = apply_filters( 'WooCommerce_Product_Type_Permalink/is_404', true );
					status_header(404);
					include get_404_template();
					exit;

				}
				
			}
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
		public static function flush_rewrites(){
			if ( get_option( self::instance()->option_flush_key ) == false ) {
		        flush_rewrite_rules();
		        update_option( self::instance()->option_flush_key, true);
		    }
		}

		/**
		 * on plugin activate ensure the permalinks are flushed
		 */
		public static function activate(){
			self::flush_rewrites();
		}

		/**
		 * on plugin deactivate ensure the permalinks are flushed and db is cleaned up
		 */
		public static function deactivate(){
			flush_rewrite_rules();
			delete_option( self::instance()->option_flush_key );
		}

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
	}
}