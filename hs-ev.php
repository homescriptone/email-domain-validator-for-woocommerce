<?php
/**
 * Plugin Name:       Email Domain Validator for WooCommerce
 * Plugin URI:        https://homescript1.github.io/once-cart-items-remover/
 * Description:       An easy tool for prevent email spamming on your store.
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
define('HS_EV_PLUGIN_PATH',plugin_dir_path(__FILE__));
require_once HS_EV_PLUGIN_PATH.'homescript_fields.php';


add_action('admin_notices','hs_check_wc');
/**
 * This function check if WooCommerce is installed.
 *
 * @return void
 */
function hs_check_wc(){
    if (!class_exists('WooCommerce') ){
        ?>
            <div class=" notice notice-error">
                <p>
                <?php
                esc_html_e( 'Email Domain Validator for WooCommerce require WooCommerce for work.', 'hs_ev' );
                ?>
                 </p>
            </div>
        <?php
	}
}

add_action( 'woocommerce_get_sections_email' , 'hs_ev_options',10,1 );
/**
 * This function is responsible to add a new settings into WooCommerce Settings.
 *
 * @param array $settings
 * @return void
 */
function hs_ev_options($settings){
   $settings['hs_ev']= __( 'Email Domain Validator for WooCommerce','hs_ev');
   return $settings;
}

add_action( 'woocommerce_settings_tabs_email' , 'hs_ev_settings');
/**
 * This function render the fields into WooCommerce Settings.
 */
function hs_ev_settings( ) {
   
    $value = get_option('hs_ev_enabled');
    

    if (isset($_GET['section']) && $_GET['section']=='hs_ev'){
        homescript_input_fields('enable_hs_ev',
            array(
                'type'        => 'checkbox',
                'label' => __( 'Enable/Disable Email Domain Validator for WooCommerce', 'hs_ev' ),
                'description' => __('<br/>By enabling it, if a fake email is put an error message will be displayed.','hs_ev'),
                'required' => true
            ),
            $value
        );
    }
}

add_action('woocommerce_settings_saved', 'hs_ev_save');
/**
 * This function is responsible to save settings.
 *
 * @return void
 */
function hs_ev_save(){
    if( isset($_POST['enable_hs_ev']) ){
        update_option('hs_ev_enabled',$_POST['enable_hs_ev']);
    }else{
        delete_option('hs_ev_enabled');
    }
}

add_action('woocommerce_checkout_process', 'hs_ev_process');

/**
 * This function is responsible to process email validation on the checkout.
 *
 * @return void
 */
function hs_ev_process(){
    $value = get_option('hs_ev_enabled');
    if (isset($value) && isset($_POST['billing_email'])){
        $check_email = hs_ev_check_email($_POST['billing_email']);
        if ($check_email == false){
            wc_add_notice( __('This is not a valid email, please enter a valid email.','hs_ev'), 'error' );
        }     
    }
}

/**
 * This function check if email is valid or not.
 *
 * @param string $email
 * @param string $record
 * @return bool
 */
function hs_ev_check_email($email, $record = 'MX'){
    list($user, $domain) = explode('@', $email);
    return checkdnsrr($domain, $record);
}

add_action('admin_menu','hs_ev_add_submenu');
/**
 * This function is responsible to add a submenu under WooCommerce.
 */
function hs_ev_add_submenu(){
    add_submenu_page('woocommerce',__("Email Domain Validator for WooCommerce",'hs_ev'),__("Email Domain Validator for WooCommerce","hs_ev"),"manage_options","hs_ev","hs_ev_submenu");
}

function hs_ev_submenu(){
    wp_redirect('admin.php?page=wc-settings&tab=email&section=hs_ev');
    exit();
}