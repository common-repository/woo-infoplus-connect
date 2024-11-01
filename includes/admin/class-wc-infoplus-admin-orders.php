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
 * @package     WC_Infoplus\Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Set up the orders admin.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Admin_Orders {


	/**
	 * Construct the class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add the order action for submitting to Infoplus manually
		add_action( 'woocommerce_order_actions', array( $this, 'add_manual_action' ) );

		// add the bulk order action for submitting to Infoplus manually
		add_action( 'admin_footer-edit.php', array( $this, 'add_bulk_action' ) );

		// process the bulk order action for submitting to Infoplus manually
		add_action( 'load-edit.php', array( $this, 'process_bulk_action' ) );

		// add the Infoplus fulfillment status meta to hidden meta for order item display
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'set_hidden_order_item_meta' ) );

		// add the meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 31 );
	}


	/**
	 * Add the order action for submitting to Infoplus manually.
	 *
	 * @since 1.0.0
	 * @param array $actions The available order actions.
	 * @return array $actions
	 */
	public function add_manual_action( $actions ) {

		$order = wc_get_order( get_the_ID() );

		// add the action if the order is ready for sending
		if ( wc_infoplus()->get_order_handler()->is_order_ready( $order ) ) {
			$actions['wc_infoplus_submit'] = __( 'Submit to Infoplus', 'woo-infoplus-connect' );
		}

		return $actions;
	}


	/**
	 * Add the bulk order action for submitting to Infoplus manually.
	 *
	 * @since 1.0.0
	 * @global string $post_type the current post type
	 * @global string $post_status the current post status
	 */
	public function add_bulk_action() {
 		global $post_type, $post_status;

 		if ( $post_type === 'shop_order' && $post_status !== 'trash' ) { ?>

 				<script type="text/javascript">
 					jQuery( document ).ready( function ( $ ) {
 						if ( 0 == $( 'select[name^=action] option[value=wc_infoplus_submit]' ).size() ) {
 							$( 'select[name^=action]' ).append(
 								$( '<option>' ).val( '<?php echo esc_js( 'wc_infoplus_submit' ); ?>' ).text( '<?php echo esc_js( __( 'Submit to Infoplus', 'woo-infoplus-connect' ) ); ?>' )
 							);
 						}
 					});
 				</script>

 		<?php }
 	}


	/**
	 * Process the bulk order action for submitting to Infoplus manually.
	 *
	 * @since 1.0.0
	 * @global string $typenow the post type of the current screen
	 */
	public function process_bulk_action() {
		global $typenow;

		if ( 'shop_order' == $typenow ) {

			// get the action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			// bail if not submitting an order
			if ( 'wc_infoplus_submit' !== $action ) {
				return;
			}

			// security check
			check_admin_referer( 'bulk-posts' );

			// make sure order IDs are submitted
			if ( isset( $_REQUEST['post'] ) ) {
				$order_ids = array_map( 'absint', $_REQUEST['post'] );
			}

			// return if there are no orders to export
			if ( empty( $order_ids ) ) {
				return;
			}

			// give ourselves an unlimited timeout if possible
			@set_time_limit( 0 );

			foreach ( $order_ids as $order_id ) {

				$order = wc_get_order( $order_id );

				if ( wc_infoplus()->get_order_handler()->is_order_ready( $order ) ) {
					wc_infoplus()->get_order_handler()->submit_order( $order );
				}
			}
		}
	}


	/**
	 * Add the Infoplus fulfillment status meta to hidden meta for order item display.
	 *
	 * @since 1.0.0
	 * @param array $meta_keys the meta keys to be hidden
	 * @return array
	 */
	public function set_hidden_order_item_meta( $meta_keys ) {

		$meta_keys[] = '_wc_infoplus_fulfillment';

		return $meta_keys;
	}


	/**
	 * Add the meta boxes.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {

		// "Infoplus Orders" meta box
		add_meta_box(
			'wc_infoplus_orders',
			__( 'Infoplus Orders', 'woo-infoplus-connect' ),
			array( $this, 'display_orders_meta_box'),
			'shop_order',
			'normal',
			'high'
		);
	}


	/**
	 * Display the "Infoplus Orders" meta box.
	 *
	 * @since 1.0.0
	 * @param \WP_Post the current post object
	 */
	public function display_orders_meta_box( $post ) {

		$order = wc_get_order( $post->ID );

		if ( ! $order ) {
			return;
		}

		$infoplus_status = get_post_meta( $post->ID, '_wc_infoplus_status', true );

		if ( 'accepted' === $infoplus_status ) {

			if ( $orders = wc_infoplus()->get_order_handler()->get_infoplus_orders( $order ) ) {

				include( wc_infoplus()->get_plugin_path() . '/includes/admin/views/html-orders-meta-box-table.php' );

			} else {

				echo '<p class="no-orders">' . esc_html__( 'No Infoplus orders associated with this order.', 'woo-infoplus-connect' ) . '</p>';

			}

		} elseif ( 'submitted' === $infoplus_status ) {

			echo '<p class="no-orders">' . esc_html__( 'Orders will appear here once this order has been processed by Infoplus.', 'woo-infoplus-connect' ) . '</p>';

		} else {

			echo '<p class="no-orders">' . esc_html__( 'Orders will appear here once this order has been submitted to Infoplus.', 'woo-infoplus-connect' ) . '</p>';
		}
	}


}
