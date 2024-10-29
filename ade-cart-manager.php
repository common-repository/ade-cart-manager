<?php

/**
 * Plugin Name: Ade Cart Manager
 * Plugin URI: https://wordpress.org/plugins/ade-cart-manager/
 * Author: Adeleye Ayodeji
 * Author URI: https://adeleyeayodeji.com/
 * Description: Track and recover users cart items
 * Version: 1.4.5
 * License: 1.4.5
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: ade-cart-manager
 */

// add basic plugin security.
defined('ABSPATH') || exit;

if (!defined('ADE_CART_PLGUN_FILE')) {
	define('ADE_CART_PLGUN_FILE', __FILE__);
}

//include the plugin class
require_once plugin_dir_path(ADE_CART_PLGUN_FILE) . 'inc/ade-cart-manager.php';
require_once plugin_dir_path(ADE_CART_PLGUN_FILE) . 'inc/ade-dashboard-data.php';
require_once plugin_dir_path(ADE_CART_PLGUN_FILE) . 'inc/ade-dashboard.php';
require_once plugin_dir_path(ADE_CART_PLGUN_FILE) . 'inc/ade-search.php';
//init the plugin
$ade_cart_manager = new ADECARTMANAGER();
$ade_cart_manager->init();

// Activation, uninstall
register_activation_hook(__FILE__, array('ADECARTMANAGER', 'activation'));
register_deactivation_hook(__FILE__, array('ADECARTMANAGER', 'deactivation'));
