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

/**
 * The Infoplus order parcel class.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Order_Parcel {


	/** @var object the raw order data from the database **/
	protected $data = array(
		'id'              => 0,
		'status'          => '',
		'carrier'         => '',
		'tracking_number' => '',
	);


	/**
	 * Construct the order object.
	 *
	 * @since 1.0.0
	 * @param array $data {
	 *     The Infoplus order parcel data, from the database.
	 *
	 *     @type int    $id              the parcel ID
	 *     @type string $status          the parcel status
	 *     @type string $carrier         the carrier
	 *     @type string $tracking_number the tracking number
	 * }
	 */
	public function __construct( $data ) {

		$this->data = wp_parse_args( $data, $this->data );
	}


	/**
	 * Get the parcel ID.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->data['id'];
	}


	/**
	 * Get the status.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status() {
		return $this->data['status'];
	}


	/**
	 * Get the carrier.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_carrier() {
		return $this->data['carrier'];
	}


	/**
	 * Get the tracking number.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_tracking_number() {
		return $this->data['tracking_number'];
	}

	/**
	 * Get the order's Infoplus dashboard URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_url() {

		return 'https://' . get_option( 'wc_infoplus_url' ) . '/infoplus-wms/fulfillment/parcel-shipment/' . $this->get_id();
	}


	/**
	 * Get the tracking URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_tracking_url() {

		$carrier = strtolower( $this->get_carrier() );

		$tracking_number = urlencode( $this->get_tracking_number() );

		switch ( $carrier ) {

			# UPS
			case SV_WC_Helper::str_starts_with( $carrier, 'ups' ):
				$url ='http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=' . $tracking_number;
			break;

			# FedEx
			case SV_WC_Helper::str_starts_with( $carrier, 'fed' ):
				$url ='http://www.fedex.com/Tracking?action=track&tracknumbers=' . $tracking_number;
			break;

			# USPS
			case SV_WC_Helper::str_starts_with( $carrier, 'mail' ):
				$url ='https://tools.usps.com/go/TrackConfirmAction?qtc_tLabels1=' . $tracking_number;
			break;

			# No match, no URL
			default:
				$url = '';
			break;
		}

		return $url;
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


}
