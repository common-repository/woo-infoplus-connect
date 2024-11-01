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

echo "\n\n";

echo "==========\n\n";

echo strtoupper( __( 'Shipments', 'woo-infoplus-connect' ) ) . "\n";

echo "\n\n";

$count = 1;

foreach( $shipments as $shipment ) {

	echo sprintf( __( 'Shipment #%s', 'woo-infoplus-connect' ), $count );

	echo "\n" . sprintf( __( 'Status: %s', 'woo-infoplus-connect' ), $shipment->get_status() );
	echo "\n" . sprintf( __( 'Carrier: %s', 'woo-infoplus-connect' ), ( $carrier = $shipment->get_carrier() ) ? $carrier : __( 'N/A', 'woo-infoplus-connect' ) );
	echo "\n" . sprintf( __( 'Tracking Number: %s', 'woo-infoplus-connect' ), ( $tracking_number = $shipment->get_tracking_number() ) ? $tracking_number : __( 'N/A', 'woo-infoplus-connect' ) );

	echo "\n\n";

	$count++;

}

echo "==========\n\n";
