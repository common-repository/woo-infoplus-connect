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

class WC_Infoplus_REST_Orders_Controller extends WC_REST_Controller {


	/** @var string the route base */
	protected $rest_base = 'orders/(?P<order_id>[\d]+)/infoplus';

	/** @var string the WC order post type */
	protected $post_type = 'shop_order';


	/**
	 * Register the routes for Infoplus orders.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		// update and delete Infoplus order data
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		// update and delete Infoplus order data
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<orderNo>[\d]+(\.[0-9][0-9][0-9])?)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'force' => array(
						'default'     => true,
						'description' => __( 'Required to be true, as resource does not support trashing.', 'woocommerce' ),
					),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}


	/**
	 * Check if a given request has access to create Infoplus orders.
	 *
	 * @param WP_REST_Request $request full details about the request.
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {

		$post = get_post( (int) $request['order_id'] );

		if ( $post && ! wc_rest_check_post_permissions( $this->post_type, 'edit', $post->ID ) ) {

			return new WP_Error( 'wc_infoplus_rest_cannot_create', __( 'Sorry, you are not allowed to create orders.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}


	/**
	 * Check if a given request has access to update an order with Infoplus data.
	 *
	 * @param  WP_REST_Request $request full details about the request.
	 * @return bool|WP_Error
	 */
	public function update_item_permissions_check( $request ) {

		$post = get_post( (int) $request['order_id'] );

		if ( $post && ! wc_rest_check_post_permissions( $this->post_type, 'edit', $post->ID ) ) {

			return new WP_Error( 'wc_infoplus_rest_cannot_update', __( 'Sorry, you are not allowed to edit this order.', 'woocommerce-infolplus' ), array(
				'status' => rest_authorization_required_code(),
			) );
		}

		return true;
	}


