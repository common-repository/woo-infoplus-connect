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
 * Set up the settings.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Settings extends WC_Settings_Page {


	/**
	 * Construct the class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id = 'infoplus';

		$this->label = __( 'Infoplus', 'woo-infoplus-connect' );

		// handle the custom action button "setting"
		add_action( 'woocommerce_admin_field_wc_infoplus_action_button', array( $this, 'output_action_button' ) );

		parent::__construct();
	}


	/**
	 * Get the connection settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_connection_settings() {

		$settings = array(

			array(
				'name' => __( 'Connection Settings', 'woo-infoplus-connect' ),
				'type' => 'title',
			),

			array(
				'id'    => 'wc_infoplus_api_key',
				'name'  => __( 'Infoplus API Key', 'woo-infoplus-connect' ),
				'class' => 'input-text regular-input',
				'type'  => 'text',
			),

			array(
				'id'       => 'wc_infoplus_debug_mode',
				'name'     => __( 'Debug Mode', 'woo-infoplus-connect' ),
				'desc'     => __( 'Enable debug mode. ', 'woo-infoplus-connect' ),
				'desc_tip' => __( 'Log API requests/responses and errors to the WooCommerce log. Only enable if you are having issues.', 'woo-infoplus-connect' ),
				'default'  => 'no',
				'type'     => 'checkbox',
			),

			array( 'type' => 'sectionend' ),
		);

		/**
		 * Filter the connection settings.
		 *
		 * @since 1.0.0
		 * @param array $settings the connection settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_connection', $settings );
	}


	/**
	 * Get the order settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_order_settings() {

		$settings = array(

			array(
				'name' => __( 'Orders', 'woo-infoplus-connect' ),
				'type' => 'title',
			),

			array(
				'id'              => 'wc_infoplus_order_update_auto_complete',
				'name'            => __( 'Auto-Complete', 'woo-infoplus-connect' ),
				'desc'            => __( 'Mark orders as "Complete" when all associated Infoplus orders have shipped', 'woo-infoplus-connect' ),
				'default'         => 'yes',
				'type'            => 'checkbox',
			),

			array(
				'type'   => 'wc_infoplus_action_button',
				'label'  => __( 'Update orders now', 'woo-infoplus-connect' ),
				'class'  => 'button-secondary',
				'action' => 'update_orders',
			),

			array( 'type' => 'sectionend' ),
		);

		/**
		 * Filter the order settings.
		 *
		 * @since 1.0.0
		 * @param array $settings the order settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_orders', $settings );
	}


	/**
	 * Get the product settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_product_settings() {

		$settings = array(

			array(
				'name' => __( 'Products', 'woo-infoplus-connect' ),
				'type' => 'title',
			),

			array(
				'type'   => 'wc_infoplus_action_button',
				'label'  => __( 'Update products now', 'woo-infoplus-connect' ),
				'class'  => 'button-secondary',
				'action' => 'update_products',
			),

			array( 'type' => 'sectionend' ),
		);

		/**
		 * Filter the product settings.
		 *
		 * @since 1.0.0
		 * @param array $settings the product settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id . '_products', $settings );
	}


	/**
	 * Get all of the combined settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings() {

		$settings = array_merge(
			$this->get_connection_settings(),
			$this->get_order_settings(),
			$this->get_product_settings()
		);

		/**
		 * Filter the combined settings.
		 *
		 * @since 1.0.0
		 * @param array $settings the combined settings
		 */
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
	}


	/**
	 * Output the settings page.
	 *
	 * @since 1.0.0
	 */
	public function output() {

		if ( wc_infoplus()->is_connected() ) {

			$this->output_settings();

		} else {

			// display the Connect HTML
			include( wc_infoplus()->get_plugin_path() . '/includes/admin/views/html-settings-connect.php' );

			$GLOBALS['hide_save_button'] = true;
		}
	}


	/**
	 * Output the settings.
	 *
	 * @since 1.0.0
	 */
	public function output_settings() {

		// if the API credentials are good, output the rest of the settings
		if ( wc_infoplus()->is_configured() ) {
			WC_Admin_Settings::output_fields( $this->get_order_settings() );
		}

		// always display the connection settings
		WC_Admin_Settings::output_fields( $this->get_connection_settings() );
	}


	/**
	 * Output the custom action button "setting".
	 *
	 * @since 1.0.0
	 * @param array $value the button params
	 */
	public function output_action_button( $value ) {

		// description handling
		$description_data = WC_Admin_Settings::get_field_description( $value );
		$tooltip_html = $description_data['tooltip_html'];
		$description  = $description_data['description'];

		$classes = $value['class'];

		if ( is_array( $classes ) ) {
			$classes = implode( ' ', $classes );
		}

		?><tr valign="top">
			<th scope="row" class="titledesc">
				<?php if ( $value['title'] ) : ?>
					<?php echo esc_html( $value['title'] ); ?>
					<?php echo $tooltip_html; ?>
				<?php endif; ?>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<div class="wc-infoplus-action-message"></div>
				<button class="wc-infoplus-action-button <?php echo esc_attr( $classes ); ?> button" data-wc-infoplus-action="<?php echo esc_attr( $value['action'] ); ?>"><?php echo esc_html( $value['label'] ); ?></button>
				<span class="wc-infoplus-action-indicator"></span>
				<?php echo $description; ?>
			</td>
		</tr><?php
	}


	/**
	 * Save the settings.
	 *
	 * @since 1.0.0
	 */
	public function save() {

		// if the API key was good at last check, save the settings
		if ( 'yes' === get_transient( 'wc_infoplus_configured' ) ) {
			WC_Admin_Settings::save_fields( $this->get_order_settings() );
		}

		// always save the connection settings
		WC_Admin_Settings::save_fields( $this->get_connection_settings() );

		// reset the API status transient
		delete_transient( 'wc_infoplus_configured' );
	}


}
