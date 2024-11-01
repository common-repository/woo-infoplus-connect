<?php
/**
 * Plugin Name: Infoplus Connect for WooCommerce
 * Plugin URI: http://wordpress.org/plugins/woo-infoplus-connect/
 * Description: Connects your store to Infoplus to sync inventory, orders, and shipment tracking information for optimized order fulfillment.
 * Author: SkyVerge & Infoplus
 * Author URI: http://skyverge.com/
 * Version: 1.0.1
 * Text Domain: woo-infoplus-connect
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2016-2017, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC_Infoplus
 * @author    SkyVerge
 * @copyright Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

// WC active check
if ( ! SV_WC_Framework_Bootstrap::is_woocommerce_active() ) {
	return;
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.3', __( 'WooCommerce Infoplus Connect', 'woo-infoplus-connect' ), __FILE__, 'init_woocommerce_infoplus', array(
	'minimum_wc_version'   => '2.6.0',
	'minimum_wp_version'   => '4.4',
	'backwards_compatible' => '4.4.0',
) );

function init_woocommerce_infoplus() {

/**
 * Define the main Infoplus plugin class.
 *
 * @since 1.0.0
 */
class WC_Infoplus extends SV_WC_Plugin {


	/** version number */
	const VERSION = '1.0.1';

	/** the plugin identifier */
	const PLUGIN_ID = 'infoplus';

	/** @var WC_Infoplus single instance of this plugin */
	protected static $instance;

	/** @var \WC_Infoplus_REST_Webhook the custom webhook handler instance **/
	protected $rest_webhook;

	/** @var \WC_Infoplus_Order_Handler the order handler instance **/
	protected $order_handler;

	/** @var \WC_Infoplus_AJAX the AJAX instance **/
	protected $ajax;

	/** @var \WC_Infoplus_Admin the admin instance **/
	protected $admin;

	/** @var \WC_Infoplus_Frontend the front-end instance **/
	protected $frontend;

	/** @var \WC_Infoplus_API the API instance **/
	protected $api;


