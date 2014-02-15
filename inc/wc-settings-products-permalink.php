<?php

class WC_Settings_Products_Permalink extends WC_Settings_Products{

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'products';
		add_filter( 'woocommerce_sections_' . $this->id . '_array', array( $this, 'inject_section' ) );
		add_filter( 'woocommerce_product_settings', array( $this, 'permalink_get_settings' ) );
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
	public function permalink_get_settings( $settings = array() ) {
		global $current_section;
		if ( $current_section == 'permalink' ) {
			return apply_filters('woocommerce_product_permalinks_settings', array(
				array(	'title' => __( 'Permalink Options', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'product_permalinks_options' ),
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

		} else {
			return $settings;
		}
	}

}

return new WC_Settings_Products_Permalink();