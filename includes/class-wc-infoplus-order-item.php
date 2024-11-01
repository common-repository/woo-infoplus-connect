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
 * The Infoplus order item class.
 *
 * @since 1.0.0
 */
class WC_Infoplus_Order_Item {


	/** @var object the raw order data from the database **/
	protected $data = array(
		'sku'      => '',
		'quantity' => 0,
	);


	/**
	 * Construct the order object.
	 *
	 * @since 1.0.0
	 * @param array $data {
	 *     The Infoplus order item data, from the database.
	 *
	 *     @type string $sku      the item SKU
	 *     @type string $quantity the quantity ordered
	 * }
	 */
	public function __construct( $data ) {

		$this->data = wp_parse_args( $data, $this->data );
	}


	/**
	 * Get the SKU.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_sku() {
		return $this->data['sku'];
	}


	/**
	 * Get the quantity ordered.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_quantity() {
		return (int) $this->data['quantity'];
	}


	/**
	 * Get the product name.
	 *
	 * If the SKU has no associated WC product, the unmodified SKU will be returned.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_name() {

		$name = $this->get_sku();

		if ( $product = wc_get_product( wc_get_product_id_by_sku( $this->get_sku() ) ) ) {
			$name = $product->get_title();
		}

		return $name;
	}


}
