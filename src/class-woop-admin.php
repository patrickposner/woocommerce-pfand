<?php

namespace woop;

class WOOP_Admin {

	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
		add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'add_pfand_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_pfand_field' ), 10, 1 );

		/* variation meta */
		add_action( 'woocommerce_variation_options_pricing', array( $this, 'add_flaschen_menge_field' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_flaschen_menge_field' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( $this, 'add_flaschen_menge_to_variation_data' ) );
	}

	/**
	 * Return instance of WOOP_Admin
	 *
	 * @return void
	 */
	public static function get_instance() {
		new WOOP_Admin();
	}

	/**
	 * Add pfand field to product stock settings
	 *
	 * @return void
	 */
	public function add_pfand_field() {

		global $woocommerce, $post;

		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'                => '_pfand_field',
				'label'             => __( 'Pfand', 'woocommerce-pfand' ),
				'placeholder'       => '',
				'description'       => __( 'Füge deinen Pfandwert ein.', 'woocommerce-pfand' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => 'any',
				),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_select_quantity_or_metafield_field',
				'label'   => __( 'Calculation-Type', 'woocommerce-pfand' ),
				'options' => array(
					'quantity'  => __( 'Quantity', 'woocommerce-pfand' ),
					'metafield' => __( 'Metafield', 'woocommerce-pfand' ),
				),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_select_tax_field',
				'label'   => __( 'Apply Tax', 'woocommerce-pfand' ),
				'options' => array(
					'no'  => __( 'No', 'woocommerce-pfand' ),
					'yes' => __( 'Yes', 'woocommerce-pfand' ),
				),
			)
		);

		echo '</div>';
	}

	/**
	 * Save pfand field
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function save_pfand_field( $post_id ) {

		$woocommerce_pfand_field      = $_POST['_pfand_field'];
		$woocommerce_pfand_type_field = $_POST['_select_quantity_or_metafield_field'];
		$woocommerce_pfand_tax_field  = $_POST['_select_tax_field'];

		if ( ! empty( $woocommerce_pfand_field ) ) {
			update_post_meta( $post_id, '_pfand_field', esc_attr( $woocommerce_pfand_field ) );
		} else {
			delete_post_meta( $post_id, '_pfand_field' );
		}
		if ( ! empty( $woocommerce_pfand_type_field ) ) {
			update_post_meta( $post_id, '_select_quantity_or_metafield_field', esc_attr( $woocommerce_pfand_type_field ) );
		}
		if ( ! empty( $woocommerce_pfand_tax_field ) ) {
			update_post_meta( $post_id, '_select_tax_field', esc_attr( $woocommerce_pfand_tax_field ) );
		}

	}
	/**
	 * Add flaschenmenge as variation field
	 *
	 * @param array $loop
	 * @param array $variation_data
	 * @param object $variation
	 * @return void
	 */
	public function add_flaschen_menge_field( $loop, $variation_data, $variation ) {

		echo '<div class="pfand">';

		woocommerce_wp_text_input(
			array(
				'id'    => 'flaschen_menge[' . $loop . ']',
				'class' => 'short',
				'label' => __( 'Anzahl der Flaschen', 'woocommerce-pfand' ),
				'type'  => 'number',
				'value' => get_post_meta( $variation->ID, 'flaschen_menge', true ),
			)
		);

		echo '</div>';
	}

	/**
	 * Save flaschen menge to variation
	 *
	 * @param int $variation_id
	 * @param string $i
	 * @return void
	 */
	public function save_flaschen_menge_field( $variation_id, $i ) {

		$flaschen = $_POST['flaschen_menge'][ $i ];

		if ( ! empty( $flaschen ) ) {
			update_post_meta( $variation_id, 'flaschen_menge', esc_attr( $flaschen ) );
		} else {
			delete_post_meta( $variation_id, 'flaschen_menge' );
		}
	}

	/**
	 * Add flaschen menge to variation data
	 *
	 * @param array $variations
	 * @return void
	 */
	public function add_flaschen_menge_to_variation_data( $variations ) {
		if ( isset( $variations['flaschen_menge'] ) && ! empty( $variations['flaschen_menge'] ) ) {
			$variations['flaschen_menge'] = '<div class="woocommerce_custom_field">Anzahl Flaschen: <span>' . get_post_meta( $variations['variation_id'], 'flaschen_menge', true ) . '</span></div>';
		}
		return $variations;
	}
	/**
	 * Add admin styles
	 *
	 * @return void
	 */
	public function add_admin_scripts() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'woop-admin', WOOPFAND_URL . '/assets/woop-admin' . $suffix . '.css', array(), 1, 'all' );
	}


}
