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
 * Set up the admin.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Admin {


	/** @var \WC_Infoplus_Admin_Orders class instance */
	protected $orders;

	/** @var \WC_Infoplus_Admin_Products class instance */
	protected $products;


	/**
	 * Bootstrap class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->add_hooks();

		$this->load_classes();
	}


	/**
	 * Add the actions and filters.
	 *
	 * @since 1.0.0
	 */
	protected function add_hooks() {

		// load the scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );

		// add the settings page
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_settings_page' ) );
	}


	/**
	 * Load the admin classes.
	 *
	 * @since 1.0.0
	 */
	protected function load_classes() {

		$this->orders   = wc_infoplus()->load_class( '/includes/admin/class-wc-infoplus-admin-orders.php', 'WC_Infoplus_Admin_Orders' );
		$this->products = wc_infoplus()->load_class('/includes/admin/class-wc-infoplus-admin-products.php', 'WC_Infoplus_Admin_Products' );
	}


	/**
	 * Load the scripts and styles.
	 *
	 * @since 1.0.0
	 * @param string $hook_suffix the current screen suffix
	 */
	public function enqueue_scripts_styles( $hook_suffix ) {

		// only enqueue the scripts on the Settings and Order screens
		if ( ! wc_infoplus()->is_plugin_settings() && 'shop_order' !== get_post_type()  ) {
			return;
		}

		wp_enqueue_style( 'wc-infoplus-admin', wc_infoplus()->get_plugin_url() . '/assets/css/admin/wc-infoplus-admin.min.css', array(), WC_Infoplus::VERSION );

		wp_enqueue_script( 'wc-infoplus-admin', wc_infoplus()->get_plugin_url() . '/assets/js/admin/wc-infoplus-admin.min.js', array( 'jquery' ), WC_Infoplus::VERSION, true );

		wp_localize_script( 'wc-infoplus-admin', 'wc_infoplus_admin', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonces' => array(
				'update_orders' => wp_create_nonce( 'wc_infoplus_update_orders' ),
			),
			'i18n' => array(
				'ajax_error_message' => __( 'Something went wrong. Please try again.', 'woo-infoplus-connect' ),
			),
		) );
	}


	/**
	 * Add the settings page.
	 *
	 * @since 1.0.0
	 * @param array $settings
	 * @return array $settings
	 */
	public function add_settings_page( $settings ) {

		// init the settings class
		$settings[] = wc_infoplus()->load_class( '/includes/admin/class-wc-infoplus-admin-settings.php', 'WC_Infoplus_Settings' );

		return $settings;
	}


	/**
	 * Get the admin orders class instance
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_Admin_Orders
	 */
	public function get_orders_instance() {

		return $this->orders;
	}


	/**
	 * Get the admin products class instance.
	 *
	 * @since 1.0.0
	 * @return \WC_Infoplus_Admin_Products
	 */
	public function get_products_instance() {

		return $this->products;
	}


}
