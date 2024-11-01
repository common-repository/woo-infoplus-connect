<?php
/**
 * WooCommerce Infoplus
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Infoplus to newer
 * versions in the future. If you wish to customize WooCommerce Infoplus for your
 * needs please refer to https://skyverge.com/documentation-infoplus-connect-woocommerce/
 *
 * @package     WC_Infoplus\Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the products admin.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Admin_Products {


	/**
	 * Construct class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		/** Edit Product **/

		// add a checkbox to enable Infoplus fulfillment
		add_action( 'woocommerce_product_options_dimensions', array( $this, 'add_fulfillment_toggle' ) );

		// save the product meta
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_meta' ), 10, 2 );

		/** Bulk Edit **/

		// add Infoplus fulfillment select box to the product bulk edit area
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'add_fulfillment_bulk_edit_field' ) );

		// process the product bulk edit to mark product(s) as fulfilled by Infoplus
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_fulfillment_bulk_edit_field' ) );

		// add the Infoplus fulfillment status column to the products list table
		add_filter( 'manage_edit-product_columns', array( $this, 'add_fulfillment_column_header' ), 20 );

		// display the Infoplus fulfillment status for products
		add_action( 'manage_product_posts_custom_column', array( $this, 'add_fulfillment_column_content' ), 10, 2 );
	}


	/**
	 * Add a checkbox to enable Infoplus fulfillment.
	 *
	 * @since 1.0.0
	 */
	public function add_fulfillment_toggle() {

		woocommerce_wp_checkbox( array(
			'id'          => '_wc_infoplus_fulfillment',
			'label'       => __( 'Infoplus Fulfillment', 'woo-infoplus-connect' ),
			'description' => __( 'This product is fulfilled by Infoplus', 'woo-infoplus-connect' ),
		) );
	}


	/**
	 * Save the product meta.
	 *
	 * @since 1.0.0
	 * @param int $post_id the product ID
	 */
	public function save_meta( $post_id ) {

		update_post_meta( $post_id, '_wc_infoplus_fulfillment', isset( $_POST['_wc_infoplus_fulfillment'] ) ? 'yes' : 'no' );
	}


	/**
	 * Add Infoplus fulfillment select box to the product bulk edit area.
	 *
	 * @since 1.0.0
	 */
	public function add_fulfillment_bulk_edit_field() {

		?>

		<label>
			<span class="title"><?php esc_html_e( 'Fulfilled by Infoplus?', 'woo-infoplus-connect' ); ?></span>
				<span class="input-text-wrap">
					<select class="change_wc_infoplus_fulfillment change_to" name="change_infoplus_fulfillment">
					<?php
					$options = array(
						''    => __( '— No Change —', 'woo-infoplus-connect' ),
						'yes' => __( 'Yes', 'woo-infoplus-connect' ),
						'no'  => __( 'No', 'woo-infoplus-connect' )
					);
					foreach ( $options as $key => $value ) {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
					}
					?>
				</select>
			</span>
		</label>

		<?php
	}


	/**
	 * Process the product bulk edit to mark product(s) as fulfilled by Infoplus.
	 *
	 * @since 1.0.0
	 * @param \WC_Product $product the product object
	 */
	public function save_fulfillment_bulk_edit_field( $product ) {

		// update option
		if ( ! empty( $_REQUEST['change_infoplus_fulfillment'] ) ) {
			update_post_meta( $product->get_id(), '_wc_infoplus_fulfillment', ( 'yes' === $_REQUEST['change_infoplus_fulfillment'] ) ? 'yes' : 'no' );
		}
	}


	/**
	 * Add the Infoplus fulfillment status column to the products list table.
	 *
	 * @since 1.0.0
	 * @param array $columns
	 * @return array
	 */
	public function add_fulfillment_column_header( $columns ) {

		$new_columns = array();

		foreach ( $columns as $column_name => $column_info ) {

			$new_columns[ $column_name ] = $column_info;

			if ( 'is_in_stock' === $column_name ) {
				$new_columns['wc_infoplus_fulfillment'] = __( 'Infoplus?', 'woo-infoplus-connect' );
			}
		}

		return $new_columns;
	}

	/**
	 * Display the Infoplus fulfillment status for products.
	 *
	 * @since 1.0.0
	 * @param string $column_id the current column ID
	 * @param int $post_id the current post ID
	 */
	public function add_fulfillment_column_content( $column_id, $post_id ) {

		if ( 'wc_infoplus_fulfillment' === $column_id ) {

			$product = wc_get_product( $post_id );

			if ( ! $product->get_sku() ) {
				echo '<span class="wc-infoplus-fulfillment na">' . esc_html__( 'N/A', 'woo-infoplus-connect' ) . '</span>';
			} else if ( 'yes' === get_post_meta( $product->get_id(), '_wc_infoplus_fulfillment', true ) ) {
				echo '<span class="wc-infoplus-fulfillment managed">' . esc_html__( 'Yes', 'woo-infoplus-connect' ) . '</span>';
			} else {
				echo '<span class="wc-infoplus-fulfillment not-managed">' . esc_html__( 'No', 'woo-infoplus-connect' ) . '</span>';
			}
		}
	}


}
