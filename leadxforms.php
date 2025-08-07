<?php

/**
 * @wordpress-plugin
 * Plugin Name:       LeadXForms
 * Plugin URI:        https://wp-lead-forms.test/
 * Description:       The Unlimited solution for creating custom forms and flows to connect users and enhance engagement and broaden your online presence.
 * Version:           1.1.1
 * Author:            Pixelz360
 * Author URI:        https://pixelz360.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lxform
 * Domain Path:       /languages
 */


defined('ABSPATH') or die('Access Denied!');


if (!defined('LXFORM_PLUGIN_VERSION')) {
    define('LXFORM_PLUGIN_VERSION', '1.1.1');
}
if (!defined('LXFORM_PLUGIN_NAME')) {
    define('LXFORM_PLUGIN_NAME', 'LeadXForms');
}
if (!defined('LXFORM_PLUGIN_ID')) {
    define('LXFORM_PLUGIN_ID', 'leadxforms');
}

add_action('init', function () {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $upgrade_file = plugin_dir_path(__FILE__) . 'includes/class-leadxforms-upgrade.php';
    $renamed_upgrade_file = plugin_dir_path(__FILE__) . 'includes/class-leadxforms-upgrade-' . time() . '.php';
    if (file_exists($upgrade_file)) {
        require_once $upgrade_file;

        if (class_exists('UpgradePluginVersion')) {
            UpgradePluginVersion::upgradeVersion();
            rename($upgrade_file, $renamed_upgrade_file);
            $current_url = (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $current_url = remove_query_arg('lxform-upgrade-done', $current_url);
            $redirect_url = add_query_arg('lxform-upgrade-done', '1', $current_url);
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
}, 1);



if (!function_exists('activate_leadxforms')) {
    function activate_leadxforms()
    {
        require_once plugin_dir_path(__FILE__) . '/includes/class-leadxforms-activator.php';
        LeadXForms_Activator::activate();
    }
}

if (!function_exists('deactivate_leadxforms')) {
    function deactivate_leadxforms()
    {
        require_once plugin_dir_path(__FILE__) . '/includes/class-leadxforms-deactivator.php';
        LeadXForms_Deactivator::deactivate();
    }
}


register_deactivation_hook(__FILE__, 'deactivate_leadxforms');
register_activation_hook(__FILE__, 'activate_leadxforms');


if (!function_exists('lxform_enqueue_select2_cdn')) {
    function lxform_enqueue_select2_cdn()
    {
        wp_enqueue_script('jquery-cdn', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', [], null, true);
        wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
        wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), null, true);

        $license_key = get_option('leadxforms_license_key');
        $user_id = get_current_user_id();
        $website = get_full_url();
        $apiUrl = apiUrl();
        echo "<meta name='sponser' content='{$apiUrl}'>" . PHP_EOL;
        echo "<meta name='wecui' content='{$user_id}'>" . PHP_EOL;
        echo "<meta name='lem-number' content='{$license_key}'>" . PHP_EOL;
        echo "<meta name='website-url' content='{$website}'>" . PHP_EOL;

    }
}

add_action('admin_enqueue_scripts', 'lxform_enqueue_select2_cdn');

if (!function_exists('custom_plugin_api_call')) {
    function custom_plugin_api_call()
    {
        $ipBlockedForm = new LeadXForms_WpAjax_FormBlockedIP('');
        return $ipBlockedForm->formInit();
    }

    add_action('init', function () {
        $api_data = custom_plugin_api_call();
        if (is_array($api_data)) {
            error_log(print_r($api_data, true));
        }
    });
}

if (!function_exists('run_leadxforms')) {
    require_once plugin_dir_path(__FILE__) . '/includes/helpers.php';
    require_once plugin_dir_path(__FILE__) . '/includes/class-leadxforms.php';
    function run_leadxforms()
    {
        $plugin = new LeadXForms();
        $plugin->run();
    }

    run_leadxforms();
}

