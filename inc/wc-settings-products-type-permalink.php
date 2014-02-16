<?php

class WC_Settings_Products_Type_Permalink extends WC_Settings_Products{

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'products';
		// add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_filter( 'woocommerce_product_permalinks_settings', array( $this, 'permalink_type_get_settings' ) );
		add_action( 'woocommerce_update_option_product_type_permalink_filter', array( $this, 'permalink_type_save' ) );
		add_action( 'woocommerce_admin_field_product_type_permalink_filter', array( $this, 'permalink_type_field' ) );
	}

	/**
	 * Save settings
	 */
	public function permalink_type_save( $value ) {
		$saved_post_types = array();
		$post_types = WC_Product_Type_Permalink::product_types( false );
		$key = 'woocommerce_product_type_permalink_filter';
		if ( !empty( $_POST[ $key ]) ){
			foreach( $_POST[ $key ] as $post_type => $slug ){
				if( ! array_key_exists( $post_type, $post_types) ) continue;
	        	$slug = wc_clean( stripslashes( $slug ) );
	        	// if( !empty( $slug ) )
	        		$post_types[ $post_type ] = $slug;

			}
		}
		update_option( 'woocommerce_product_type_permalink_types', $post_types );
	}

	public function permalink_type_field( $value ){

		// Custom attribute handling
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) )
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value )
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';

		// Description handling
		if ( $value['desc_tip'] === true ) {
			$description = '';
			$tip = $value['desc'];
		} elseif ( ! empty( $value['desc_tip'] ) ) {
			$description = $value['desc'];
			$tip = $value['desc_tip'];
		} elseif ( ! empty( $value['desc'] ) ) {
			$description = $value['desc'];
			$tip = '';
		} else {
			$description = $tip = '';
		}

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ) ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ) ) ) {
			$description =  wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		if ( $tip && in_array( $value['type'], array( 'checkbox' ) ) ) {

			$tip = '<p class="description">' . $tip . '</p>';

		} elseif ( $tip ) {

			$tip = '<img class="help_tip" data-tip="' . esc_attr( $tip ) . '" src="' . WC()->plugin_url() . '/assets/images/help.png" height="16" width="16" />';

		}


		$type 			= $value['type'];
    	$class 			= '';
    	$option_value 	= WC_Admin_Settings::get_option( $value['id'], $value['default'] );

    	if ( $value['type'] == 'color' ) {
    		$type = 'text';
    		$value['class'] .= 'colorpick';
        	$description .= '<div id="colorPickerDiv_' . esc_attr( $value['id'] ) . '" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>';
    	}

    	?><tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
				<?php echo $tip; ?>
			</th>
            <td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
            	<input
            		name="<?php echo esc_attr( $value['id'] ); ?>"
            		id="<?php echo esc_attr( $value['id'] ); ?>"
            		type="<?php echo esc_attr( $type ); ?>"
            		style="<?php echo esc_attr( $value['css'] ); ?>"
            		value="<?php echo esc_attr( $option_value ); ?>"
            		class="<?php echo esc_attr( $value['class'] ); ?>"
            		<?php echo implode( ' ', $custom_attributes ); ?>
            		/> <?php echo $description; ?>
            </td>
        </tr><?php
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function permalink_type_get_settings( $settings = array() ) {
		global $current_section;

		if ( $current_section == 'permalink' ) {

			$new_settings[] = array( 'title' => __( 'Product Types', 'woocommerce' ), 'type' => 'title', 'desc' => '', 'id' => 'product_type_permalinks_options' );
			$new_settings[] = array(
				'title' => __( 'Enable', 'woocommerce' ),
				'desc' 		=> __( 'Enable Product Type Filters', 'woocommerce' ),
				'id' 		=> 'woocommerce_product_type_permalink_enabled',
				'default'	=> 'no',
				'type' 		=> 'checkbox',
				'show_if_checked' => 'yes',
				'autoload'      => false
			);

			foreach( WC_Product_Type_Permalink::product_types( false ) as $product_type => $slug ){
				$new_settings[] = array(
					'title' 	=> __('Product Type:') . ' ' . ucwords( $product_type ),
					'id' 		=> 'woocommerce_product_type_permalink_filter[' . $product_type . ']',
					'type' 		=> 'product_type_permalink_filter',
					'default'	=> $slug,
					'autoload'  => false
				);
			}
			$new_settings[] = array( 'type' => 'sectionend', 'id' => 'product_type_permalinks_options' );

			$settings = array_merge( $settings, $new_settings);
			return apply_filters('woocommerce_product_type_permalinks_settings', $settings );
		} else {
			return $settings;
		}
	}

}

return new WC_Settings_Products_Type_Permalink();