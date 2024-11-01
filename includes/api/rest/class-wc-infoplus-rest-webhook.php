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
 * Handle the custom webhook functionality.
 *
 * @since 1.0.0
 */
class WC_Infoplus_REST_Webhook {


	/** @var string the Infoplus webhook resource **/
	protected $resource = 'order';

	/** @var string the Infoplus webhook event **/
	protected $event = 'wc_infoplus_submitted';

	/** @var string the Infoplus webhook topic **/
	protected $topic;


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// build the topic
		$this->topic = $this->resource . '.' . $this->event;

		// add the custom Infoplus event to the list of valid events
		add_filter( 'woocommerce_valid_webhook_events', array( $this, 'add_valid_event' ) );

		// add the custom webhook topic & hook
		add_filter( 'woocommerce_webhook_topic_hooks', array( $this, 'add_topic_hook' ) );

		// disable async delivery for the Infoplus webhook
		add_filter( 'woocommerce_webhook_deliver_async', array( $this, 'disable_async_delivery' ), 10, 2 );

		// adjust the Infoplus webhook for data appropriate for the Infoplus API
		add_filter( 'woocommerce_webhook_payload', array( $this, 'adjust_payload' ), 10, 4 );

		// handle the custom Infoplus webhook response
		add_action( 'woocommerce_webhook_delivery', array( $this, 'handle_response' ), 10, 5 );
	}


	/**
	 * Add the custom Infoplus event to the list of valid events.
	 *
	 * @since 1.0.0
	 * @param array $events the existing valid events
	 * @return array
	 */
	public function add_valid_event( $events ) {

		$events[] = $this->event;

		return $events;
	}


	/**
	 * Add the custom Infoplus topic & hook.
	 *
	 * @since 1.0.0
	 * @param array $topic_hooks the existing topic hooks
	 * @return array
	 */
	public function add_topic_hook( $topic_hooks ) {

		$topic_hooks[ $this->topic ] = array(
			'wc_infoplus_before_order_submitted',
		);

		return $topic_hooks;
	}


	/**
	 * Disable async delivery for the Infoplus webhook.
	 *
	 * This allows us to throw an exception based on the response, which will add an order Note
	 * to the order if something went wrong on the Infoplus side.
	 *
	 * @since 1.0.0
	 * @param bool $async whether async delivery is enabled across the board
	 * @param \WC_Webhook $webhook the webhook object
	 * @return bool
	 */
	public function disable_async_delivery( $async, $webhook ) {

		if ( $webhook->get_topic() === $this->topic ) {
			return false;
		}

		return $async;
	}


	/**
	 * Adjust the Infoplus webhook for data appropriate for the Infoplus API.
	 *
	 * @since 1.0.0
	 * @param array $payload the webhook payload
	 * @param string $resource the webhook resource
	 * @param int $resource_id the order ID
	 * @param int $webhook_id the webhook ID
	 * @return array $payload
	 */
	public function adjust_payload( $payload, $resource, $resource_id, $webhook_id ) {

		$webhook = new WC_Webhook( $webhook_id );

		if ( $webhook->get_topic() !== $this->topic ) {
			return $payload;
		}

		foreach ( $payload['order']['line_items'] as $key => $line_item ) {

			$product = wc_get_product( $line_item['product_id'] );

			if ( ! $product ) {
				continue;
			}

			$parent = ( $product->is_type( 'variation' ) ) ? SV_WC_Product_Compatibility::get_parent( $product ) : $product;

			// if the item has no SKU or isn't an Infoplus item, remove it
			if ( ! $product || ! $product->get_sku() || 'yes' !== get_post_meta( $parent->get_id(), '_wc_infoplus_fulfillment', true ) ) {
				unset( $payload['order']['line_items'][ $key ] );
			}
		}

		$payload['order']['total_line_items_quantity'] = count( $payload['order']['line_items'] );

		return $payload;
	}


	/**
	 * Handle the custom Infoplus webhook response.
	 *
	 * @since 1.0.0
	 * @throws \SV_WC_Plugin_Exception for a failed response
	 */
	public function handle_response( $http_args, $response, $duration, $order_id, $webhook_id ) {

		if ( $http_args['headers']['X-WC-Webhook-Topic'] !== $this->topic ) {
			return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			throw new SV_WC_Plugin_Exception( __( 'Order submit failed.', 'woo-infoplus-connect' ) . ' ' . wp_remote_retrieve_response_message( $response ) );
		}
	}


}
