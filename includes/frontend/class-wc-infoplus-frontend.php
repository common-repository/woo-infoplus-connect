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
 * @package     WC_Infoplus\Frontend
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the front-end.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Frontend {


	/**
	 * Bootstrap class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add the shipments list table to the View Order screen
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_order_shipments_table' ) );
	}


	/**
	 * Add the shipments list table to the View Order screen.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order the order object
	 */
	public function add_order_shipments_table( $order ) {

		$shipments = array();

		$infoplus_orders = wc_infoplus()->get_order_handler()->get_infoplus_orders( $order );

		foreach ( $infoplus_orders as $infoplus_order ) {
			$shipments = array_merge( $shipments, $infoplus_order->get_parcels() );
		}

		// no shipments? no table
		if ( empty( $shipments ) ) {
			return;
		}

		wc_get_template( 'order/order-infoplus-shipments.php', array(
			'shipments' => $shipments,
		) );
	}


}
