<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Custom_OrderEmails extends WC_Email {

    public function __construct() {

        $this->map = array(
            'readyfordelivery' => 'Ready for Delivery',
            'pickedservice' => 'Picked up by Delivery Service',
            'onroute' => 'On Route',
            'delivered' => 'Delivered'
        );

        $this->id = 'customer_orderemails';
        $this->customer_email = true;
        $this->title = __( 'Custom Order Status Emails', 'woocommerce' );
        $this->description = __( 'When Status is changed to Ready for Delivery/Picked up by Delivery Service/On Route/Delivered an email will be sent to customer', 'woocommerce' );
        $this->placeholders   = array(
            '{site_title}'   => $this->get_blogname(),
            '{order_date}'   => '',
            '{order_status}' => '',
            '{order_number}' => '',
        );
        $this->template_path  = plugin_dir_path(__FILE__);
        $this->template_html  = 'emails/wc-custom-orderemail.php';
        $this->template_plain = 'emails/plain/wc-custom-orderemail.php';

        add_action( 'woocommerce_order_status_readyfordelivery_notification', array( $this, 'trigger' ), 10, 2 );
        add_action( 'woocommerce_order_status_pickedservice_notification', array( $this, 'trigger' ), 10, 2 );
        add_action( 'woocommerce_order_status_onroute_notification', array( $this, 'trigger' ), 10, 2 );
        add_action( 'woocommerce_order_status_delivered_notification', array( $this, 'trigger' ), 10, 2 );

        parent::__construct();
    }

    public function get_default_subject() {
        return __( 'Your {site_title} order from {order_date} is {order_status}', 'woocommerce' );
    }

    public function get_default_heading() {
        return __( 'Your order is {order_status}', 'woocommerce' );
    }

    public function trigger( $order_id, $order = false ) {

        $this->setup_locale();

        if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
            $order = wc_get_order( $order_id );
        }

        if ( is_a( $order, 'WC_Order' ) ) {
            $this->object                         = $order;
            $this->recipient                      = $this->object->get_billing_email();
            $this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
            $this->placeholders['{order_number}'] = $this->object->get_order_number();
            $this->placeholders['{order_status}'] = $this->map[$this->object->get_status()];
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }


    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'order'                    => $this->object,
            'email_heading'            => $this->get_heading(),
            'sent_to_admin'            => false,
            'plain_text'               => false,
            'email'                    => $this
        ), $this->template_path, $this->template_path);
    }

    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'order'                    => $this->object,
            'email_heading'            => $this->get_heading(),
            'sent_to_admin'            => false,
            'plain_text'               => true,
            'email'                    => $this
        ), $this->template_path, $this->template_path);
    }


    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'woocommerce' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}, {order_status}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => __( 'Email Heading', 'woocommerce' ),
                'type'        => 'text',
                'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>{site_title}, {order_date}, {order_number}, {order_sattus}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'woocommerce' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'woocommerce' ),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            )
        );
    }

}
