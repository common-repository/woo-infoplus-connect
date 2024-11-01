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
?>

<table class="wc-infoplus-orders" cellpadding="0" cellspacing="0">

	<thead>
		<tr>
			<th class="number"><?php esc_html_e( 'Number', 'woo-infoplus-connect' ); ?></th>
			<th class="status"><?php esc_html_e( 'Status', 'woo-infoplus-connect' ); ?></th>
			<th class="items"><?php esc_html_e( 'Items', 'woo-infoplus-connect' ); ?></th>
			<th class="parcels"><?php esc_html_e( 'Parcels', 'woo-infoplus-connect' ); ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ( $orders as $order ) : ?>

			<tr>
				<td class="number"><a href="<?php echo esc_url( $order->get_url() ); ?>" target="_blank"><?php echo esc_html( $order->get_number() ); ?></a></td>
				<td class="status <?php echo esc_attr( strtolower( $order->get_status() ) ); ?>"><?php echo esc_html( $order->get_status() ); ?></td>

				<td class="items">
					<ul>
						<?php foreach ( $order->get_items() as $item ) : ?>

							<li><?php echo esc_html( $item->get_name() ); ?> <span class="times">&times;</span> <?php echo (int) $item->get_quantity(); ?></li>

						<?php endforeach; ?>
					</ul>
				</td>

				<td class="parcels">

					<?php $parcels = $order->get_parcels(); ?>

					<?php if ( ! empty( $parcels ) ) : ?>

						<?php foreach ( $parcels as $parcel ) : ?>

							<div class="parcel">

								<div class="details">

									<span class="id"><a href="<?php echo esc_url( $parcel->get_url() ); ?>" target="_blank"><?php echo esc_html( $parcel->get_id() ); ?></a></span>:

									<?php if ( $tracking_number = $parcel->get_tracking_number() ) : ?>

										<?php if ( $tracking_url = $parcel->get_tracking_url() ) : ?>
											<a class="tracking-number" href="<?php echo esc_url( $parcel->get_tracking_url() ); ?>" target="_blank">
										<?php endif; ?>

										<?php echo esc_html( $tracking_number ); ?>

										<?php if ( $tracking_url ) : ?>
											</a>
										<?php endif; ?>

									<?php endif; ?>

								</div>

								<div class="meta">

									<span class="status"><?php echo esc_html( $parcel->get_status() ); ?></span>

									<?php if ( $carrier = $parcel->get_carrier() ) : ?>
										<?php esc_html_e( 'via', 'woo-infoplus-connect' ); ?>
										<span class="carrier"><?php echo esc_html( $carrier ); ?></span>
									<?php endif; ?>

								</div>

							</div>

						<?php endforeach; ?>

					<?php else : ?>

						<span class="na"><?php esc_html_e( 'N/A', 'woo-infoplus-connect' ); ?></span>

					<?php endif; ?>

				</td>

			</tr>

		<?php endforeach; ?>

	</tbody>

</table>
