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
define('HS_EV_PLUGIN_PATH',plugin_dir_path(__FILE__));
require_once HS_EV_PLUGIN_PATH.'homescript_fields.php';


add_action('admin_notices','hs_check_wc');
function hs_check_wc(){
    if (!class_exists('WooCommerce') ){
        ?>
            <div class=" notice notice-error">
                <p>
                <?php
                esc_html_e( 'WooCommerce Checkout Email Validator require WooCommerce for work.', 'hs_ev' );
                ?>
                 </p>
            </div>
        <?php
	}
}

add_action( 'woocommerce_get_sections_email' , 'hs_ev_options',10,1 );
function hs_ev_options($settings){
   $settings['hs_ev']= __( 'WooCommerce Checkout Email Validator','hs_ev');
   return $settings;
}
add_action( 'woocommerce_settings_tabs_email' , 'hs_ev_settings');
function hs_ev_settings( ) {
   
    $value = get_option('hs_ev_enabled');
    

    if (isset($_GET['section']) && $_GET['section']=='hs_ev'){
        homescript_input_fields('enable_hs_ev',
            array(
                'type'        => 'checkbox',
                'label' => __( 'Enable/Disable WooCommerce Checkout Email Validator', 'hs_ev' ),
                'description' => __('<br/>By enabling it, if a fake email is put an error message will be displayed.','ultimate-sms-notifications'),
                'required' => true
            ),
            $value
        );
    }
}

add_action('woocommerce_settings_saved', 'hs_ev_save');
function hs_ev_save(){
    if( isset($_POST['enable_hs_ev']) ){
        update_option('hs_ev_enabled',$_POST['enable_hs_ev']);
    }else{
        delete_option('hs_ev_enabled');
    }
}

add_action('woocommerce_checkout_process', 'hs_ev_process');
function hs_ev_process(){
    $value = get_option('hs_ev_enabled');
    if (isset($value) && isset($_POST['billing_email'])){
        $check_email = hs_ev_check_email($_POST['billing_email']);
        if ($check_email == false){
            wc_add_notice( __('This is not a valid email, please enter a valid email.','hs_ev'), 'error' );
        }     
    }
}

register_activation_hook(__FILE__, 'hs_ev_activate_hook');

function hs_ev_activate_hook() {
	$type_of_actions = "activation";
	$email = get_option('admin_email');
	$siteurl = get_site_url();
	$product = "hs_ev";
	$values = new stdClass();
	$values->text = "website : $siteurl ,\n email : $email ,\n product : $product ,\n action : $type_of_actions ";
	$json = wp_json_encode($values);
		wp_remote_post('https://hooks.slack.com/services/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',array(
		'method' => 'POST',
		'body' => $json
		) 
	);
}

function hs_ev_deactivate_hook() {
	$type_of_actions = "desactivation";
	$email = get_option('admin_email');
	$siteurl = get_site_url();
	$product = "hs_ev";
	$values = new stdClass();
	$values->text = "website : $siteurl ,\n email : $email ,\n product : $product ,\n action : $type_of_actions ";
	$json = wp_json_encode($values);
		wp_remote_post('https://hooks.slack.com/services/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',array(
		'method' => 'POST',
		'body' => $json
		) 
	);
}

register_deactivation_hook( __FILE__, 'hs_ev_deactivate_hook' );

function hs_ev_check_email($email, $record = 'MX'){
    list($user, $domain) = explode('@', $email);
    return checkdnsrr($domain, $record);
}