	/**
	 * Check if a given request has access delete an order's Infoplus data.
	 *
	 * @param  WP_REST_Request $request full details about the request.
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {

		$post = get_post( (int) $request['order_id'] );

		if ( $post && ! wc_rest_check_post_permissions( $this->post_type, 'delete', $post->ID ) ) {

			return new WP_Error( 'wc_infoplus_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'woocommerce-infolplus' ), array(
				'status' => rest_authorization_required_code(),
			) );
		}

		return true;
	}


	/**
	 * Create a new Infoplus order.
	 *
	 * @param WP_REST_Request $request full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {

		$wc_order = wc_get_order( (int) $request['order_id'] );

		if ( ! $wc_order ) {
			return new WP_Error( 'wc_infoplus_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		if ( ! wc_infoplus()->get_order_handler()->auto_update_enabled() ) {
			return new WP_Error( 'wc_infoplus_rest_order_no_auto_update', __( 'Auto-update is disabled in the Infoplus settings.', 'woocommerce-infolplus' ), array( 'status' => 500 ) );
		}

		if ( ! SV_WC_Order_Compatibility::get_meta( $wc_order, '_wc_infoplus_status', true ) ) {
			return new WP_Error( 'wc_infoplus_rest_order_not_submitted', __( 'The order has not been submitted.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		// ensure the order has been marked as successfully submitted
		SV_WC_Order_Compatibility::update_meta_data( $wc_order, '_wc_infoplus_status', 'accepted' );

		// store the data
		$result = wc_infoplus()->get_order_handler()->create_infoplus_order( $wc_order, $this->prepare_order_data( $request ) );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'wc_infoplus_rest_cannot_create', sprintf( __( 'The Infoplus order could not be created. %s', 'woo-infoplus-connect' ), $result->get_error_message() ), array( 'status' => 500 ) );
		}

		// update the WC order as needed
		wc_infoplus()->get_order_handler()->update_order( $wc_order );

		$response = rest_ensure_response( array(
			'code'    => 'wc_infoplus_rest_order_created',
			'message' => __( 'The order was created.', 'woo-infoplus-connect' ),
		) );

		/**
		 * Fires after an Infoplus order is created via the WC REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'wc_infoplus_rest_create_order', $response, $request );

		return $response;
	}


	/**
	 * Update an order's Infoplus order.
	 *
	 * @param WP_REST_Request $request full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {

		$wc_order = wc_get_order( (int) $request['order_id'] );

		if ( ! $wc_order ) {
			return new WP_Error( 'wc_infoplus_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		if ( ! wc_infoplus()->get_order_handler()->auto_update_enabled() ) {
			return new WP_Error( 'wc_infoplus_rest_order_no_auto_update', __( 'Auto-update is disabled in the Infoplus settings.', 'woocommerce-infolplus' ), array( 'status' => 500 ) );
		}

		if ( ! get_post_meta( SV_WC_Order_Compatibility::get_prop( $wc_order, 'id' ), '_wc_infoplus_status', true ) ) {
			return new WP_Error( 'wc_infoplus_rest_order_not_submitted', __( 'The order has not been submitted.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		// store the data
		$result = wc_infoplus()->get_order_handler()->update_order( $wc_order, array( 'refresh_orders' => true ) );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'wc_infoplus_rest_cannot_update', sprintf( __( 'The Infoplus order could not be updated. %s', 'woo-infoplus-connect' ), $result->get_error_message() ), array( 'status' => 500 ) );
		}

		$response = rest_ensure_response( array(
			'code'    => 'wc_infoplus_rest_order_updated',
			'message' => __( 'The order was updated.', 'woo-infoplus-connect' ),
		) );

		/**
		 * Fires after an Infoplus order is updated via the WC REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'wc_infoplus_rest_update_order', $response, $request );

		return $response;
	}


	/**
	 * Delete an order's Infoplus data.
	 *
	 * @param WP_REST_Request $request full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {

		$force = isset( $request['force'] ) ? (bool) $request['force'] : false;

		// We don't support trashing for this type, error out.
		if ( ! $force ) {
			return new WP_Error( 'wc_infoplus_rest_trash_not_supported', __( 'Infoplus orders do not support trashing.', 'woocommerce-infolplus' ), array( 'status' => 501 ) );
		}

		$wc_order = wc_get_order( (int) $request['order_id'] );

		if ( ! $wc_order ) {
			return new WP_Error( 'wc_infoplus_rest_order_invalid_id', __( 'Invalid order ID.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		if ( ! get_post_meta( SV_WC_Order_Compatibility::get_prop( $wc_order, 'id' ), '_wc_infoplus_status', true ) ) {
			return new WP_Error( 'wc_infoplus_rest_order_not_submitted', __( 'The order has not been submitted.', 'woocommerce-infolplus' ), array( 'status' => 404 ) );
		}

		$result = wc_infoplus()->get_order_handler()->delete_infoplus_order( $wc_order, $request['orderNo'] );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'wc_infoplus_rest_cannot_delete', sprintf( __( 'The Infoplus order could not be deleted. %s', 'woo-infoplus-connect' ), $result->get_error_message() ), array( 'status' => 500 ) );
		}

		$response = rest_ensure_response( array(
			'code'    => 'wc_infoplus_rest_order_deleted',
			'message' => __( 'The Infoplus order was deleted.', 'woo-infoplus-connect' ),
		) );

		/**
		 * Fires after an Infoplus order is deleted via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'wc_infoplus_rest_delete_order', $response, $request );

		return $response;
	}


	/**
	 * Prepare the request's order data for storage.
	 *
	 * @since 1.0.0
	 * @param array $data {
	 *     The Infoplus API data.
	 *
	 *     @type string $orderNo the order number
	 *     @type string $status  the order status
	 *     @type array  $lineItems {
	 *         The order line items
	 *
	 *         @type string $sku the item SKU
	 *         @type int    $orderedQty the ordered quantity
	 *     }
	 * }
	 * @return array
	 */
	protected function prepare_order_data( $data ) {

		$data = array(
			'number' => str_replace( '.000', '', (string) $data['orderNo'] ),
			'status' => $data['status'],
			'items'  => isset( $data['lineItems'] ) && is_array( $data['lineItems'] ) ? $data['lineItems'] : array(),
		);

		// set the line items
		foreach ( $data['items'] as $key => $item ) {

			unset( $data['items'][ $key ] );

			if ( ! isset( $item['sku'] ) || ! isset( $item['orderedQty'] ) ) {
				continue;
			}

			$data['items'][] = array(
				'sku'      => $item['sku'],
				'quantity' => $item['orderedQty'],
			);
		}

		return $data;
	}


	/**
	 * Get the Infoplus orders schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'infoplus_order',
			'type'       => 'object',
			'properties' => array(
				'orderNo' => array(
					'description' => __( 'The unique order number.', 'woo-infoplus-connect' ),
					'type'        => 'float',
					'required'    => true,
					'context'     => array( 'edit' ),
				),
				'status' => array(
					'description' => __( "The current status of the order.", 'woo-infoplus-connect' ),
					'type'        => 'string',
					'required'    => true,
					'context'     => array( 'edit' ),
				),
				'lineItems' => array(
					'description' => __( 'Line items data.', 'woo-infoplus-connect' ),
					'type'        => 'array',
					'required'    => true,
					'context'     => array( 'edit' ),
					'items' => array(
						'type'        => 'object',
						'properties'  => array(
							'sku' => array(
								'description' => __( 'Product SKU.', 'woo-infoplus-connect' ),
								'type'        => 'string',
								'required'    => true,
								'context'     => array( 'edit' ),
							),
							'orderedQty' => array(
								'description' => __( 'Quantity ordered.', 'woocommerce' ),
								'type'        => 'integer',
								'required'    => true,
								'context'     => array( 'edit' ),
							),
						),
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}


}
