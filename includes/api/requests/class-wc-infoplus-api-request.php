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
class WC_Infoplus_API_Request extends SV_WC_API_JSON_Request {


	/**
	 * Ping the Infoplus API to check the credentials.
	 *
	 * @since 1.0.0
	 */
	public function ping() {

		$this->path = '/item/search?limit=1';

		$this->method = 'GET';
	}


	/**
	 * Get a carrier name.
	 *
	 * @since 1.0.0
	 * @param int $id the carrier ID
	 * @return \WC_Infoplus_API_Response
	 */
	public function get_carrier( $id ) {

		$this->path = '/carrier/' . (int) $id;

		$this->method = 'GET';
	}


	/**
	 * Get a carrier service name.
	 *
	 * @since 1.0.0
	 * @param int $id the carrier service ID
	 * @return \WC_Infoplus_API_Response
	 */
	public function get_carrier_service( $id ) {

		$this->path = '/carrierService/' . (int) $id;

		$this->method = 'GET';
	}


}
