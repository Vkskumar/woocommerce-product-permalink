<?php
/*
Plugin Name: WooCommerce: Product Type Permalinks
Plugin URI: http://codearachnid.github.io/woocommerce-product-type-permalink/
Description: An addon for WooCommerce to create customized product type permalinks with features.
Version: 1.0
Author: Timothy Wood @codearachnid
Author URI: http://www.imaginesimplicity.com
Text Domain: woocommerce-product-type-permalink
Domain Path: /lang/
License: GPLv3

WooCommerce: Product Type Permalinks
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

require_once( plugin_dir_path( __FILE__ ) . 'inc/product-permalinks.php' );

register_activation_hook( __FILE__, array( 'WooCommerce_Product_Type_Permalink', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WooCommerce_Product_Type_Permalink', 'deactivate' ) );

if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {

	add_action( 'plugins_loaded', array( 'WooCommerce_Product_Type_Permalink', 'instance' ) );

}

