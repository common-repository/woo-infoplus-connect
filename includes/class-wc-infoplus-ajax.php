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
 * @package     WC_Infoplus\AJAX
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Handle AJAX.
 *
 * @since 1.0.0
 */
class WC_Infoplus_AJAX {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add Infoplus fulfillment status meta to order line items via AJAX
		add_action( 'woocommerce_ajax_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 2 );

		// update all orders with Infoplus order data
		add_action( 'wp_ajax_wc_infoplus_update_orders', array( $this, 'update_orders' ) );
	}


	/**
	 * Add Infoplus fulfillment status meta to order line items via AJAX.
	 *
	 * @since 1.0.0
	 * @param int $item_id the order item ID
	 * @param array $item the order item data
	 */
	public function add_order_item_meta( $item_id, $item ) {

		$managed = ( get_post_meta( $item['product_id'], '_wc_infoplus_fulfillment', true ) ) ? get_post_meta( $item['product_id'], '_wc_infoplus_fulfillment', true ) : 'no';

		wc_add_order_item_meta( $item_id, '_wc_infoplus_fulfillment', $managed );
	}


	/**
	 * Update all orders with Infoplus order data.
	 *
	 * @since 1.0.0
	 */
	public function update_orders() {

		check_ajax_referer( 'wc_infoplus_update_orders', 'security' );

		$order_ids = wc_infoplus()->get_order_handler()->update_all();

		$order_count = count( $order_ids );

		wp_send_json_success( array(
			'message' => sprintf( _n( '%d orders updated', '%d orders updated', $order_count, 'woo-infoplus-connect' ), $order_count ),
		) );
	}


}
