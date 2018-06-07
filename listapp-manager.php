<?php
/**
 * Plugin Name: ListApp Mobile Manager
 * Plugin URI: https://github.com/inspireui/listapp-manager
 * Description: The ListApp Settings and APIs for supporting the Listing Directory mobile app by React Native framework
 * Version: 1.0.2
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

    protected $_slugOS = 'push-notification';
    protected $_pageTitleOS = 'Push Notification';
    protected $_menuTitleOS = 'Push Notification';
    /*
    * ListAppSetting constructor
    */

    public function __construct()
    {
        define('LISTAPP_SETTING', $this->version);
        define('LISTAPP_PLUGIN_FILE', __FILE__);

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
            add_submenu_page($this->_slugPage,
                          $this->_pageTitleOS, 
                          $this->_menuTitleOS,
                          'manage_options',
                          $this->_slugOS,
                          array($this, 'output_pushNotification'));

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
     * Call the function to display view
     */
    public function display_setting()
    {
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/templates/setting-template.php';
    }

    public function output_pushNotification(){
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/templates/push-notification.php';
    }

    /**
    * Load Template When Active
    */

    public function load_layout(){
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/controllers/mstore-checkout.php';
        require_once LISTAPP_SETTING_PLUGIN_PATH . '/rest-api/class.api.fields.php';
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
                ['component' => 'listing', "paging" => true, 'layout' => 1],
                ['component' => 'map'],
                ['component' => 'listing', 'name' => 'Eat & Drink', 'layout' => 5],
                ['component'=> 'listing', 'name'=> 'Visit', 'paging'=> true, 'row'=> 3, 'layout'=> 8],
                ['component'=> 'listing','name'=> 'Stay', 'layout'=> 4],
                ['component'=> 'listing', 'name'=> 'Shops', 'layout'=> 7, 'width'=> 120, 'height'=> 250],
                ['component'=> 'news', 'name'=> 'Videos', 'paging'=> true, 'layout'=> 1],
                ['component'=> 'news', 'name'=> 'Tips & Articles', 'paging'=> true, 'row'=> 3, 'layout'=> 9]
            ],
            'menu' => [
                (Object)[
                    'route' =>'home',
                    'name' => 'Explore',
                ],
                (Object)[
                    'route' =>'setting',
                    'name' => 'Settings',
                ],
                (Object)[
                    'route' =>'customPage',
                    'name' => 'Contact',
                    'params' => (Object)[
                        'title' => 'Contact', 
                        'url'=> 'https://inspireui.com/about'
                    ],

                ],
                (Object)[
                    'route' =>'customPage',
                    'name' => 'About Us',
                    'params' => (Object)[
                        'title' => 'Contact', 
                        'url'=> 'https://inspireui.com/about'
                    ],
                    'icon' => 'assignment',

                ],
                (Object)[
                    'route' =>'login',
                    'name' => 'Sign In',
                ],

            ],
            'color' => (Object)[
                  'mainColorTheme'=> '#000000',
                  'tabbar' => '#ffffff',
                  'tabbarTint' => '#3bc651',
                  'tabbarColor' => '#929292',
            ],
            'general' => (Object)[
                'Firebase' => (Object)[
                        'apiKey'=> 'AIzaSyAZhwel4Nd4T5dSmGB3fI_MUJj6BIz5Kk8',
                        'authDomain'=> 'beonews-ef22f.firebaseapp.com',
                        'databaseURL'=> 'https://beonews-ef22f.firebaseio.com',
                        'storageBucket'=> 'beonews-ef22f.appspot.com',
                        'messagingSenderId'=> '1008301626030',
                        'readlaterTable'=> 'list_readlater',
                ],
                "Facebook" => (Object)[
                        'visible'=> false,
                        'adPlacementID'=> '1809822172592320_1981610975413438',
                        'logInID'=> '1809822172592320',
                        'sizeAds'=> 'standard', // standard, large
                ],
                "AdMob" => (Object)[
                        'visible'=> false,
                        'deviceID'=> 'pub-2101182411274198',
                        'unitID'=> 'ca-app-pub-2101182411274198/8802887662',
                        'unitInterstitial'=> 'ca-app-pub-2101182411274198/7326078867',
                        'isShowInterstital'=> true,
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

