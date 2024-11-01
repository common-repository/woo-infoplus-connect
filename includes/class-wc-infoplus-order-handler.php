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
 * Handle the order-related actions.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Order_Handler {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add Infoplus fulfillment status meta to order line items
		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			add_action( 'woocommerce_new_order_item', array( $this, 'add_order_item_meta' ), 10, 2 );
		} else {
			add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta_legacy' ), 10, 2 );
		}

		if ( $this->auto_submit_enabled() ) {

			// submit to Infoplus when payment is complete
			add_action( 'woocommerce_payment_complete', array( $this, 'submit_paid_order' ) );

			// submit to Infoplus when the status is changed
			add_action( 'woocommerce_order_status_changed', array( $this, 'submit_paid_order' ), 10, 2 );
		}

		// submit to Infoplus using the manual admin action
		add_action( 'woocommerce_order_action_wc_infoplus_submit', array( $this, 'submit_order' ) );

		// add shipment information to the Order Completed email
		add_action( 'woocommerce_email_order_details', array( $this, 'add_completed_email_shipments' ), 30, 4 );
	}


	/**
	 * Add Infoplus fulfillment status meta to order line items.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param int $item_id the order item ID
	 * @param \WC_Order_Item $item order item object
	 */
	public function add_order_item_meta( $item_id, $item ) {

		if ( ! SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() || ! $item instanceof WC_Order_Item_Product ) {
			return;
		}

		// always get the parent product
		$product = wc_get_product( $item->get_product_id() );

		if ( ! $product ) {
			return;
		}

		$managed = ( $product->get_meta( '_wc_infoplus_fulfillment' ) ) ? $product->get_meta( '_wc_infoplus_fulfillment' ) : 'no';

		$item->update_meta_data( '_wc_infoplus_fulfillment', $managed );
		$item->save();
	}


	/**
	 * Add Infoplus fulfillment status meta to order line items.
	 *
	 * For WC 2.6 compatibility
	 *
	 * @since 1.0.0
	 * @param int $item_id the order item ID
	 * @param array $values the order item data
	 */
	public function add_order_item_meta_legacy( $item_id, $values ) {

		$product = $values['data'];

		if ( $product->is_type( 'variation' ) ) {
			$product = SV_WC_Product_Compatibility::get_parent( $product );
		}

		$managed = ( get_post_meta( $product->get_id(), '_wc_infoplus_fulfillment', true ) ) ? get_post_meta( $product->get_id(), '_wc_infoplus_fulfillment', true ) : 'no';

		wc_add_order_item_meta( $item_id, '_wc_infoplus_fulfillment', $managed );
	}


	/** Submittal methods ******************************************************/


	/**
	 * Ping Infoplus to get a WooCommerce order's data.
	 *
	 * @since 1.0.0
	 * @param int $order_id the order ID
	 * @param string $old_status Optional. The previous order status if the status was switched
	 */
	public function submit_paid_order( $order_id, $old_status = '' ) {

		$order = wc_get_order( $order_id );

		// don't re-submit orders
		if ( ! $this->is_order_ready( $order, $old_status ) ) {
			return;
		}

		$this->submit_order( $order );
	}


	/**
	 * Ping Infoplus to get a WooCommerce order's data.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order the order object
	 */
	public function submit_order( WC_Order $order ) {

		try {

			/**
			 * Fire before an order is submitted to Infoplus.
			 *
			 * This hook triggers the `order.wc_infoplus_submitted` webhook, and a `SV_WC_Plugin_Exception`
			 * can be thrown to halt the process.
			 *
			 * @since 1.0.0
			 * @param int $order_id the order ID
			 */
			do_action( 'wc_infoplus_before_order_submitted', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

			// mark the order as successfully submitted
			update_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_infoplus_status', 'submitted' );

			$order->add_order_note( __( 'Order submitted to Infoplus.', 'woo-infoplus-connect' ) );

			/**
			 * Fire after an order is submitted to Infoplus.
			 *
			 * @since 1.0.0
			 * @param int $order_id the order ID
			 */
			do_action( 'wc_infoplus_after_order_submitted', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		} catch ( SV_WC_Plugin_Exception $e ) {

			$order->add_order_note( '<strong>' . __( 'Infoplus Error', 'woo-infoplus-connect' ) . ':</strong> ' . $e->getMessage() );

			/**
			 * Fire if there was an error submitting an order to Infoplus.
			 *
			 * @since 1.0.0
			 * @param int $order_id the order ID
			 */
			do_action( 'wc_infoplus_after_order_submit_error', SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );
		}
	}


	/**
	 * Determine if an order is ready to be submitted to Infoplus.
	 *
	 * The primary factor is if the order hasn't already been submitted and is considered "paid".
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order the order object
	 * @param string $old_status Optional. The previous order status if the status was switched
	 * @return bool
	 */
	public function is_order_ready( WC_Order $order, $old_status = '' ) {

		// assume it's not ready
		$is_ready = false;

		// only continue checking if the order hasn't already been submitted
		if ( ! get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_infoplus_status', true ) ) {

			$status = $order->get_status();

			/**
			 * Filter the order statuses that allow order submitting.
			 *
			 * @since 1.0.0
			 * @param array $valid_statuses the valid statuses.
			 */
			$valid_statuses = apply_filters( 'wc_infoplus_order_ready_statuses', array(
				'processing',
				'completed',
			) );

			$is_ready = in_array( $status, $valid_statuses );

			// if Order Status Manager is active then check the status' paid property
			if ( class_exists( 'WC_Order_Status_Manager_Order_Status' ) && ! $is_ready ) {

				$status = new WC_Order_Status_Manager_Order_Status( $status );

				$is_ready = ( $status->get_id() > 0 && ! $status->is_core_status() && $status->is_paid() );
			}

			// if the order's status was just switched, check if the previous status is valid
			if ( $old_status ) {

				/**
				 * Filter the statuses that allow order submitting when switched from them.
				 *
				 * @since 1.0.0
				 * @param array $statuses the valid statuses.
				 */
				$valid_old_statuses = apply_filters( 'wc_infoplus_order_ready_from_statuses', array(
					'on-hold',
					'failed',
				) );

				$is_ready = $is_ready && in_array( $old_status, $valid_old_statuses );
			}
		}

		/**
		 * Filter whether an order is ready to be submitted to Infoplus.
		 *
		 * @since 1.0.0
		 * @param bool $is_ready
		 * @param \WC_Order $order order object
		 */
		return (bool) apply_filters( 'wc_infoplus_order_is_ready', $is_ready, $order );
	}


	/**
	 * Determine if orders are set to auto-submit.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function auto_submit_enabled() {

		/**
		 * Filters whether new WooCommerce orders should be automatically submitted to Infoplus.
		 *
		 * @since 1.0.0
		 * @param bool $auto_submit
		 */
		return (bool) apply_filters( 'wc_infoplus_order_auto_submit', true );
	}


	/** Update methods ******************************************************/


	/**
	 * Update an order with Infoplus data.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order the order object
	 * @return \WC_Order|WP_Error the order object on success or \WP_Error on failure
	 */
	public function update_order( WC_Order $order, $args = array() ) {

		$order = wc_get_order( SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

		$args = wp_parse_args( $args, array(
			'refresh_orders' => false,
		) );

		// pull fresh order data from the API if desired
		if ( $args['refresh_orders'] ) {

			try {

				$response = wc_infoplus()->get_api()->get_orders( SV_WC_Order_Compatibility::get_prop( $order, 'id' ) );

				$this->update_infoplus_orders( $order, $response->get_orders() );

 			} catch ( SV_WC_API_Exception $e ) {

				wc_infoplus()->log( $e->getMessage() );

				return new WP_Error( 'wc_infoplus_cannot_update_order', __( 'Could not update order.', 'woo-infoplus-connect' ) . ' ' . $e->getMessage() );
			}
		}

		$infoplus_orders = $this->get_infoplus_orders( $order );

		$shipped_orders = 0;

		foreach ( $infoplus_orders as $infoplus_order ) {

			if ( $infoplus_order->is_shipped() ) {
				$shipped_orders++;
			}
		}

		/**
		 * Filters whether WooCommerce orders should auto-complete when all Infoplus orders have shipped.
		 *
		 * @since 1.0.0
		 * @param bool $auto_complete
		 */
		$auto_complete = apply_filters( 'wc_infoplus_order_auto_complete', ( 'yes' === get_option( 'wc_infoplus_order_update_auto_complete' ) ) );

		// if all Infoplus orders are considered shipped and set to do so, mark the order as completed
		if ( count( $infoplus_orders ) > 0 && $shipped_orders === count( $infoplus_orders ) && $auto_complete ) {
			$order->update_status( 'completed', __( 'All Infoplus orders have shipped.', 'woo-infoplus-connect' ) );
		}

		return $order;
	}


	public function update_all() {

		/**
		 * Fire before all orders have been updated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_infoplus_before_orders_updated' );

		$updated_orders = array();

		$posts = new WP_Query( array(
			'post_type'   => 'shop_order',
			'post_status' => 'any',
			'meta_query'  => array(
				array(
					'key'     => '_wc_infoplus_status',
					'value'   => 'accepted',
					'compare' => '=',
				),
			),
			'nopaging' => true,
		) );

		foreach ( $posts->posts as $post ) {

			$order = wc_get_order( $post->ID );

			if ( ! $order ) {
				continue;
			}

			if ( $order->has_status( 'trashed' ) ) {
				continue;
			}

			// save the order details pre-update so we can compare
			$current_status     = $order->get_status();
			$current_order_data = get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_infoplus_orders', true );

			$order = $this->update_order( $order, array( 'refresh_orders' => true ) );

			if ( is_wp_error( $order ) ) {
				continue;
			}

			$updated_status     = $order->get_status();
			$updated_order_data = get_post_meta( SV_WC_Order_Compatibility::get_prop( $order, 'id' ), '_wc_infoplus_orders', true );

			// compare the old and new details and add to the array of updated orders if a change was made
			if ( $updated_status !== $current_status || $updated_order_data !== $current_order_data ) {
				$updated_orders[] = SV_WC_Order_Compatibility::get_prop( $order, 'id' );
			}
		}

		/**
		 * Fire after all orders have been updated.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wc_infoplus_after_orders_updated' );

		return $updated_orders;
	}


	/**
	 * Determine if orders are set to auto-update.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function auto_update_enabled() {

		/**
		 * Filters whether WooCommerce orders should be automatically updated by Infoplus.
		 *
		 * @since 1.0.0
		 * @param bool $auto_update
		 */
		return (bool) apply_filters( 'wc_infoplus_order_auto_update', true );
	}


	/** Infoplus order CRUD methods ******************************************************/


	/**
	 * Add an Infoplus order to a given WC order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @param array $data the Infoplus order data. see `WC_Infoplus_Order` for params
	 * @return bool|WP_Error
	 */
	public function create_infoplus_order( WC_Order $wc_order, $data ) {

		$orders = $this->get_infoplus_orders( $wc_order );

		$order = new WC_Infoplus_Order( $data );

		if ( isset( $orders[ $order->get_id() ] ) ) {
			return new WP_Error( 'wc_infoplus_order_exists', __( 'Order already exists.', 'woo-infoplus-connect' ) );
		}

		$orders[ $order->get_id() ] = $order;

		return $this->update_infoplus_orders( $wc_order, $orders );
	}


	/**
	 * Update an Infoplus order for a given WC order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @param array|WC_Infoplus_Order $data the Infoplus order data. see `WC_Infoplus_Order` for params
	 * @return bool|WP_Error
	 */
	public function update_infoplus_order( WC_Order $wc_order, $data ) {

		$orders = $this->get_infoplus_orders( $wc_order );

		if ( $data instanceof WC_Infoplus_Order ) {
			$order = $data;
		} else {
			$order = new WC_Infoplus_Order( $data );
		}

		if ( isset( $orders[ $order->get_id() ] ) ) {
			$orders[ $order->get_id() ] = $order;
		} else {
			return new WP_Error( 'wc_infoplus_order_not_found', __( 'Order not found.', 'woo-infoplus-connect' ) );
		}

		return $this->update_infoplus_orders( $wc_order, $orders );
	}


	/**
	 * Delete an Infoplus order for a given WC order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @param string $order_number the Infoplus order number
	 * @return bool|WP_Error
	 */
	public function delete_infoplus_order( WC_Order $wc_order, $order_number ) {

		$orders = $this->get_infoplus_orders( $wc_order );

		if ( ! isset( $orders[ $order_number ] ) ) {
			return new WP_Error( 'wc_infoplus_order_not_found', __( 'Order not found.', 'woo-infoplus-connect' ) );
		}

		unset( $orders[ $order_number ] );

		return $this->update_infoplus_orders( $wc_order, $orders );
	}


	/**
	 * Get Infoplus orders for a given WC order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @return array
	 */
	public function get_infoplus_orders( WC_Order $wc_order ) {

		$orders        = array();
		$stored_orders = SV_WC_Order_Compatibility::get_meta( $wc_order, '_wc_infoplus_orders', true );

		if ( ! $stored_orders ) {
			$stored_orders = array();
		}

		foreach ( $stored_orders as $id => $data ) {
			$orders[ $id ] = new WC_Infoplus_Order( $data );
		}

		return $orders;
	}


	/**
	 * Update Infoplus orders for a given WC order.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order order object
	 * @param array $orders `WC_Infoplus_Order` objects
	 * @return bool
	 */
	public function update_infoplus_orders( WC_Order $wc_order, $orders ) {

		$data = array();

		foreach ( $orders as $order ) {

			// update the parcel data from the API
			$order->update_parcels();

			$data[ $order->get_id() ] = $order->get_data();
		}

		ksort( $data );

		return SV_WC_Order_Compatibility::update_meta_data( $wc_order, '_wc_infoplus_orders', $data );
	}


	/**
	 * Add shipment information to the Order Completed email.
	 *
	 * @since 1.0.0
	 * @param \WC_Order $order the order object
	 * @param bool $sent_to_admin whether the email was sent to the admin
	 * @param bool $plain_text whether the email is plain text or HTML
	 * @param \WC_Email $email the email object
	 * @return string
	 */
	public function add_completed_email_shipments( $order, $sent_to_admin, $plain_text, $email ) {

		// only on the Order Completed email
		if ( 'customer_completed_order' !== $email->id ) {
			return;
		}

		$shipments = array();

		$infoplus_orders = wc_infoplus()->get_order_handler()->get_infoplus_orders( $order );

		foreach ( $infoplus_orders as $infoplus_order ) {
			$shipments = array_merge( $shipments, $infoplus_order->get_parcels() );
		}

		// no shipments? no table
		if ( empty( $shipments ) ) {
			return;
		}

		if ( $plain_text ) {
			wc_get_template( 'emails/plain/email-order-infoplus-shipments.php', array( 'shipments' => $shipments ) );
		} else {
			wc_get_template( 'emails/email-order-infoplus-shipments.php', array( 'shipments' => $shipments ) );
		}
	}


}
