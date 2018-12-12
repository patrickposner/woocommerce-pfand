<?php

namespace woop;

class WOOP_Public {

	public function __construct() {

		add_action( 'wgm_after_tax_display_single', array( $this, 'add_pfand_single_product' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_pfand_meta_to_cart' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_pfand_meta_in_cart' ), 10, 2 );
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_pfand_fee_to_cart_total' ), 10, 1 );
	}

	/**
	 * Return instance of WOOP_Admin
	 *
	 * @return void
	 */
	public static function get_instance() {
		new WOOP_Public();
	}

	/**
	 * Add pfand meta to single product
	 *
	 * @return void
	 */
	public function add_pfand_single_product() {

		$pfand  = str_replace( '.', ',', get_post_meta( get_the_id(), '_pfand_field', true ) );
		$string = '<div class="wgm-info woocommerce-de_price_taxrate ">Zzgl. ' . $pfand . ' € Pfand</div>';

		echo $string;
	}

	/**
	 * Add pfand meta to cart
	 *
	 * @param array $cart_item_data
	 * @param int $product_id
	 * @param int $variation_id
	 * @return void
	 */
	public function add_pfand_meta_to_cart( $cart_item_data, $product_id, $variation_id ) {

		$pfand    = get_post_meta( $product_id, '_pfand_field', true );
		$flaschen = get_post_meta( $variation_id, 'flaschen_menge', true );

		if ( empty( $pfand ) ) {
			return $cart_item_data;
		}

		if ( empty( $flaschen ) ) {
			return $cart_item_data;
		}

		$cart_item_data['pfand']  = $pfand;
		$cart_item_data['flaschen'] = $flaschen;

		return $cart_item_data;
	}

	/**
	 * Display pfand meta in cart
	 *
	 * @param array $item_data
	 * @param object $cart_item
	 * @return void
	 */
	public function display_pfand_meta_in_cart( $item_data, $cart_item ) {

		if ( empty( $cart_item['pfand'] ) ) {
			return $item_data;
		}
		if ( empty( $cart_item['flaschen'] ) ) {
			return $item_data;
		}

		$pfand = floatval( $cart_item['pfand'] ) * intval( $cart_item['flaschen'] );

		$item_data[] = array(
			'key'     => __( 'Pfand', 'wocommerce-pfand' ),
			'value'   => wc_clean( $pfand . ' €' ),
			'display' => '',
		);

		return $item_data;
	}

	/**
	 * Add pfand fee to cart
	 *
	 * @param object $cart_object
	 * @return void
	 */
	public function add_pfand_fee_to_cart_total( $cart_object ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$pfand_fee = 0;

		foreach ( $cart_object->cart_contents as $key => $value ) {
			$qty      = intval( $value['quantity'] );
			$pfand    = floatval( $value['pfand'] );
			$flaschen = intval( $value['flaschen'] );

			$pfand_fee .= ( $pfand * $flaschen ) * $qty;
		}

		if ( 0 !== $pfand_fee ) {
			$cart_object->add_fee( __( "Pfand", "woocommerce-pfand" ), $pfand_fee, false );
		}
	}

}
