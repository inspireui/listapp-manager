<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>ListApp Setting</title>
    <script src="<?php echo esc_html(LISTAPP_SETTING_PLUGIN_URL."assets/js/jsoneditor.js") ?>"></script>
    <link href="<?php echo esc_html(LISTAPP_SETTING_PLUGIN_URL."assets/css/listapp-setting-style.css") ?>" rel="stylesheet"
          type="text/css"/>
    <script src="<?php echo esc_html(LISTAPP_SETTING_PLUGIN_URL."assets/js/listapp-setting-custom.js") ?>"></script>
</head>
<body>

<div id="head-full"><h1>ListApp Settings</h1></div>

<?php
$isChild = strstr(strtolower(wp_get_theme()), "child");
if($isChild == 'child'){
    $string = explode(" ", wp_get_theme());
    $currentTemplate = strtolower($string[0]) ;
}else{
    $currentTemplate = strtolower(wp_get_theme());
}

$typeJobListing  = 'job_listing_type';
if($currentTemplate == 'listify'){
    $typeJobListing = 'job_listing_region';
}else if($currentTemplate == 'my listing'){
    $typeJobListing = 'region';
}


$nonce = sanitize_text_field($_POST['_wpnonce']);
if (isset($_POST['submit']) && wp_verify_nonce( $nonce, 'inspireuiteam')) {
    $dataHome = sanitize_text_field($_POST['kqHome']);
    $dataMenu = sanitize_text_field($_POST['kqMenu']);
    $dataColor = sanitize_text_field($_POST['kqColor']);
    $dataGeneral = sanitize_text_field($_POST['kqGeneral']);
    // print_r(json_decode(stripcslashes($dataHome), true));
    $homepageLayoutSave = json_decode(stripcslashes($dataHome), true);
    $menuSave = json_decode(stripcslashes($dataMenu), true);
    $colorSave = json_decode(stripcslashes($dataColor), true);
    $generalSave = json_decode(stripcslashes($dataGeneral), true);
    // echo "<pre>";
    // print_r($homepageLayoutSave);
    // echo "</pre>";
    // return ;
    $result = array(
        'homepageLayout' => $homepageLayoutSave['homepageLayout'],
        'verticalLayout' => $homepageLayoutSave['verticalLayout'],
        'horizontalLayout' => $homepageLayoutSave['horizontalLayout'],
        'menu' => $menuSave,
        'color' => $colorSave,
        'general' => $generalSave,

    );
    update_option('_listapp_config', json_encode($result));
}
?>

