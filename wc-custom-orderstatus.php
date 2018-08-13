<?php

/**
 * Plugin Name: WooCommerce Custom Order Status
 * Description: Adds custom order status to WooCommerce.
 * Author: Smartameer <smartameer@icloud.com>
 * Author URI: https://github.com/smartameer
 * Version: 1.0.1
 *
 * @author  smartameer
 */

/**
 * Copyright 2018 Smartameer  (email: smartameer@icloud.com)
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    if ( ! class_exists( 'WC_CustomOrderStatuses' ) ) {

        class WC_CustomOrderStatuses {

            public function __construct() {
                // called just before the woocommerce template functions are included
                add_action( 'admin_init', array( &$this, 'plugins_admin_init' ));
                add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ));
                add_action( 'init', array( &$this, 'register_order_statuses' ));
                add_filter( 'wc_order_statuses', array( &$this, 'add_to_order_statuses') );
                add_action( 'init', array(&$this, 'init_emails'));
            }

            public function plugins_admin_init() {
                wp_register_style( 'PluginStylesheet', plugins_url( 'styles/custom_order_statuses.css', __FILE__ ) );
                wp_enqueue_style( 'PluginStylesheet' );
            }

            public function load_textdomain() {
                load_plugin_textdomain( 'wc_custom_orderstatus', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
            }

            public function register_order_statuses() {
                register_post_status( 'wc-readyfordelivery', array(
                    'label'                     => __('Ready for Delivery', 'wc_custom_orderstatus'),
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( __('Ready for Delivery', 'wc_custom_orderstatus') . ' <span class="count">(%s)</span>', __('Ready for Delivery', 'wc_custom_orderstatus') . ' <span class="count">(%s)</span>' )
                ) );
                register_post_status( 'wc-pickedservice', array(
                    'label'                     => __('Picked up by Delivery Service', 'wc_custom_orderstatus'),
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( __('Picked up by Delivery Service', 'wc_custom_orderstatus') . ' <span class="count">(%s)</span>', __('Picked up by Delivery Service', 'wc_custom_orderstatus') . ' <span class="count">(%s)</span>' )
                ) );
                register_post_status( 'wc-onroute', array(
                    'label'                     => __('On Route', 'wc_custom_orderstatus'),
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop(  __('On Route', 'wc_custom_orderstatus' ) . ' <span class="count">(%s)</span>', __('On Route', 'wc_custom_orderstatus' ) . '<span class="count">(%s)</span>' )
                ) );
                register_post_status( 'wc-delivered', array(
                    'label'                     => __('Delivered', 'wc_custom_orderstatus'),
                    'public'                    => true,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( __('Delivered', 'wc_custom_orderstatus') . ' <span class="count">(%s)</span>', __('Delivered', 'wc_custom_orderstatus') .' <span class="count">(%s)</span>' )
                ) );
            }


            public function add_to_order_statuses( $order_statuses ) {
                $new_order_statuses = array();
                // add new order status after processing
                foreach ( $order_statuses as $key => $status ) {
                    $new_order_statuses[ $key ] = $status;
                    if ( 'wc-on-hold' === $key ) {
                        $new_order_statuses['wc-readyfordelivery'] = __('Ready for Delivery', 'wc_custom_orderstatus');
                        $new_order_statuses['wc-pickedservice'] = __('Picked up by Delivery Service', 'wc_custom_orderstatus');
                        $new_order_statuses['wc-onroute'] = __('On Route', 'wc_custom_orderstatus');
                        $new_order_statuses['wc-delivered'] = __('Delivered', 'wc_custom_orderstatus');
                    }
                }
                return $new_order_statuses;
            }

            public function init_emails() {
                add_filter( 'woocommerce_email_actions', array(&$this, 'wc_custom_actions'));
                add_filter( 'woocommerce_email_classes', array(&$this, 'wc_custom_orderemails' ));
            }

            public function wc_custom_actions ($actions) {
                $actions[] = 'woocommerce_order_status_readyfordelivery';
                $actions[] = 'woocommerce_order_status_pickedservice';
                $actions[] = 'woocommerce_order_status_onroute';
                $actions[] = 'woocommerce_order_status_delivered';
                return $actions;
            }

            public function wc_custom_orderemails( $email_classes ) {
                include plugin_dir_path(__FILE__) . 'wc-custom-orderemails.php';
                $email_classes['WC_Custom_OrderEmails'] = new WC_Custom_OrderEmails();
                return $email_classes;
            }

        }

        // finally instantiate our plugin class and add it to the set of globals
        $GLOBALS['wc_custom_orderstatus'] = new WC_CustomOrderStatuses();
    }
}
