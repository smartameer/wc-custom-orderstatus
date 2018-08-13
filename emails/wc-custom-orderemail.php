<?php
/**
 *  HTML emailing Template
 *  @author: Ameer <smartameer@icloud.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php _e( 'Below are the order details for your reference.' ) ?></p>

<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_footer', $email );
