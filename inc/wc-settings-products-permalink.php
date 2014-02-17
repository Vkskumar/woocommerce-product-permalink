<?php

class WC_Settings_Products_Permalink extends WC_Settings_Products{

	private $newer_wc = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'products';
		$this->newer_wc = version_compare( WC_VERSION, '2.2.0', '>=' );
		// newer versions of WooCommerce take advantage of customizable sections
		if( $this->newer_wc ) {
			add_filter( 'woocommerce_get_sections_' . $this->id, array( $this, 'inject_section' ) );
		}

		add_filter( 'woocommerce_product_settings', array( $this, 'permalink_inject_settings' ) );
	}

	/**
	 * Get sections
	 *
	 * @return array
	 */
	public function inject_section( $sections ) {
		$sections[ 'permalink' ] = __( 'Permalink', 'woocommerce' );
		return $sections;
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function permalink_inject_settings( $settings = array() ) {
		global $current_section;
		if ( ! $this->newer_wc ){
			return array_merge( $settings, (array) $this->permalink_get_settings() );
		} elseif ( $this->newer_wc && $current_section == 'permalink' ) {
			return (array) $this->permalink_get_settings();
		} else {
			return $settings;
		}
	}

	function permalink_get_settings(){
		return apply_filters('woocommerce_product_permalinks_settings', array(
			array(	'title' => __( 'Product Custom Permalinks', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'product_permalinks_options' ),
			array(
				'title' => __( 'Flush Permalinks', 'woocommerce' ),
				'desc' 		=> __( 'Flush Permalinks', 'woocommerce' ),
				'id' 		=> 'woocommerce_product_permalink_flush_rewrite_rules',
				'default'	=> 'no',
				'type' 		=> 'checkbox',
				'show_if_checked' => 'yes',
				'desc_tip'	=> __( 'If checked flush rewrites.' )
			),
			array( 'type' => 'sectionend', 'id' => 'product_permalinks_options' ),
		));
	}

}

return new WC_Settings_Products_Permalink();