<form method="post" class="frmSubmit">
    <input type="hidden" name="_wpnonce" value="<?php echo esc_html(wp_create_nonce('inspireuiteam')) ?>" />
    <div class="admin-panel">
        <div class="slidebar">
            <ul>
                <li><a href="" name="home">
                        <div><img src="<?= LISTAPP_SETTING_PLUGIN_URL . '/assets/images/icon-home.png' ?>"
                                  class="imageIcon"/></div>
                        HOME
                    </a></li>
                <li><a href="" name="menus">
                        <div><img src="<?= LISTAPP_SETTING_PLUGIN_URL . '/assets/images/icon-menu.png' ?>"
                                  class="imageIcon"/></div>
                        MENU</a></li>
                <!-- <li><a href="" name="images"><i class="fa fa-picture-o"></i>Images</a></li> -->
                <li><a href="" name="color">
                        <div><img src="<?= LISTAPP_SETTING_PLUGIN_URL . '/assets/images/icon-color.png' ?>"
                                  class="imageIcon"/></div>
                        COLORS</a></li>
                <li><a href="" name="general">
                        <div><img src="<?= LISTAPP_SETTING_PLUGIN_URL . '/assets/images/icon-setting.png' ?>"
                                  class="imageIcon"/></div>
                        GENERAL</a></li>
                <li><a href="" name="advance">
                        <div><img src="<?= LISTAPP_SETTING_PLUGIN_URL . '/assets/images/icon-advance.png' ?>"
                                  class="imageIcon"/></div>
                        ADVANCE</a></li>
            </ul>
        </div>

        <div class="main">
            <button type="submit" name="submit" class="btn btn-large" id="save">Save</button>
            <button class="btn btn-large" id="reset">Reset</button>

            <!-- <button id='submit' style="margin-left: 16px" class="large">Generate</button> -->
            <div id="home">
                <div class="wrap">
                    <div id='editor_template'></div>
                    <div id='editor_holder'></div>
                </div>
            </div>
            <div id="menus">
                <div class="wrap">
                    <div id='editor_holder_menu'></div>
                </div>
            </div>
            <div id="images">
                <div class="wrap">
                    <div id='editor_holder_image'></div>
                </div>
            </div>
            <div id="color">
                <div class="wrap">
                    <div id='editor_holder_color'></div>
                </div>
            </div>
            <div id="general">
                <div class="wrap">
                    <div id='editor_holder_general'></div>
                </div>
            </div>
            <div id="advance">
                <div class="wrap">
                    <div id="results">
                        <div class="textIntroAdvance">
                            This is advance feature that you can edit the JSON without using the layout, please be
                            careful with editing this section and make sure the format is correctly
                        </div>
                        <div class="block_result">
                            <b>Home</b>
                            <textarea class="block_textarea" name="kqHome" id="kq_home"></textarea>
                        </div>
                        <div class="block_result">
                            <b>Menu</b>
                            <textarea class="block_textarea" name="kqMenu" id="kq_menu"></textarea>
                        </div>
                        <!-- <div class="block_result">
                          <b>Image</b>
                          <textarea class="block_textarea" name="kqImage" id="kq_image"></textarea>
                        </div> -->
                        <div class="block_result">
                            <b>Color</b>
                            <textarea class="block_textarea" name="kqColor" id="kq_color"></textarea>
                        </div>
                        <div class="block_result">
                            <b>General</b>
                            <textarea class="block_textarea" name="kqGeneral" id="kq_general"></textarea>
                        </div>
                    </div>

                </div> <!-- .end wrap -->
            </div>  <!-- end advance -->
        </div> <!-- end.main-->
    </div>
</form>

