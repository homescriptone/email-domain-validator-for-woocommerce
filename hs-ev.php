<?php
/**
 * Plugin Name:       WooCommerce Checkout Email Validator
 * Plugin URI:        https://homescript1.github.io/once-cart-items-remover/
 * Description:        An easy tool for prevent email spamming on your store.
 * Version:           1.0
 * Author:            HomeScript
 * Author URI:        https://homescriptone.com
 * Text Domain:       hs_ev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')){
    die();
}

define('HS_EV_VERSION','1.0');

require_once plugin_dir(__FILE__).'class-hs-ev.php';

