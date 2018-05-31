<?php

/*
  Controller name: Mstore Checkout
  Controller description: Controller that extend from Mstore User
  Controller Author: InspireUI
*/
// use JO\Module\Templater\Templater;
class MstoreCheckOut
{

    public function __construct()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php') == false) {
            return 0;
        }

        // require_once __DIR__ . '/../wp-templater/src/Templater.php';
        require_once __DIR__ . '/../templates/class-mobile-detect.php';
        add_action('wp_print_scripts', array($this, 'handle_received_order_page'));
    }

    public function handle_received_order_page()
    {
        if (is_order_received_page()) {
            $detect = new Mobile_Detect;
            if ($detect->isMobile()) {
                wp_register_style('mstore-order-custom-style', plugins_url('assets/css/mstore-order-style.css', LISTAPP_PLUGIN_FILE));
                wp_enqueue_style('mstore-order-custom-style');

                // default return true for getting checkout library working
                add_filter('woocommerce_is_checkout', '__return_true');

            }
        }

    }
}

$mstoreCheckOut = new MstoreCheckOut();

add_action('plugins_loaded', 'load_templater');
if(!function_exists('load_templater')){
    function load_templater()
    {
        include LISTAPP_SETTING_PLUGIN_PATH."wp-templater/src/Templater.php"; 
        // add our new custom templates
        $my_templater =  new Templater(
            array(
                // YOUR_PLUGIN_DIR or plugin_dir_path(__FILE__)
                'plugin_directory' => plugin_dir_path(__FILE__),
                // should end with _ > prefix_
                'plugin_prefix' => 'plugin_prefix_',
                // templates directory inside your plugin
                'plugin_template_directory' => 'templates',
            )
        );
        $my_templater->add(
            array(
                'page' => array(
                    'checkout.php' => 'Page Custom Template',
                ),
            )
        )->register();
    }
}

///////////////////////////////////////////////////////////////////////////////////////////////////
// Define for the API User wrapper which is based on json api user plugin
///////////////////////////////////////////////////////////////////////////////////////////////////

if (!is_plugin_active('json-api/json-api.php')) {
    add_action('admin_notices', 'pim_draw_notice_json_api');
    return;
}

add_filter('json_api_controllers', 'registerJsonApiController');
add_filter('json_api_mstore_user_controller_path', 'setMstoreUserControllerPath');
add_action('init', 'json_api_mstore_user_checkAuthCookie', 100);

if(!function_exists('registerJsonApiController')){
    function registerJsonApiController($aControllers)
    {
        $aControllers[] = 'Mstore_User';
        return $aControllers;
    }
}

if(!function_exists('setMstoreUserControllerPath')){
    function setMstoreUserControllerPath()
    {
        return dirname(__FILE__) . '/mstore-user.php';
    }
}

if(!function_exists('json_api_mstore_user_checkAuthCookie')){

    function json_api_mstore_user_checkAuthCookie()
    {
        global $json_api;

        if ($json_api->query->cookie) {
            $user_id = wp_validate_auth_cookie($json_api->query->cookie, 'logged_in');
            if ($user_id) {
                $user = get_userdata($user_id);
                wp_set_current_user($user->ID, $user->user_login);
            }
        }
    }
}