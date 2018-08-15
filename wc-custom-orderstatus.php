<?php

/**
 * Plugin Name: WooCommerce Custom Order Status
 * Description: Adds custom order status to WooCommerce.
 * Author: Ameer <smartameer@icloud.com>
 * Author URI: https://github.com/smartameer
 * Version: 1.0.1
 */

/**
 * Copyright 2018 Ameer  (email: smartameer@icloud.com)
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    if ( ! class_exists( 'WC_CustomOrderStatuses' ) ) {

        class WC_CustomOrderStatuses {

            public function __construct() {

                $this->map = get_option('wc_custom_orderstatuses');
                if (!$this->map) {
                    $this->map = array();
                }
                $GLOBALS['wc_custom_orderstatus_map'] = $this->map;

                // called just before the woocommerce template functions are included
                add_action( 'admin_init', array( &$this, 'plugins_admin_init' ));
                add_action( 'plugins_loaded', array( &$this, 'load_textdomain' ));
                add_action( 'init', array( &$this, 'register_order_statuses' ));
                add_filter( 'wc_order_statuses', array( &$this, 'add_to_order_statuses') );
                add_action( 'init', array(&$this, 'init_emails'));
                add_action( 'admin_menu', array( &$this, 'wc_custom_orderstatus_menu' ), 100 );
                add_action( 'admin_post_orderstatus_create', array( &$this, 'wc_custom_orderstatus_create' ));
                add_action( 'admin_post_orderstatus_update', array( &$this, 'wc_custom_orderstatus_update' ));
            }

            public function wc_custom_orderstatus_create() {
                $error = false;
                $map = $this->map;
                if( isset( $_POST['orderstatus_nonce'] ) && wp_verify_nonce( $_POST['orderstatus_nonce'], 'orderstatus_create') ) {
                    $name = sanitize_text_field($_POST['name']);
                    $key = strtolower(str_replace(' ', '', $name));
                    if (isset($map[$key])) {
                        $error = true;
                    } else {
                        $map[$key] = array(
                            'label' => $name,
                            'email' => isset($_POST['email']),
                            'background' => $_POST['background'],
                            'color' => $_POST['color']
                        );
                        update_option('wc_custom_orderstatuses', $map);
                    }
                    $query = array('page' => 'order-status');
                    if ($error) {
                        $query['error'] = 'duplicate_order_status';
                    }
                    wp_safe_redirect(add_query_arg($query, esc_url(admin_url('admin.php'))));
                } else {
                    wp_die( __( 'Invalid nonce specified', 'wc_custom_orderstatus'), __( 'Error', 'wc_custom_orderstatus' ), array(
                        'response'     => 403,
                        'back_link' => 'admin.php?page=order-status',
                    ));
                }
            }

            public function wc_custom_orderstatus_update() {
                $error = false;
                $map = $this->map;
                if( isset( $_POST['orderstatus_nonce'] ) && wp_verify_nonce( $_POST['orderstatus_nonce'], 'orderstatus_update') ) {
                    $name = sanitize_text_field($_POST['name']);
                    $key = $_POST['status_key'];
                    if (isset($map[$key])) {
                        $map[$key] = array(
                            'label' => $name,
                            'email' => isset($_POST['email']),
                            'background' => $_POST['background'],
                            'color' => $_POST['color']
                        );
                        update_option('wc_custom_orderstatuses', $map);
                    } else {
                        $error = true;
                    }
                    $query = array('page' => 'order-status');
                    if ($error) {
                        $query['error'] = 'invalid_status_code';
                    }
                    wp_safe_redirect(add_query_arg($query, esc_url(admin_url('admin.php'))));
                } else {
                    wp_die( __( 'Invalid nonce specified', 'wc_custom_orderstatus'), __( 'Error', 'wc_custom_orderstatus' ), array(
                        'response'     => 403,
                        'back_link' => 'admin.php?page=order-status',
                    ));
                }
            }

            public function wc_custom_orderstatus_menu() {
                add_submenu_page( 'woocommerce', __('Order Status', 'wc_custom_orderstatus'), __('Order Status', 'wc_custom_orderstatus'), 'manage_woocommerce', 'order-status', array( &$this, 'wc_custom_orderstatus_page' ));
            }

            public function wc_custom_orderstatus_page() {
                wp_enqueue_script( 'wc_custom_orderstatus_js', plugins_url( 'scripts/custom_order_statuses.js', __FILE__ ), array( 'jquery' ), '', true  );
                include plugin_dir_path(__FILE__) . 'templates/custom-orderstatus-page.php';
            }

            public function plugins_admin_init() {
                wp_register_style( 'wc_custom_orderstatus_css', plugins_url( 'styles/custom_order_statuses.css', __FILE__ ) );
                wp_enqueue_style( 'wc_custom_orderstatus_css' );
                $customcss = '';
                foreach($this->map as $key => $status) {
                    $customcss .= ".order-status.status-{$key} { background: {$status['background']}; color: {$status['color']} }";
                }
                wp_add_inline_style('wc_custom_orderstatus_css', $customcss);
            }

            public function load_textdomain() {
                load_plugin_textdomain( 'wc_custom_orderstatus', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
            }

            public function register_order_statuses() {
                foreach($this->map as $key => $status) {
                    register_post_status( 'wc-' . $key, array(
                        'label'                     => $status['label'],
                        'public'                    => true,
                        'exclude_from_search'       => false,
                        'show_in_admin_all_list'    => true,
                        'show_in_admin_status_list' => true,
                        'label_count'               => _n_noop( $status['label'] . ' <span class="count">(%s)</span>', $status['label'] . ' <span class="count">(%s)</span>' )
                    ) );
                }
            }


            public function add_to_order_statuses( $order_statuses ) {
                $new_order_statuses = array();
                // add new order status after processing
                foreach ( $order_statuses as $key => $status ) {
                    $new_order_statuses[ $key ] = $status;
                    if ( 'wc-on-hold' === $key ) {
                        foreach($this->map as $key => $st) {
                            $new_order_statuses['wc-'. $key] = $st['label'];
                        }
                    }
                }
                return $new_order_statuses;
            }

            public function init_emails() {
                add_filter( 'woocommerce_email_actions', array(&$this, 'wc_custom_actions'));
                add_filter( 'woocommerce_email_classes', array(&$this, 'wc_custom_orderemails' ));
            }

            public function wc_custom_actions ($actions) {
                // add new order status after processing
                foreach ( $this->map as $key => $status ) {
                    if ($status['email']) {
                        $actions[] = 'woocommerce_order_status_' . $key;
                    }
                }
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
