<?php
/**
 * Plugin Name: ListApp Mobile Manager
 * Plugin URI: https://github.com/inspireui/listapp-manager
 * Description: The ListApp Settings and APIs for supporting the Listing Directory mobile app by React Native framework
 * Version: 1.0.0
 * Author: InspireUI
 * Author URI: http://inspireui.com
 *
 * Text Domain: listapp
 */


if (!defined('ABSPATH')) {
    exit;
}


class ListAppSetting
{
    public $version = '1.1.0';
    protected $_textDomain = 'listapp-setting';
    protected $_pageTitle = 'ListApp Settings';
    protected $_menuTitle = 'ListApp Settings';
    protected $_slugPage = 'listapp-setting';
    protected $_routeApi = 'inspireui/v1';
    protected $_routeApiUrl = 'config';

    /*
    * ListAppSetting constructor
    */

    public function __construct()
    {
        define('LISTAPP_SETTING', $this->version);
        define('PLUGIN_FILE', __FILE__);

        //extra for define constants
        define('LISTAPP_SETTING_VERSION', '1.0.0');
        define('LISTAPP_SETTING_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('LISTAPP_SETTING_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('LISTAPP_SETTING_PLUGIN_URL_JS', plugin_dir_url(__FILE__) . 'assets/js/');

        add_action('admin_menu', function () {
            // add menu item to settings page
            add_menu_page(__($this->_pageTitle, $this->_textDomain),
                          __($this->_menuTitle, $this->_textDomain),
                          'manage_options', $this->_slugPage,
                          array($this, 'display_setting'), 
                          'dashicons-location');

        });

        // add custom url to rest_api if have
        add_action('rest_api_init', function () {
            register_rest_route($this->_routeApi, $this->_routeApiUrl, array(
                'methods' => 'GET',
                'callback' => array($this, 'get_config_layouts'),
            ));
        });

        //allow comments
        add_filter('rest_allow_anonymous_comments', '__return_true');

        //autoload templates 
        $this->load_layout();
        $this->set_config_default();
    }

    /**
    * Load Template When Active
    */

    public function load_layout(){
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/controllers/mstore-checkout.php';
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/rest-api/template.php';
    }

    /**
     * Call the function to display view
     */
    public function display_setting()
    {
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/templates/setting-template.php';
    }

    /**
     * Set default config for the app
     */
    public function set_config_default()
    {
        //set option default when active
        $result = [
            'homepageLayout' => 1,
            'verticalLayout' => 2,
            'horizontalLayout' => [
                ['component' => 'listing', 'layout' => 1],
                ['component' => 'map'],
                ['component' => 'listing', 'layout' => 2],
                ['component' => 'news', 'layout' => 3],
            ],
            'menu' => [
                (Object)[
                    'route' =>'home',
                    'name' => 'Discover',
                ],
                (Object)[
                    'route' =>'setting',
                    'name' => 'Setting',
                ],
                (Object)[
                    'route' =>'customPage',
                    'name' => 'About Us',
                    'params' => (Object)[
                            'id' => 1,
                            'title' => 'About Us', 
                            'url'=> ''
                    ],

                ],
                (Object)[
                    'route' =>'login',
                    'name' => 'Sign In',
                ],

            ],
            'color' => (Object)[
                 "mainColorTheme" => "#000000",
                 "tabbar"=>"#ffffff",
                 "tabbarTint"=> "#000000",
                 "tabbarColor"=> "#000000"
            ],
            'general' => (Object)[
                "Facebook" => (Object)[
                      "visible" => 'false',
                      "sizeAds" => "standard"
                ],
                "AdMob" => (Object)[
                      "visible" => 'false',
                      "isShowInterstital" => 'false'
                ]
            ],
        ];

        // var_dump(get_option('_listapp_config'));
        if(get_option('_listapp_config') == ''){
            update_option('_listapp_config', json_encode($result));
        }
    }

    /**
     * Call the function return to api json
     * @param $data
     * @return array|mixed|object
     */
    public function get_config_layouts()
    {
        $layouts = get_option('_listapp_config', array());
        $result = json_decode($layouts);
        if (empty($layouts)) {
            return [];
        }
        foreach($result->menu as $item):
            foreach($item as $key => $item2):
                if($key == 'params' && is_array($item->params) && count($item->params) == 0){
                    unset($item->params);
                }
            endforeach;
        endforeach;
        // foreach ($layouts as $k => $item):
        //     $layouts[$k] = json_decode(stripslashes($item));
        // endforeach;

        return $result;
    }
}

$listApp = new ListAppSetting();

