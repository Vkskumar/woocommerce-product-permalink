<?php

class WC_Product_Type_Settings {

	private $settings;

	function __construct(){
		// global $wp_filter;
		// echo "<pre>";
  //       print_r( $wp_filter );
  //       echo "</pre>";
		$this->id    = 'products';
		// $this->label = __( 'Products', 'woocommerce' );

		// add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		// add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		// add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		// add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );

		$this->settings['product_types'] = array( 
				'simple' => 'simple-prod', 
				'variable' => 'variable-prod', 
				'grouped' => 'grouped-prod',
				'external' => 'external-prod'
				);

		// add_filter( 'woocommerce_sections_' . $this->id, array( $this, 'woocommerce_settings_tabs_array') );
		// add_filter( 'woocommerce_get_settings_pages',array( $this, 'woocommerce_settings_tabs_array') );
		add_filter( 'WC_Product_Type_Permalink/product_types', array( $this, 'product_types' ) );
		add_action(  'woocommerce_sections_' . $this->id . '_list', array( $this, 'add_section' ) );
	}

	function add_section( $stuff ){
		print_r($stuff);
		$stuff[ 'permalink'] = __( 'Links', 'woocommerce' );
		return $stuff;
	}

	function woocommerce_settings_tabs_array( $stuff ){
		echo '<pre>';
				echo 'prod type settings';
		print_r( $stuff);
		echo '</pre>';
		// return $stuff;
	}
	function product_types(){
		return (array) $this->settings['product_types'];
	}

}