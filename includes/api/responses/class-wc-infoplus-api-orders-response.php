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
 * @package     WC_Infoplus\API
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * The Infoplus API orders response
 *
 * @since 1.0.0
 */
class WC_Infoplus_API_Orders_Response extends WC_Infoplus_API_Order_Response {


	/**
	 * Get the order from the API data.
	 *
	 * @since 1.0.0
	 * @return array WC_Infoplus_Order objects
	 */
	public function get_orders() {

		$orders = array();

		foreach ( $this->response_data as $order ) {

			$data = array(
				'number'  => $order->orderNo,
				'status'  => $order->status,
				'items'   => $this->prepare_line_items( $order ),
			);

			$order = new WC_Infoplus_Order( $data );

			$orders[] = $order;
		}

		return $orders;
	}


}