<script>
    // This is the starting value for the editor
    // We will use this to seed the initial editor
    // and to provide a "Restore to Default" button.
    <?php
    $config = json_decode(get_option('_listapp_config'), true);

    foreach ($config as $k => $item):
        $value = $item;
        if (is_string($value)) {
            $value = json_decode(stripslashes($value));
        }
        $config[$k] = $value;
    endforeach;

    $homepageLayout = $config['homepageLayout'] ? $config['homepageLayout'] : 1;
    $verticalLayout = $config['verticalLayout'] ? $config['verticalLayout'] : 1;
    $horizontalLayout = $config['horizontalLayout'];
    $menu = $config['menu'];
    $color = $config['color'];
    $general = $config['general'];
    ?>

    var starting_value = [
            <?php
            foreach ($horizontalLayout as $item):
                echo "{";
                foreach ($item as $k => $item2):
                    echo "'" . esc_html($k) . "': '" . esc_html($item2) . "',";
                endforeach;
                echo "},\n";
            endforeach;
            ?>
    ];

    var starting_Menu = [
        <?php
        // print_r($menu);
        foreach($menu as $kPrev => $item):
            echo '{';
            foreach ($item as $k => $item2):
                if($k == 'params'){
                    if(is_array($item['params']) && count($item['params']) > 0){
                        echo "'params':";
                            echo "{'title': '". esc_html($item['name']). "',";
                            if($item['params']['id']){
                                echo "'id': ". esc_html($item['params']['id'])."},";
                            }else{
                                echo "},";
                            }
                    }
                }else{
                    echo "'".esc_html($k)."': '".esc_html($item2)."',";
                }
            endforeach;
            echo "},\n";
            
        endforeach;
        ?>
    ];

    var editorTemplate = new JSONEditor(document.getElementById('editor_template'), {
        // Enable fetching schemas via ajax
        ajax: true,

        // The schema for the editor
        schema: {
            type: "object",
            title: " ",
            format: "string", // table or grid
            properties: {
                "homepageLayout": {
                    "type": "number",
                    "enum": [1, 2, 3],
                    'options': {
                        'enum_titles': ['Horizontal', 'Vertical', 'Mansory']
                    },
                    'default': <?php echo esc_html($homepageLayout) ?>,
                    'title': 'Home Layout',
                },
                "verticalLayout": {
                    "title": "Type",
                    "type": "number",
                    "format": 'object',
                    "enum": [1, 4, 2, 5, 9, 11, 3, 10],
                    "options": {
                        "enum_titles": [
                            "Card", 'Banner',
                            "One Column", "Two Column", "Three Column",  "Flexible Column",
                            "Listing Align Left", "Listing Align Right"
                        ]
                    },
                    'default': <?php echo esc_html($verticalLayout) ?>,
                }
            }
          
        },
    });

    // Initialize the editor HomePage
    var editor = new JSONEditor(document.getElementById('editor_holder'), {
        // Enable fetching schemas via ajax
        ajax: true,

        // The schema for the editor
        schema: {
            type: "array",
            title: " ",
            format: "string", // table or grid
            items: {
                title: "Row ",
                type: "object",
                properties: {
                    "component": {
                        "type": "string",
                        "enum": [
                            "listing",
                            "map",
                            "news",
                        ],
                        'options': {
                            'enum_titles': ['Listing', 'Map', 'News']
                        },
                        'title': 'Component',
                        "default": "listing"
                    },
                    "name": {
                        "title": "Title",
                        "type": "string",
                    },
                    "layout": {
                        "title": "Layout",
                        "type": "number",
                        "format": 'object',
                        "enum": [1, 2, 3, 4, 5, 6, 7, 8, 9],
                        "options": {
                            "enum_titles": [
                                "Card", "Card Trend", 'Banner',
                                "One Column", "Two Column", "Three Column",  "Flexible Column",
                                "Listing Align Left", "Listing Align Right"
                            ]
                        },
                        "default": 1,
                        "description": 'The app is support to display varies of layout',
                    },
                    "typeId": {
                        "title": "Type",
                        "type": "string",
                        'enum': ['', <?php $terms = get_terms($typeJobListing); foreach ($terms as $item):
                            echo esc_html($item->term_id). ", ";
                        endforeach;?>],
                        'options': {
                            'enum_titles': ['Choose', <?php $terms = get_terms($typeJobListing); foreach ($terms as $item):
                                echo "'" . esc_html($item->name) . "', ";
                            endforeach;?>]
                        },
                        'default': '',
                        'description': 'Select this value if the is Listing ',
                    },
                    "tags": {
                        "title": "Tags",
                        "type": "string",
                        'enum': ['', <?php $terms = get_terms('case27_job_listing_tags'); foreach ($terms as $item):
                            echo esc_html($item->term_id). ", ";
                        endforeach;?>],
                        'options': {
                            'enum_titles': ['Choose', <?php $terms = get_terms('case27_job_listing_tags'); foreach ($terms as $item):
                                echo "'" . esc_html($item->name) . "', ";
                            endforeach;?>]
                        },
                        'default': '',
                        'description': 'Only apply for myListing theme',
                    },
                    "categoryListingId": {
                        "title": "Category Listing",
                        "type": "number",
                        "description": 'Optional, select this value if the Compoent is Listing',
                        'enum': ['', <?php $terms = get_terms('job_listing_category'); foreach ($terms as $item):
                            echo esc_html($item->term_id) . ", ";
                        endforeach;?>],
                        'options': {
                            'enum_titles': ['Choose', <?php $terms = get_terms('job_listing_category'); foreach ($terms as $item):
                                echo "'" . esc_html($item->name ). "', ";
                            endforeach;?>]
                        },
                        'default': '',
                    },
                    "categoryNewsId": {
                        "title": "Category News",
                        "type": "number",
                        "description": 'Optional,  selected this value if the Component is News',
                        'enum': ['', <?php $terms = get_categories(array('exclude' => 1)); foreach ($terms as $item):
                            echo esc_html($item->term_id ). ", ";
                        endforeach;?>],
                        'options': {
                            'enum_titles': ['Choose', <?php $terms = get_categories(array('exclude' => 1)); foreach ($terms as $item):
                                echo "'" . esc_html($item->name) . "', ";
                            endforeach;?>]
                        },
                    },
                    "width": {
                        "title": "Width",
                        "type": "number",
                        'description': 'Only apply for Flex Collumn Layout',
                    },
                    "height": {
                        "title": "Height",
                        "type": "number",
                        'description': 'Only apply for Flex Collumn Layout',
                    },
                    "paging": {
                        "title": "Paging",
                        "type": "boolean",
                        "format": "checkbox",
                        'description': 'Select Yes if the scroll is set as sticky ',
                    },
                    "row": {
                        "title": "Row",
                        "type": "number",
                        'description': 'This value is only support for column layout ',
                    },

                } // end properties
            } // end all items
        }, // end schema

        // Seed the form with a starting value
        startval: starting_value,
        // Disable additional properties
        no_additional_properties: true,
        // Require all properties by default
        required_by_default: true,
        remove_empty_properties: true,
        disable_array_reorder: true,
        disable_edit_json: true,
    });

    // Initialize the editor Menu
    var editorMenu = new JSONEditor(document.getElementById('editor_holder_menu'), {
        // Enable fetching schemas via ajax
        ajax: true,
        // The schema for the editor
        schema: {
            type: "array",
            title: " ",
            format: "string", // table or grid
            items: {
                title: "Row",
                type: "object",
                properties: {
                    "route": {
                        "type": "string",
                        'title': 'Navigation',
                        "default": "home",
                        "enum": ["home", "categories", "map", "search", "photo", "readlater", "setting", "login", "customPage"],
                        "options": {
                            "enum_titles": ["Home", "Categories", "Map", "Search", "Photo", "Read Later", "Setting", "Login", "Custom Page"]
                        },

                    },
                    "name": {
                        "title": "Menu Name",
                        "type": "string",
                        'description': 'Name of the menu item',
                    },
                    "params": {
                        "title": "Params",
                        "type": "object",
                        'description': 'This setting only apply for CustomPage Navigation',
                        'properties': {                           
                            'id': {
                                'type': 'string',
                                'title': 'Post ID',
                                'description': 'ID of the post content ',
                                'default': '',
                            },
                            'url': {
                                'type': 'string',
                                'title': 'Url',
                                'description': 'Link to any web page',
                                'default': '',
                            },
                        },
                    }

                } // end properties
            } // end all items


        }, // end schema

        // theme: 'foundation5',

        // Seed the form with a starting value
        startval: starting_Menu,

        // Disable additional properties
        no_additional_properties: true,

        // Require all properties by default
        required_by_default: true,
        remove_empty_properties: true,
        disable_array_reorder: true,
        disable_edit_json: true,
    });


    // Initialize the editor Color
    var editorColor = new JSONEditor(document.getElementById('editor_holder_color'), {
        // Enable fetching schemas via ajax
        ajax: true,

        // The schema for the editor
        schema: {
            type: "object",
            title: " ",
            format: "string", // table or grid
            properties: {
                "mainColorTheme": {
                    "type": "string",
                    'title': 'Main Color',
                    "format": 'color',
                    'description': 'This is a description'
                },
                "tabbar": {
                    "type": "string",
                    'title': 'TabBar',
                    "format": 'color',
                    'description': 'This is a description',
                    'default': '#FFFFFF',
                },
                "tabbarTint": {
                    "type": "string",
                    'title': 'TabBar Tint',
                    "format": 'color',
                    'description': 'This is a description'
                },
                "tabbarColor": {
                    "type": "string",
                    'title': 'TabBar Color',
                    "format": 'color',
                    'description': 'This is a description'
                },
                // "calendars": {
                //   "title":' Calendars',
                //   "type": 'object',
                //   "properties": {
                //       "dayNum": {
                //         'type': 'string',
                //         'title': 'Day Num',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "dayText": {
                //         'type': 'string',
                //         'title': 'Day Text',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "selectBackground": {
                //         'type': 'string',
                //         'title': 'Select Background',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "today": {
                //         'type': 'string',
                //         'title': 'Today',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "dot": {
                //         'type': 'string',
                //         'title': 'Dot Color',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "textColor": {
                //         'type': 'string',
                //         'title': 'Text Color',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //       "monthTextColor": {
                //         'type': 'string',
                //         'title': 'Month Text Color',
                //         "format": 'color',
                //         'description': 'This is a description'
                //       },
                //   }// end prop
                // }, // end calendars

                // "map": {
                //   'type': 'object',
                //   'title': 'Map',
                //   'properties': {
                //     'marker': {
                //       'type': 'string',
                //       'title': 'Marker',
                //       'format': 'color',
                //       'description': 'This is a description'
                //     },
                //     'markerActive': {
                //       'type': 'string',
                //       'title': 'Marker Active',
                //       'format': 'color',
                //       'description': 'This is a description'
                //     },
                //     'defaultPinColor': {
                //       'type': 'string',
                //       'title': 'Default Pin Color',
                //       'format': 'color',
                //       'description': 'This is a description',
                //     },
                //   } // end props
                // }, // end map

            } // end all props
        }, // end schema

        // theme: 'foundation5',

        // Seed the form with a starting value
        startval: {
            <?php
            foreach ($color as $kPrev => $item):

                if (is_object($item)) {
                    echo "'" . esc_html($kPrev) . "': {\n";
                    foreach ($item as $k => $item2):
                        echo "'" . esc_html($k) . "': '" . esc_html($item2) . "', \n";
                    endforeach;
                    echo "}, \n";
                } else {
                    echo "'" . esc_html($kPrev) . "': '". esc_html($item)."', \n";
                }
                
            endforeach;
            ?>
        },

        // Disable additional properties
        no_additional_properties: true,

        // Require all properties by default
        required_by_default: true,
        remove_empty_properties: true,
        disable_edit_json: true,
    });

    // Initialize the editor General
    var editorGeneral = new JSONEditor(document.getElementById('editor_holder_general'), {
        // Enable fetching schemas via ajax
        ajax: true,

        // The schema for the editor
        schema: {
            type: "object",
            title: " ",
            format: "string", // table or grid
            properties: {
                "Firebase": {
                    "type": "object",
                    'title': 'Firebase',
                    'properties': {
                        'apiKey': {
                            'type': 'string',
                            'title': 'Api Key',
                        },
                        'authDomain': {
                            'type': 'string',
                            'title': 'Auth Domain',
                        },
                        'databaseURL': {
                            'type': 'string',
                            'title': 'Database URL',

                        },
                        'storageBucket': {
                            'type': 'string',
                            'title': 'Storage Bucket',
                        },
                        'messagingSenderId': {
                            'type': 'string',
                            'title': 'Messaging SenderId',
                        },
                        'readlaterTable': {
                            'type': 'string',
                            'title': 'Table',
                        },

                    }
                }, // end block
                "Facebook": {
                    "type": "object",
                    'title': 'Facebook',
                    'properties': {
                        'visible': {
                            "title": "Visible",
                            "type": "boolean",
                            "format": "checkbox",
                        },
                        'adPlacementID': {
                            'type': 'string',
                            'title': 'AdPlacement ID',
                        },
                        'logInID': {
                            'type': 'string',
                            'title': 'Login ID',

                        },
                        'sizeAds': {
                            'type': 'string',
                            'enum': ['standard', 'large'],
                            'title': 'Size Ads',
                        },
                    }
                }, // end block
                "AdMob": {
                    "type": "object",
                    'title': 'AdMob',
                    'properties': {
                        'visible': {
                            "title": "Visible",
                            "type": "boolean",
                            "format": "checkbox",
                        },
                        'deviceID': {
                            'type': 'string',
                            'title': 'Device ID',
                        },
                        'unitID': {
                            'type': 'string',
                            'title': 'Unit ID',
                        },
                        'unitInterstitial': {
                            'type': 'string',
                            'title': 'UnitInterstitial',
                        },
                        'isShowInterstital': {
                            'title': 'Show Interstital',
                            "type": "boolean",
                            "format": "checkbox",

                        }
                    }
                }, // end block

            } // end all items
        }, // end schema

        // theme: 'foundation5',
        // Seed the form with a starting value
        startval: {
            <?php
            foreach ($general as $kPrev => $item):
                echo "'" . esc_html($kPrev) . "': {";
                foreach ($item as $k => $item2):
                    echo "'" . esc_html($k) . "': '" . esc_html($item2) . "',";
                endforeach;
                echo "},\n";
            endforeach;
            ?>
        },

        // Disable additional properties
        no_additional_properties: true,

        // Require all properties by default
        required_by_default: true,
        remove_empty_properties: true,
        disable_edit_json: true,
    });

    jQuery(document).ready(function () {
        // init all fields value when start form
        
        let home = Object.assign({}, editorTemplate.getValue(), {
            'horizontalLayout': editor.getValue()
        });

        document.getElementById('kq_home').value = JSON.stringify(home, undefined, 7);
        document.getElementById('kq_menu').value = JSON.stringify(editorMenu.getValue(), undefined, 7);
        document.getElementById('kq_color').value = JSON.stringify(editorColor.getValue(), undefined, 7);
        document.getElementById('kq_general').value = JSON.stringify(editorGeneral.getValue(), undefined, 7);

        // add listener by event
        editorTemplate.on('change', function () { 
            home = Object.assign({}, editorTemplate.getValue(), {
                'horizontalLayout': editor.getValue()
            });
            document.getElementById('kq_home').value = JSON.stringify(home, undefined, 7);


            /* --- event template --- */
            let $this = editorTemplate.getEditor('root.homepageLayout').getValue();
            // console.log($this);
            if($this == 2){
                jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'block');
                jQuery('#editor_holder').css('display', 'none');
            }else if($this == 1){
                jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'none');
                jQuery('#editor_holder').css('display', 'block');
            }else{
                jQuery('#editor_holder').css('display', 'none');
                jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'none');
            }
       
        });

        editor.on('change', function () {
            home = Object.assign({}, editorTemplate.getValue(),  {
                'horizontalLayout': editor.getValue()
            });
            document.getElementById('kq_home').value = JSON.stringify(home, undefined, 7);
        });



        
        editorMenu.on('change', function () {
            document.getElementById('kq_menu').value = JSON.stringify(editorMenu.getValue(), null, 7);
        });
        editorColor.on('change', function () { // console.log(editor.getValue());
            document.getElementById('kq_color').value = JSON.stringify(editorColor.getValue(), null, 7);
        });
        editorGeneral.on('change', function () { // console.log(editor.getValue());
            document.getElementById('kq_general').value = JSON.stringify(editorGeneral.getValue(), null, 7);
        });


        jQuery('.slidebar li a[name="home"]').click(function(e){
            let $this = editorTemplate.getEditor('root.homepageLayout').getValue();
            // console.log($this);
            if($this == 1){
                jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'none');
            }
        });

        document.getElementById('save').addEventListener('click', function () {
            jQuery(".frmSubmit").submit();
        });

        // Hook up the Restore to Default button
        document.getElementById('reset').addEventListener('click', function (e) {
            e.preventDefault();
            let comment = 'Are you sure? This will clear your all settings and recover the default data.';
            if(window.confirm(comment)){
                // reset the same as local
                editorTemplate.setValue({
                    homepageLayout: 1,
                    verticalLayout: 2,
                });
                editor.setValue([
                  {
                    component: 'listing',
                    // categoryListingId: "33",
                    paging: true,
                    layout: 3,
                  },
                  { component: 'map' },
                  {
                    component: 'listing',
                    name: 'Eat & Drink',
                    // typeId: "63",
                    layout: 5,
                  },
                  {
                    component: 'listing',
                    name: 'Visit',
                    // typeId: "66",
                    paging: true,
                    row: 3,
                    layout: 8,
                  },
                  {
                    component: 'listing',
                    name: 'Stay',
                    // typeId: "65",
                    layout: 4,
                  },
                  {
                    component: 'listing',
                    name: 'Shops',
                    // typeId: "64",
                    layout: 7,
                    width: 120,
                    height: 250,
                  },
                  {
                    component: 'news',
                    name: 'Videos',
                    // categoryNewsId: "160",
                    paging: true,
                    layout: 1,
                  },

                  {
                    component: 'news',
                    name: 'Tips & Articles',
                    paging: true,
                    row: 3,
                    layout: 9,
                  },
                ]);
                
                editorMenu.setValue([
                  {
                    route: 'home',
                    name: 'Explore',
                  },
                  {
                    route: 'setting',
                    name: 'Settings',
                  },
                  {
                    route: 'customPage',
                    params: {
                      title: 'Contact',
                      url: 'https://inspireui.com/about',
                    },
                    name: 'Contact',
                  },
                  {
                    route: 'customPage',
                    params: {
                      title: 'About Us',
                      url: 'https://inspireui.com/about',
                    },
                    name: 'About Us',
                    icon: 'assignment',
                  },
                  {
                    route: 'login',
                    name: 'Sign In',
                  },
                ]);
                editorColor.setValue({
                  mainColorTheme: '#000000',
                  tabbar: '#ffffff',
                  tabbarTint: '#3bc651',
                  tabbarColor: '#929292',
                });
                editorGeneral.setValue({
                      Firebase: {
                        apiKey: 'AIzaSyAZhwel4Nd4T5dSmGB3fI_MUJj6BIz5Kk8',
                        authDomain: 'beonews-ef22f.firebaseapp.com',
                        databaseURL: 'https://beonews-ef22f.firebaseio.com',
                        storageBucket: 'beonews-ef22f.appspot.com',
                        messagingSenderId: '1008301626030',
                        readlaterTable: 'list_readlater',
                      },
                      Facebook: {
                        visible: false,
                        adPlacementID: '1809822172592320_1981610975413438',
                        logInID: '1809822172592320',
                        sizeAds: 'standard', // standard, large
                      },
                      AdMob: {
                        visible: false,
                        deviceID: 'pub-2101182411274198',
                        unitID: 'ca-app-pub-2101182411274198/8802887662',
                        unitInterstitial: 'ca-app-pub-2101182411274198/7326078867',
                        isShowInterstital: true,
                      }
                });
            }else{
                return false;
            }
         
        });

        jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'none');
        <?php
            if($homepageLayout == 2):
        ?>
            jQuery('#editor_holder').css('display', 'none');
            jQuery('div[data-schemapath="root.verticalLayout"').css('display', 'block');
        <?php 
            elseif($homepageLayout == 3):
        ?>
            jQuery('#editor_holder').css('display', 'none');
        <?php 
            endif;
        ?>

        jQuery('div > button.json-editor-btn-collapse:visible').click()
    })


    //style for change select component news/listing
    var $cateNews = editor.getEditor('root.0.categoryNewsId');
    var $cateListing = editor.getEditor('root.0.categoryListingId');
    // $cateNews.disable();
    jQuery('select[name="root[0][component]"]').on('change', function () {
        let valChange = jQuery(this).val();
        if (valChange == 'news') {
            console.log('vao', valChange)
            $cateNews.enable();
            $cateListing.disable();
        } else {
            $cateNews.disable();
            $cateListing.enable();
        }
    });

    //set for Tabs
    jQuery(".main div").hide();
    jQuery('#home .wrap, #home .wrap div').fadeIn();
    jQuery('#home .property-selector').hide();
    jQuery('#home .json-editor-btn-save').parent().hide();
    jQuery(".slidebar li:first").attr("id", "active");
    jQuery(".main div:first").fadeIn();
    jQuery('.slidebar a').click(function (e) {
        e.preventDefault();
        if (jQuery(this).closest("li").attr("id") == "active") {

            return
        } else {
            jQuery(".main div").hide();

            jQuery(".slidebar li").attr("id", "");

            jQuery(this).parent().attr("id", "active");

            let $name = jQuery(this).attr('name');
            jQuery('#' + $name).fadeIn();
            jQuery('#' + $name + ' div').show();
            jQuery('.property-selector').hide();
            jQuery('.json-editor-btn-save').parent().hide();
        }
    });

</script>


</body>
</html>