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
 * The Infoplus API parcels response
 *
 * @since 1.0.0
 */
class WC_Infoplus_API_Parcels_Response extends WC_Infoplus_API_Response {


	/**
	 * Get the parcels from the API data.
	 *
	 * @since 1.0.0
	 * @return array WC_Infoplus_Order_Parcel objects
	 */
	public function get_parcels() {

		$parcels = array();

		foreach ( $this->response_data as $parcel ) {

			$data = array(
				'id'              => $parcel->id,
				'status'          => $parcel->status,
				'carrier'         => $this->get_parcel_carrier( $parcel->carrierServiceId ),
				'tracking_number' => $parcel->trackingNo,
			);

			$parcels[] = new WC_Infoplus_Order_Parcel( $data );
		}

		return $parcels;
	}


	/**
	 * Get the carrier name for a parcel from the Infoplus API.
	 *
	 * @since 1.0.0
	 * @param int $id the carrier service ID
	 * @return string
	 */
	protected function get_parcel_carrier( $id ) {

		$carrier = '';

		try {

			$carrier = wc_infoplus()->get_api()->get_carrier_service( $id );

		} catch ( SV_WC_API_Exception $e ) {

			wc_infoplus()->log( 'Error getting carrier ' . $id );
		}

		return $carrier;
	}


}
