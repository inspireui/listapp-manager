<?php

// Get theme info
switch (get_template()) {
    case 'listify':
        require_once __DIR__ . '/rest-api-listify.php';
        break;
    case 'my-listing':
        require_once __DIR__ . '/rest-api-mylisting.php';
        break;
    default:
        require_once __DIR__ . '/rest-api-listable.php';
}

add_action('init', 'add_events_to_json_api', 30);
add_action('rest_api_init', 'register_add_more_fields_to_rest_api');