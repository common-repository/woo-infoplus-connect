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
 * @package     WC_Infoplus\Templates
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

?>

<h2><?php esc_html_e( 'Shipments', 'woo-infoplus-connect' ); ?></h2>

<table class="shop_table order_shipments">
	<thead>
		<tr>
			<th class="shipment-status"><?php esc_html_e( 'Status', 'woo-infoplus-connect' ); ?></th>
			<th class="shipment-carrier"><?php esc_html_e( 'Carrier', 'woo-infoplus-connect' ); ?></th>
			<th class="shipment-tracking-number"><?php esc_html_e( 'Tracking Number', 'woo-infoplus-connect' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $shipments as $shipment ) : ?>

			<tr>

				<td class="shipment-status"><?php echo esc_html( $shipment->get_status() ); ?></td>

				<td class="shipment-carrier"><?php echo esc_html( ( $carrier = $shipment->get_carrier() ) ? $carrier : esc_html__( 'N/A', 'woo-infoplus-connect' ) ); ?></td>

				<td class="shipment-tracking-number">
					<?php if ( $tracking_number = $shipment->get_tracking_number() ) : ?>

						<?php if ( $tracking_url = $shipment->get_tracking_url() ) : ?>
							<a class="tracking-number" href="<?php echo esc_url( $shipment->get_tracking_url() ); ?>" target="_blank">
						<?php endif; ?>

						<?php echo esc_html( $tracking_number ); ?>

						<?php if ( $tracking_url ) : ?>
							</a>
						<?php endif; ?>

					<?php else : ?>
						<?php esc_html_e( 'N/A', 'woo-infoplus-connect' ); ?>
					<?php endif; ?>
				</td>
			</tr>

		<?php endforeach; ?>
	</tbody>
</table>
