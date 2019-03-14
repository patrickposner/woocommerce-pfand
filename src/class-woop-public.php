<?php

namespace woop;

class WOOP_Public {

	/**
	 * Constructor for WOOP_Public
	 */
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
		if ( ! empty( $pfand ) ) {
			echo $string;
		}
	}

	/**
	 * Add pfand meta to cart
	 *
	 * @param array $cart_item_data current cart item data.
	 * @param int   $product_id current product id.
	 * @param int   $variation_id current variation id.
	 * @return array
	 */
	public function add_pfand_meta_to_cart( $cart_item_data, $product_id, $variation_id ) {

		$pfand = get_post_meta( $product_id, '_pfand_field', true );
		$type  = get_post_meta( $product_id, '_select_quantity_or_metafield_field', true );
		$tax   = get_post_meta( $product_id, '_select_tax_field', true );

		$flaschen = get_post_meta( $variation_id, 'flaschen_menge', true );

		if ( empty( $pfand ) ) {
			return $cart_item_data;
		}

		if ( empty( $type ) ) {
			return $cart_item_data;
		}

		if ( empty( $tax ) ) {
			return $cart_item_data;
		}

		if ( ! empty( $flaschen ) ) {
			$cart_item_data['flaschen'] = $flaschen;
		}

		$cart_item_data['pfand']      = $pfand;
		$cart_item_data['pfand_type'] = $type;
		$cart_item_data['pfand_tax']  = $tax;

		return $cart_item_data;
	}

	/**
	 * Display pfand meta in cart
	 *
	 * @param array  $item_data current cart item.
	 * @param object $cart_item current cart item data.
	 * @return array
	 */
	public function display_pfand_meta_in_cart( $item_data, $cart_item ) {

		if ( empty( $cart_item['pfand'] ) ) {
			return $item_data;
		}

		if ( empty( $cart_item['pfand_type'] ) ) {
			return $item_data;
		}

		if ( ! empty( $cart_item['flaschen'] ) ) {
			$pfand = floatval( $cart_item['pfand'] ) * intval( $cart_item['flaschen'] * intval( $cart_item['quantity'] ) );
		} else {
			if ( 'quantity' === $cart_item['pfand_type'] ) {
				$pfand = floatval( $cart_item['pfand'] ) * intval( $cart_item['quantity'] );
			}
		}

		if ( isset( $pfand ) && ! empty( $pfand ) ) {
			$item_data[] = array(
				'key'     => __( 'Pfand', 'wocommerce-pfand' ),
				'value'   => wc_price( $pfand ),
				'display' => '',
			);
		}

		return $item_data;
	}

	/**
	 * Add pfand fee to cart
	 *
	 * @param object $cart_object current cart object.
	 * @return void
	 */
	public function add_pfand_fee_to_cart_total( $cart_object ) {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		$pfand_fee = 0;
		$taxable   = false;

		foreach ( $cart_object->cart_contents as $key => $value ) {
			$qty   = intval( $value['quantity'] );
			$pfand = floatval( $value['pfand'] );
			$type  = $value['pfand_type'];
			$tax   = $value['pfand_tax'];

			if ( 'yes' === $tax ) {
				$taxable = true;
			}

			if ( isset( $value['flaschen'] ) && ! empty( $value['flaschen'] ) ) {
				$pfand_fee = $pfand_fee + ( $pfand * intval( $value['flaschen'] ) ) * $qty;
			} else {
				if ( 'quantity' === $type ) {
					$pfand_fee = $pfand_fee + $pfand * $qty;
				}
			}
		}

		if ( 0 !== $pfand_fee ) {
			if ( false === $taxable ) {
				$cart_object->add_fee( __( 'Pfand', 'woocommerce-pfand' ), $pfand_fee, false );
			} else {
				$cart_object->add_fee( __( 'Pfand', 'woocommerce-pfand' ), $pfand_fee );
			}
		}
	}

}
