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

class WC_Infoplus_REST_Auth_Controller extends WC_REST_Controller {


	/** @var string the route base */
	protected $rest_base = 'infoplus/auth';


	/**
	 * Register the routes for Infoplus authentication.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		// update the Infoplus authentication data
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array_merge( $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ), array(
					'url' => array(
						'required' => true,
					),
					'api_key' => array(
						'required' => true,
					),
				) ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}


	/**
	 * Check if a given request has access to update the Infoplus authentication data.
	 *
	 * @param  WP_REST_Request $request full details about the request.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {

		if ( ! wc_rest_check_manager_permissions( 'settings', 'edit' ) ) {
			return new WP_Error( 'wc_infoplus_rest_cannot_create', __( 'Sorry, you are not allowed to edit resources.', 'woo-infoplus-connect' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}


	/**
	 * Update the Infoplus authentication data.
	 *
	 * @param WP_REST_Request $request full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {

		// save the WC API key ID for later use
		update_option( 'wc_infoplus_wc_api_key_id', $this->get_requesting_key_id() );

		$url = untrailingslashit( $request['url'] );
		$url = str_replace( array( 'http://', 'https://' ), '', $url );

		// save the Infoplus API key for later use
		update_option( 'wc_infoplus_url', $url );

		if ( ! is_string( $request['api_key'] ) ) {
			return new WP_Error( 'wc_infoplus_rest_auth_invalid_api_key', __( 'Invalid API key.', 'woo-infoplus-connect' ), array( 'status' => rest_authorization_required_code() ) );
		}

		// save the Infoplus API key for later use
		update_option( 'wc_infoplus_api_key', (string) $request['api_key'] );

		$response = rest_ensure_response( array(
			'code'    => 'wc_infoplus_rest_auth_updated',
			'message' => __( 'The Infoplus authentication data was updated.', 'woo-infoplus-connect' ),
		) );

		/**
		 * Fires after the Infoplus authentication data is updated via the WC REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'wc_infoplus_rest_update_auth', $response, $request );

		$request->set_param( 'context', 'edit' );

		$response = rest_ensure_response( $response );

		return $response;
	}


	/**
	 * Get the stored API key ID for the user that initiated this request.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb the WordPress database class
	 * @return int
	 */
	private function get_requesting_key_id() {
		global $wpdb;

		$consumer_key = SV_WC_Helper::get_request( 'oauth_consumer_key' );

		if ( ! $consumer_key ) {
			$consumer_key = SV_WC_Helper::get_request( 'consumer_key' );
		}

		if ( ! $consumer_key ) {
			return '';
		}

		$consumer_key = wc_api_hash( sanitize_text_field( $consumer_key ) );

		// get the stored key data based on the current requesting key
		$key_data = $wpdb->get_row( $wpdb->prepare( "
			SELECT key_id
			FROM {$wpdb->prefix}woocommerce_api_keys
			WHERE consumer_key = %s
		", $consumer_key ) );

		return (int) $key_data->key_id;
	}


	/**
	 * Get the Infoplus authentication data schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'infoplus_auth_data',
			'type'       => 'object',
			'properties' => array(
				'url' => array(
					'description' => __( 'The Infoplus account URL.', 'woo-infoplus-connect' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
				'api_key' => array(
					'description' => __( 'The Infoplus API key for the authenticated account.', 'woo-infoplus-connect' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}


}
