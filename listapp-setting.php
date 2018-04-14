<?php
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

    /**
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
                array($this, 'display_setting'), 'dashicons-location');

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

        $this->set_config_default();
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
                    'icon'=> 'home'
                ],
                (Object)[
                    'route' =>'setting',
                    'name' => 'Setting',
                    'icon'=> 'settings'
                ],
                (Object)[
                    'route' =>'customPage',
                    'name' => 'About Us',
                    'params' => (Object)[
                            'id' => 1,
                            'title' => 'About Us', 
                            'url'=> ''
                    ],
                    'icon'=> 'home'
                ],
                (Object)[
                    'route' =>'login',
                    'name' => 'Sign In',
                    'icon'=> 'user'
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
    public function get_config_layouts($data)
    {
        $layouts = get_option('_listapp_config', array());

        if (empty($layouts)) {
            return [];
        }
        // foreach ($layouts as $k => $item):
        //     $layouts[$k] = json_decode(stripslashes($item));
        // endforeach;

        return json_decode($layouts);
    }
}

$listApp = new ListAppSetting();

