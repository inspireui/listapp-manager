<?php
/**
 * Plugin Name: ListApp Mobile Manager
 * Plugin URI: http://inspireui.com
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

require_once __DIR__ . '/controllers/mstore-checkout.php';
require_once __DIR__ . '/controllers/job-manager-better-images.php';
require_once __DIR__ . '/listapp-setting.php';
require_once __DIR__ . '/rest-api/index.php';

