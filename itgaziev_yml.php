<?php
/*
Plugin Name: Имопорт товаров из прайсов YML
Version: 0.2.0
Description: Работает с прайсам из платформы dropo.ru
Author: Газиев Ильёс
Author URI: mailto:gaziev.ilyos@yandex.ru
Plugin URI: https://it-gaziev.ru
Text Domain: IT Gaziev YML Prices
Domain Path: /languages
*/
if (!defined('ABSPATH')) exit;

define('ITGAZIEV_DIR', __DIR__);
//require_once ITGAZIEV_DIR . '/vendor/autoload.php';
require_once ABSPATH . "wp-admin/includes/plugin.php";
//Classes
require_once ITGAZIEV_DIR . '/ITGaziev_Plugin.php';
require_once ITGAZIEV_DIR . '/classes/ITGaziev_Import.php';
require_once ITGAZIEV_DIR . '/classes/ITGaziev_DB.php';
require_once ITGAZIEV_DIR . '/classes/ITGaziev_YML.php';

require_once ITGAZIEV_DIR . '/helper.php';
require_once ITGAZIEV_DIR . '/meta_field.php';
require_once ITGAZIEV_DIR . '/itgaziev_admin.php';
require_once ITGAZIEV_DIR . '/itgaziev_ajax.php';

function itgaziev_yml_init()
{
    if (!is_plugin_active("woocommerce/woocommerce.php")) {
        function itgaziev_yml_woocommerce_admin_notices()
        {
            $plugin_data = get_plugin_data(__FILE__);
            $message = sprintf(__("Plugin <strong>%s</strong> requires plugin <strong>WooCommerce</strong> to be installed and activated.", 'itgaizev_yml'), $plugin_data['Name']);
            printf('<div class="updated"><p>%s</p></div>', $message);
        }
        add_action('admin_notices', 'itgaziev_yml_woocommerce_admin_notices');
    }
}
add_action('init', 'itgaziev_yml_init');

//Загрузка плагина
function itgaziev_yml_activate()
{
    \ITGaziev\ITGaziev_Plugin::installPlugin();

    // for 1c
    // itgaziev_yml_1c_plugins();
}
register_activation_hook(__FILE__, 'itgaziev_yml_activate');