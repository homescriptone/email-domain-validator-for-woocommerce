<?php

/**
 * Plugin Name:       Email Domain Validator for WooCommerce
 * Plugin URI:        https://github.com/homescript1/email-domain-validator-for-woocommerce
 * Description:       An easy tool for prevent email spamming on your store.
 * Version:           1.0
 * Author:            HomeScript
 * Author URI:        https://homescriptone.com
 * Text Domain:       hs_ev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    die();
}

define('HS_EV_VERSION', '1.0');
define('HS_EV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HS_EV_PLUGIN_URL', plugin_dir_url(__FILE__));
require_once HS_EV_PLUGIN_PATH . 'homescript_fields.php';


add_action('admin_notices', 'hs_check_wc');
/**
 * This function check if WooCommerce is installed.
 *
 * @return void
 */
function hs_check_wc()
{
    if (!class_exists('WooCommerce')) {
        ?>
        <div class=" notice notice-error">
            <p>
                <?php
                        esc_html_e('Email Domain Validator for WooCommerce require WooCommerce for work.', 'hs_ev');
                        ?>
            </p>
        </div>
    <?php
        }
    }

    add_action('woocommerce_get_sections_email', 'hs_ev_options', 10, 1);
    /**
     * This function is responsible to add a new settings into WooCommerce Settings.
     *
     * @param array $settings
     * @return void
     */
    function hs_ev_options($settings)
    {
        $settings['hs_ev'] = __('Email Domain Validator for WooCommerce', 'hs_ev');
        return $settings;
    }

    add_action('woocommerce_settings_tabs_email', 'hs_ev_settings');
    /**
     * This function render the fields into WooCommerce Settings.
     */
    function hs_ev_settings()
    {

        $value = get_option('hs_ev_enabled');


        if (isset($_GET['section']) && $_GET['section'] == 'hs_ev') {
            homescript_input_fields(
                'enable_hs_ev',
                array(
                    'type'        => 'checkbox',
                    'label' => __('Enable/Disable Email Domain Validator for WooCommerce', 'hs_ev'),
                    'description' => __('<br/>By enabling it, if a email domain name is false, an error message will be returned.', 'hs_ev'),
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
    function hs_ev_save()
    {
        if (isset($_POST['enable_hs_ev'])) {
            update_option('hs_ev_enabled', sanitize_text_field($_POST['enable_hs_ev']));
        } else {
            delete_option('hs_ev_enabled');
        }
    }

    add_action('woocommerce_checkout_process', 'hs_ev_process');

    /**
     * This function is responsible to process email validation on the checkout.
     *
     * @return void
     */
    function hs_ev_process()
    {
        $value = get_option('hs_ev_enabled');
        if (isset($value) && isset($_POST['billing_email'])) {
            $check_email = hs_ev_check_email($_POST['billing_email']);
            if ($check_email == false) {
                wc_add_notice(__('This is not a valid email, please enter a valid email.', 'hs_ev'), 'error');
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
    function hs_ev_check_email($email, $record = 'MX')
    {
        list($user, $domain) = explode('@', $email);
        return checkdnsrr($domain, $record);
    }

    add_action('admin_menu', 'hs_ev_add_submenu');
    /**
     * This function is responsible to add a submenu under WooCommerce.
     */
    function hs_ev_add_submenu()
    {
        add_submenu_page('woocommerce', __("Email Domain Validator for WooCommerce", 'hs_ev'), __("Email Domain Validator for WooCommerce", "hs_ev"), "manage_options", "hs_ev", "hs_ev_submenu");
    }

    function hs_ev_submenu()
    {
        wp_redirect('admin.php?page=wc-settings&tab=email&section=hs_ev');
        exit();
    }

    add_filter('admin_footer_text', 'hs_footer_credits');
    function hs_footer_credits($text)
    {
        if (isset($_GET['section']) && $_GET['section'] == 'hs_ev') {
            $text = sprintf(__('If you like %1$s please leave us a %2$s rating.This will make happy %3$s.', 'hs_ev'),    sprintf('<strong>%s</strong>', esc_html__('Email Domain Validator for WooCommerce', 'hs_ev')), '<a href="https://wordpress.org/support/plugin/email-domain-validator-for-woocommerce/reviews?rate=5#new-post" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__('Thanks :)', 'hs_ev') . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>', sprintf('<strong>%s</strong>', esc_html__('HomeScript team', 'hs_ev')));
        }
        return $text;
    }

    add_action('admin_notices', "hs_reviews_notices");
    function hs_reviews_notices()
    {
        if (!get_option('hs_ev_subscribe_to_tips') || get_transient('hs_remind_customers') == "remind-o") {

            ?>
        <div class="hs_tips notice notice-info">
            <p>
                <img id="plug-logo" style="width: 50px" src="<?php echo HS_EV_PLUGIN_URL; ?>/images/logo.png">
                <span style="    display: table; margin-left: 55px; margin-top: -41px; z-index: -1;">
                    <?php _e('<strong>Email Domain Validator for WooCommerce</strong>: Sign up now to receive tips and tricks for improve your sales.', 'hs_ev'); ?>

                    <input type="email" id="hs_email" name="usermail" placeholder="Your email here" value="<?php echo get_option('admin_email'); ?>">
                    <img id="hs-subscribe-loader" style="width: 20px; display : none;" src="<?php echo HS_EV_PLUGIN_URL; ?>/images/loader.gif">&nbsp
                    <button id="hs-subscribe" class="button button-primary"><?php _e("Subscribe", "hs_ev"); ?></button>&nbsp
                    <a id="hs-dismiss" href="#hs-dismiss" style="text-decoration:none;"><?php _e("Not now", "hs_ev"); ?></a>

            </p>
        </div>
<?php
    }
}

add_action('admin_enqueue_scripts', 'hs_load_assets');
function hs_load_assets()
{
    wp_enqueue_style('hs_ev_styles', plugin_dir_url(__FILE__) . '/assets/css/hs_ev.css', array(), HS_EV_VERSION, 'all');
    wp_enqueue_script('hs_ev_script', plugin_dir_url(__FILE__) . "/assets/js/hs_ev.js", array('jquery'), HS_EV_VERSION, true);
}

add_action('wp_ajax_hs_subscribe_tips', 'hs_subscribe_tips');
add_action('wp_ajax_no_priv_hs_subscribe_tips', 'hs_subscribe_tips');
function hs_subscribe_tips()
{
    if (isset($_POST)) {
        $email = sanitize_text_field($_POST['email']);
        $plugin_name = sanitize_text_field($_POST['plugin_name']);
        $choice = sanitize_text_field($_POST['choice']);

        $call_api = wp_remote_get('https://homescriptone.com/wp-json/services/v1/subscriptions', array(
            'method' => 'GET',
            'body' => array(
                'plugin-name' => $plugin_name,
                'email' => $email,
                'choice' => $choice,
                'site_web' => get_site_url()
            )
        ));

        $response_code = wp_remote_retrieve_body($call_api);
        $success = 0;
        if ("200" == $response_code) {
            $success = 1;
            update_option('hs_ev_subscribe_to_tips', true);
        }

        if ($choice == false){
            set_transient('hs_remind_customers',"remind-o",DAY_IN_SECONDS * 5 );
        }
        echo $success;
    }
    die();
}

