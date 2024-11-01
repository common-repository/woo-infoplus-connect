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
 * The Infoplus API.
 *
 * @since 1.0.0
 */
class WC_Infoplus_API extends SV_WC_API_Base {


	/**
	 * Construct the API.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $url, $key ) {

		$this->request_uri = 'https://' . $url . '/infoplus-wms/api/v1.0';

		$this->set_request_content_type_header( 'application/json' );
		$this->set_request_accept_header( 'application/json' );

		$this->set_request_header( 'API-Key', $key );
	}


	/**
	 * Ping the Infoplus API to check the credentials.
	 *
	 * @since 1.0.0
	 */
	public function ping() {

		$request = $this->get_new_request();

		$request->ping();

		return $this->perform_request( $request );
	}


	/**
	 * Get an Infoplus order.
	 *
	 * @since 1.0.0
	 * @param float $order_number the Infoplus order number.
	 * @return \WC_Infoplus_API_Order_Response
	 */
	public function get_order( $order_number ) {

		$request = $this->get_new_request( 'order' );

		$request->get_order( $order_number );

		return $this->perform_request( $request );
	}


	/**
	 * Get all Infoplus orders associated with a WC order.
	 *
	 * @since 1.0.0
	 * @param int $order_id the WC order ID
	 * @return \WC_Infoplus_API_Orders_Response
	 */
	public function get_orders( $order_id ) {

		$request = $this->get_new_request( 'orders' );

		$request->get_orders( $order_id );

		return $this->perform_request( $request );
	}


	/**
	 * Get an order's parcels.
	 *
	 * @since 1.0.0
	 * @param float $order_number the Infoplus order number.
	 * @return \WC_Infoplus_API_Response
	 */
	public function get_order_parcels( $order_number ) {

		$request = $this->get_new_request( 'parcels' );

		$request->get_parcels( $order_number );

		return $this->perform_request( $request );
	}


	/**
	 * Get a carrier name.
	 *
	 * @since 1.0.0
	 * @param int $id the carrier ID
	 * @return \WC_Infoplus_API_Response
	 */
	public function get_carrier( $id ) {

		$request = $this->get_new_request();

		$request->get_carrier( $id );

		$response = $this->perform_request( $request );

		return $response->get_carrier();
	}


	/**
	 * Get a carrier service name.
	 *
	 * @since 1.0.0
	 * @param int $id the carrier service ID
	 * @return \WC_Infoplus_API_Response
	 */
	public function get_carrier_service( $id ) {

		$request = $this->get_new_request();

		$request->get_carrier_service( $id );

		$response = $this->perform_request( $request );

		return $response->get_carrier_service();
	}


	/**
	 * Maybe error out if the API was unreachable.
	 *
	 * @since 1.0.0
	 * @throws \SV_WC_API_Exception
	 */
	protected function do_pre_parse_response_validation() {

		if ( SV_WC_Helper::str_starts_with( (string) $this->get_response_code(), '5' ) ) {
			throw new SV_WC_API_Exception( 'The Infoplus API could not be reached' );
		}

		return true;
	}


	/**
	 * Check for errors in the response.
	 *
	 * @since 1.0.0
	 */
	protected function do_post_parse_response_validation() {

		$response = $this->get_response();

		if ( $response->has_errors() ) {
			throw new SV_WC_API_Exception( implode( '. ', $response->get_errors() ) );
		}

		return true;
	}


	/**
	 * Get the API request object.
	 *
	 * @since 1.0.0
	 * @param string $type the request type.
	 * @return \WC_Infoplus_API_Request
	 */
	protected function get_new_request( $type = '' ) {

		switch ( $type ) {

			case 'order':
				$this->set_response_handler( 'WC_Infoplus_API_Order_Response' );
				return new WC_Infoplus_API_Order_Request;
			break;

			case 'orders':
				$this->set_response_handler( 'WC_Infoplus_API_Orders_Response' );
				return new WC_Infoplus_API_Orders_Request;
			break;

			case 'parcels':
				$this->set_response_handler( 'WC_Infoplus_API_Parcels_Response' );
				return new WC_Infoplus_API_Parcels_Request;
			break;

			default:
				$this->set_response_handler( 'WC_Infoplus_API_Response' );
				return new WC_Infoplus_API_Request;
		}
	}


	/**
	 * Get sanitized request headers suitable for logging, stripped of any confidential information.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_sanitized_request_headers() {

		$headers = $this->get_request_headers();

		if ( ! empty( $headers['API-Key'] ) ) {
			$headers['API-Key'] = str_repeat( '*', strlen( $headers['API-Key'] ) );
		}

		return $headers;
	}


	/**
	 * Get the plugin instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus
	 */
	protected function get_plugin() {

		return wc_infoplus();
	}


}
