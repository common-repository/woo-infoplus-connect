=== Infoplus Connect for WooCommerce ===
Author: skyverge, infopluscommerce
Tags: woocommerce, orders, fulfillment, warehouse, inventory, inventory management
Requires at least: 4.4
Tested up to: 4.7.5
Requires WooCommerce at least: 2.6.0
Tested WooCommerce up to: 3.0.7
Stable Tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Connects your store to Infoplus to sync inventory, orders, and shipment tracking information for optimized order fulfillment.

== Description ==

> **Requires: WooCommerce 2.6** or newer

[Infoplus](http://infopluscommerce.com/) helps you run your business with Amazon-like accuracy and efficiency, optimizing order management and scaling with your store as you grow. Merchants who use Infoplus for order fulfillment can use Infoplus’s cloud platform to manage inventory, orders, and shipments.

Infoplus works with 19 shipping providers to ensure you get the best rates; let you print shipping labels, packing lists, or documents; and ensure you meet all shipping regulations for your products. Shipment tracking helps you and your customers keep a pulse on your shipments, ensuring your customers are happy and that orders arrive when expected.

Inventory for WooCommerce products will automatically be synced to Infoplus to keep your stock-on-hand current with your warehouse levels.

When orders are placed, items can automatically be synced to Infoplus for fulfillment, then tracking and status updates are pushed back to your store without any additional effort.

= Infoplus Connect =

Infoplus Connect for WooCommerce connects WooCommerce stores to Infoplus to manage and simplify your order fulfillment process. Automatically sync your store's product inventory with Infoplus, sync orders that contain Infoplus-managed products, and display tracking updates to admins and customers!

Choose which products are managed via Infoplus, or which you fulfill outside of this system.

= Infoplus Connect Features =

 - Automatically **sync paid orders** to Infoplus for fulfillment and shipment
 - Automatic order sync even supports [Order Status Manager](https://www.woocommerce.com/products/woocommerce-order-status-manager/) "is paid" order statuses!
 - Supports **split orders** of Infoplus-managed and non-managed products; only managed products are submitted to Infoplus
 - Choose to **auto-complete orders** when they’re marked "shipped" by Infoplus
 - Automatically **sync product inventory** with Infoplus
 - Will pull tracking numbers from Infoplus and **automatically update orders**

= More Details =

 - Visit [InfoplusCommerce.com](http://www.infopluscommerce.com/) for more details on Infoplus and how it can streamline your inventory management and order fulfillment.
 - See the [knowledge base and documentation](https://skyverge.com/documentation-infoplus-connect-woocommerce/) for questions and set up help.
 - Developers can find helpful hooks and functions [in our developer reference](https://www.skyverge.com/documentation-developers-infoplus-connect/).

== Installation ==

1. Be sure you're running **WooCommerce 2.6** or newer in your shop.

2. To install the plugin, you can do one of the following:

    - (Recommended) Search for "Infoplus Connect for WooCommerce" under Plugins &gt; Add New
    - Upload the entire `infoplus-connect-for-woocommerce` folder to the `/wp-content/plugins/` directory.
    - Upload the .zip file with the plugin under **Plugins &gt; Add New &gt; Upload**

3. Activate the plugin through the 'Plugins' menu in WordPress

4. Follow the [setup guide](https://skyverge.com/documentation-infoplus-connect-woocommerce/) to connect to Infoplus.

5. Click the "Configure" plugin link or go to **WooCommerce &gt; Settings &gt; Infoplus** to configure the extension settings.

6. Save your settings!

== Frequently Asked Questions ==

= Do I need anything else to use this plugin? =

Yes, an Infoplus account (paid) is required to use Infoplus's service to manage order fulfillment and inventory for your store. You can [learn more about using Infoplus with WooCommerce here](http://www.infopluscommerce.com/integrations/woocommerce).

= Is this plugin translatable? =

Yep! The text domain is: `woo-infoplus-connect`

== Screenshots ==

1. Connect your store to Infoplus
2. Determine which products are managed via Infoplus
3. Order data is automatically pushed to Infoplus and synced to fulfillment workflows
4. Customers and admins can easily track orders

== Changelog ==

= 2017-05-23 - version 1.0.1 =
 * Fix - Order meta not being stored properly in WooCommerce 3.0+

= 2017-05-16 - version 1.0.0 =
 * Initial release!
