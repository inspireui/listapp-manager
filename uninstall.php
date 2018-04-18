<?php
//this check makes sure that this file is called manually.
if (!defined("WP_UNINSTALL_PLUGIN")) {
    exit();
}


if(get_option('_listapp_config')){
    delete_option('_listapp_config');
}