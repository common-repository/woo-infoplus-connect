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
 * @package     WC_Infoplus\Orders
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

class WC_Infoplus_Order {


	/** @var object the raw order data from the database **/
	protected $data = array(
		'number'  => 0,
		'status'  => '',
		'items'   => array(),
		'parcels' => array(),
	);


	/**
	 * Construct the order object.
	 *
	 * @since 1.0.0
	 * @param array $data {
	 *     The Infoplus order data, from the database.
	 *
	 *     @type int    $number the order number
	 *     @type string $status the order status
	 *     @type array  $items  the order items {
	 *
	 *         @type string $sku      the item sku
	 *         @type string $quantity the ordered quantity
	 *     }
	 *     @type array $parcels the shipped parcels, as \WC_Infoplus_Order_Parcel objects
	 * }
	 */
	public function __construct( $data ) {

		$this->data = wp_parse_args( $data, $this->data );

		// the order number should always be a string
		$this->data['number'] = (string) $this->data['number'];

		if ( ! is_array( $this->data['items'] ) ) {
			$this->data['items'] = array();
		}

		if ( ! is_array( $this->data['parcels'] ) ) {
			$this->data['parcels'] = array();
		}
	}


	/**
	 * Get the order ID.
	 *
	 * For now this is just the order number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_id() {
		return $this->get_number();
	}


	/**
	 * Get the order number.
	 *
	 * @since 1.0.0
	 * @return float
	 */
	public function get_number() {
		return $this->data['number'];
	}


	/**
	 * Get the order status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status() {
		return $this->data['status'];
	}


	/**
	 * Get the order items.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_items() {

		$items = array();

		foreach ( $this->data['items'] as $data ) {
			$items[] = new WC_Infoplus_Order_Item( $data );
		}

		return $items;
	}


	/**
	 * Get the list of items, formatted for display.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_formatted_items() {

		foreach ( $this->get_items() as $item ) {
			$items[] = $item->get_name() . ' &times; ' . $item->get_quantity();
		}

		return implode( ', ', $items );
	}


	/**
	 * Get the order parcels.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_parcels() {

		$parcels = array();

		foreach ( $this->data['parcels'] as $data ) {
			$parcels[] = new WC_Infoplus_Order_Parcel( $data );
		}

		return $parcels;
	}


	/**
	 * Update the order parcels.
	 *
	 * @since 1.0.0
	 */
	public function update_parcels() {

		try {

			$response = wc_infoplus()->get_api()->get_order_parcels( $this->get_number() );

			$this->data['parcels'] = array();

			foreach ( $response->get_parcels() as $parcel ) {
				$this->data['parcels'][] = $parcel->get_data();
			}

		} catch ( SV_WC_API_Exception $e ) {

			wc_infoplus()->log( 'Error getting parcels for order #' . $this->get_number() );
		}
	}


	/**
	 * Get the order's Infoplus dashboard URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_url() {

		return 'https://' . get_option( 'wc_infoplus_url' ) . '/infoplus-wms/order/req/' . $this->get_number();
	}


	/**
	 * Get the raw data, usually for DB storage.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_data() {

		return $this->data;
	}


	/**
	 * Determine if an order is considered shipped.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_shipped() {
		return 'Shipped' === $this->get_status();
	}


}
