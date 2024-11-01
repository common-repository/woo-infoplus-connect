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
 * The Infoplus API parcels request.
 *
 * @since 1.0.0
 */
class WC_Infoplus_API_Parcels_Request extends WC_Infoplus_API_Request {


	/**
	 * Get an order's parcels.
	 *
	 * @since 1.0.0
	 * @param float $order_number the order number for which to retrieve parcels
	 */
	public function get_parcels( $order_number ) {

		$query = rawurlencode( 'orderNo eq ' . (float) $order_number );

		$this->path = '/parcelShipment/search?filter=' . $query;

		$this->method = 'GET';
	}


}
