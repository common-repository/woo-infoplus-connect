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
 * @package     WC_Infoplus\Admin\Settings
 * @author      SkyVerge
 * @copyright   Copyright (c) 2016-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
?>

<h2><?php esc_html_e( 'WooCommerce Infoplus Connect', 'woo-infoplus-connect' ); ?></h2>

<div class="wrap" style="max-width: 800px;">
	<div class="notice notice-success">
		<p>You're almost ready to connect your store to Infoplus!</p>
	</div>
	<p>Please log into your <a href="http://www.infopluscommerce.com/" target="_blank">Infoplus</a> dashboard using your shop's unique Infoplus URL, then our setup guide will show you how to connect Infoplus to your shop.</p>

	<p><a href="<?php echo esc_url( wc_infoplus()->get_documentation_url() ); ?>#setup" target="_blank" class="button button-primary button-large">View setup guide</a></p>
</div>