	/**
	 * Setup main plugin class.
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::__construct()
	 */
	public function __construct() {

		parent::__construct( self::PLUGIN_ID, self::VERSION, array(
			'text_domain' => 'woo-infoplus-connect',
		) );

		// include required files
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ) );

		// register the WC REST API routes
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

		// set an Infoplus webhook's API version when created using the WC REST API
		add_filter( 'woocommerce_rest_insert_shop_webhook', array( $this, 'set_webhook_api_version' ), 10, 3 );

		// add the Infoplus webhook topic to the list of options
		add_filter( 'woocommerce_webhook_topics', array( $this, 'add_infoplus_webhook_topic' ) );

		// add a product's Infoplus fulfillment status as a REST API field
		add_filter( 'woocommerce_rest_prepare_product', array( $this, 'add_rest_product_fulfillment_status' ), 10 );

		// add an order line item's Infoplus fulfillment status as a REST API field
		add_filter( 'woocommerce_rest_prepare_shop_order', array( $this, 'add_rest_order_item_fulfillment_status' ), 10 );

		// locate the WooCommerce template files from our templates directory
		add_filter( 'woocommerce_locate_template', array( $this, 'locate_template' ), 20, 3 );
	}


	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 */
	public function includes() {

		require_once( $this->get_plugin_path() . '/includes/class-wc-infoplus-order.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-infoplus-order-item.php' );
		require_once( $this->get_plugin_path() . '/includes/class-wc-infoplus-order-parcel.php' );

		// REST API includes
		require_once( $this->get_plugin_path() . '/includes/api/rest/class-wc-infoplus-rest-orders-controller.php' );
		require_once( $this->get_plugin_path() . '/includes/api/rest/class-wc-infoplus-rest-auth-controller.php' );

		$this->rest_webhook = $this->load_class( '/includes/api/rest/class-wc-infoplus-rest-webhook.php', 'WC_Infoplus_REST_Webhook' );

		$this->order_handler = $this->load_class( '/includes/class-wc-infoplus-order-handler.php', 'WC_Infoplus_Order_Handler' );

		if ( is_ajax() ) {
			$this->ajax = $this->load_class( '/includes/class-wc-infoplus-ajax.php', 'WC_Infoplus_AJAX' );
		} elseif ( is_admin() ) {
			$this->admin = $this->load_class( '/includes/admin/class-wc-infoplus-admin.php', 'WC_Infoplus_Admin' );
		} else {
			$this->frontend = $this->load_class( '/includes/frontend/class-wc-infoplus-frontend.php', 'WC_Infoplus_Frontend' );
		}
	}


	/**
	 * Get the custom webhook handler instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_REST_Webhook
	 */
	public function get_rest_webhook_instance() {

		return $this->rest_webhook;
	}


	/**
	 * Get the order handler instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_Order_Handler
	 */
	public function get_order_handler() {

		return $this->order_handler;
	}


	/**
	 * Get the AJAX instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_AJAX
	 */
	public function get_ajax_instance() {

		return $this->ajax;
	}


	/**
	 * Get the admin instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_Admin
	 */
	public function get_admin_instance() {

		return $this->admin;
	}


	/**
	 * Get the front-end instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_Frontend
	 */
	public function get_frontend_instance() {

		return $this->frontend;
	}


	/**
	 * Register the WC REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {

		$controllers = array(
			'WC_Infoplus_REST_Orders_Controller',
			'WC_Infoplus_REST_Auth_Controller',
		);

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}


	/**
	 * Sets an Infoplus webhook's API version when created using the WC REST API.
	 *
	 * Infoplus's integration was built for WC 2.6's order webhook payload.
	 * This is considered legacy in WC 3.0 so whenever Infoplus connects to a
	 * new site, ensure the newly created webhook uses the legacy payload.
	 *
	 * TODO: Eventually this can be removed when the Infoplus side is updated to
	 * use the v2 REST API. {CW 2017-04-14}
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post the webhook's post object
	 * @param array $request the API request data that generated the webhook
	 * @param bool $creating whether this is a new webhook or one being updated
	 */
	public function set_webhook_api_version( $post, $request, $creating ) {

		// if this isn't a new webhook, bail
		if ( ! $creating ) {
			return;
		}

		// if this is the infoplus webhook, set the API version for backwards compatibility
		if ( 'order.wc_infoplus_submitted' === $request['topic'] ) {

			$api_version = ! empty( $request['api_version'] ) ? $request['api_version'] : 'legacy_v3';

			$versions = array(
				'wp_api_v2',
				'wp_api_v1',
				'legacy_v3',
			);

			if ( ! in_array( $api_version, $versions, true ) ) {
				$api_version = 'wp_api_v2';
			}

			update_post_meta( $post->ID, '_api_version', $api_version );
		}
	}


	/**
	 * Adds the Infoplus webhook topic to the list of options.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param array $topics existing webhook topics
	 *
	 * @return array $topics
	 */
	public function add_infoplus_webhook_topic( $topics ) {

		$topics['order.wc_infoplus_submitted'] = __( 'Order ready for Infoplus', 'woo-infoplus-connect' );

		return $topics;
	}


	/**
	 * Add a product's Infoplus fulfillment status as a REST API field.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Response $response the REST API response for a single product.
	 * @return \WP_REST_Response
	 */
	public function add_rest_product_fulfillment_status( $response ) {

		$product_data = $response->get_data();

		// get the parent product's ID if this is a variation
		$product_id = ( $product_data['parent_id'] ) ? $product_data['parent_id'] : $product_data['id'];

		$product_data['infoplus_fulfillment'] = ( 'yes' === get_post_meta( $product_id, '_wc_infoplus_fulfillment', true ) && $product_data['sku'] );

		$response->data = $product_data;

		return $response;
	}


	/**
	 * Add an order line item's Infoplus fulfillment status as a REST API field.
	 *
	 * @since 1.0.0
	 * @param \WP_REST_Response $response the REST API response for a single order.
	 * @return \WP_REST_Response
	 */
	public function add_rest_order_item_fulfillment_status( $response ) {

		$order_data = $response->get_data();

		foreach ( $order_data['line_items'] as $key => $item ) {

			$order_data['line_items'][ $key ]['infoplus_fulfillment'] = ( 'yes' === wc_get_order_item_meta( $item['id'], '_wc_infoplus_fulfillment', true ) && $item['sku'] );
		}

		$response->data = $order_data;

		return $response;
	}


	/**
	 * Logs an entry to the debug log.
	 *
	 * @since 1.0.0
	 * @param string $message the log message
	 * @param null $_ unused
	 */
	public function log( $message, $_ = null ) {

		if ( apply_filters( 'wc_infoplus_debug_log_enabled', ( 'yes' === get_option( 'wc_infoplus_debug_mode' ) ) ) ) {
			parent::log( $message );
		}
	}


	/**
	 * Get the API instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_API
	 */
	public function get_api() {

		if ( ! is_object( $this->api ) ) {

			// base
			require_once( $this->get_plugin_path() . '/includes/api/class-wc-infoplus-api.php' );

			// requests
			require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-infoplus-api-request.php' );
			require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-infoplus-api-order-request.php' );
			require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-infoplus-api-orders-request.php' );
			require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-infoplus-api-parcels-request.php' );

			// responses
			require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-infoplus-api-response.php' );
			require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-infoplus-api-order-response.php' );
			require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-infoplus-api-orders-response.php' );
			require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-infoplus-api-parcels-response.php' );

			$url = get_option( 'wc_infoplus_url' );
			$key = get_option( 'wc_infoplus_api_key', '' );

			$this->api = new WC_Infoplus_API( $url, $key );
		}

		return $this->api;
	}


	/** Connection methods ******************************************************/


	/**
	 * Determine if the plugin is connected with Infoplus.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_connected() {

		return get_option( 'wc_infoplus_url' ) && get_option( 'wc_infoplus_api_key' );
	}


	/**
	 * Determine if the plugin is configured properly.
	 *
	 * Primarily checks that we can communicate with the Infoplus API.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_configured() {

		$is_configured = false;

		if ( 'yes' === get_transient( 'wc_infoplus_configured' ) ) {

			$is_configured = true;

		} else {

			try {

				$this->log( 'Pinging the Infoplus API' );

				$this->get_api()->ping();

				$is_configured = true;

				set_transient( 'wc_infoplus_configured', 'yes', 5 * MINUTE_IN_SECONDS );

			} catch ( SV_WC_API_Exception $e ) {

				$this->log( $e->getMessage() );
			}
		}

		return $is_configured;
	}


	/**
	 * Get the stored API key ID for the user that initiated this request.
	 *
	 * @since 1.0.0
	 * @global \wpdb $wpdb the WordPress database class
	 * @return int
	 */
	private function has_wc_api_key() {
		global $wpdb;

		$key_id = get_option( 'wc_infoplus_wc_api_key_id', 0 );

		$key = $wpdb->get_row( $wpdb->prepare( "
			SELECT *
			FROM {$wpdb->prefix}woocommerce_api_keys
			WHERE key_id = %s
		", $key_id ) );

		return ( $key );
	}


	/**
	 * Locate the WooCommerce template files from our templates directory.
	 *
	 * @since 1.0.0
	 * @param string $template Already found template
	 * @param string $template_name Searchable template name
	 * @param string $template_path Template path
	 * @return string Search result for the template
	 */
	public function locate_template( $template, $template_name, $template_path ) {

		// only keep looking if no custom theme template was found
		// or if a default WooCommerce template was found
		if ( ! $template || SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

			// set the path to our templates directory
			$plugin_path = $this->get_plugin_path() . '/templates/';

			// if a template is found, make it so
			if ( is_readable( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}
		}

		return $template;
	}


	/** Helper methods ******************************************************/


	/**
	 * Get the main plugin instance, ensures only one instance is/can be loaded.
	 *
	 * @since 1.0.0
	 * @see wc_infoplus()
	 * @return \WC_Infoplus
	 */
	public static function instance() {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Get the plugin configuration URL.
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_settings_link()
	 * @return string
	 */
	public function get_settings_url( $plugin_id = null ) {

		return admin_url( 'admin.php?page=wc-settings&tab=infoplus' );
	}


	/**
	 * Determine if viewing the admin plugin settings page.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'infoplus' === $_GET['tab'];
	}


	/**
	 * Get the plugin documentation url.
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://skyverge.com/documentation-infoplus-connect-woocommerce/';
	}


	/**
	 * Get the plugin support URL.
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://skyverge.com/contact/?form_type=support&purchased=wordpress&plugin=infoplus';
	}


	/**
	 * Get the plugin name, localized.
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string
	 */
	public function get_plugin_name() {
		return __( 'Infoplus Connect for WooCommerce', 'woo-infoplus-connect' );
	}


	/**
	 * Get the plugin file path.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_file() {
		return __FILE__;
	}


} // end WC_Infoplus


/**
 * Returns the one true instance of Infoplus.
 *
 * @since 1.0.0
 * @return \WC_Infoplus
 */
function wc_infoplus() {
	return WC_Infoplus::instance();
}

// fire it up!
wc_infoplus();

} // init_woocommerce_infoplus()
