<?php

/*
Plugin Name:	Pfand for WooCommerce
Plugin URI:		https://patrickposner.de
Description: 	A clean and simple approach to add the Pfand concept to WooCommerce
Author: 		Patrick Posner
Version:		1.0
Text Domain:    woocommerce-pfand
Domain Path:    /languages
*/

define( 'WOOPFAND_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WOOPFAND_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/* localize */
$textdomain_dir = plugin_basename( dirname( __FILE__ ) ) . '/languages';
load_plugin_textdomain( 'woocommerce-pfand', false, $textdomain_dir );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

woop\WOOP_Admin::get_instance();
woop\WOOP_Public::get_instance();
