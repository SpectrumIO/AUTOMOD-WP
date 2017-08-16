<?php
/*
Plugin Name: Spectrum - Intelligent Moderation
Plugin URI: https://www.getspectrum.io
Description: Use cutting-edge artificial intelligence to automatically protect your site from inappropriate comments.
Version: 1.0.0
Author: Spectrum Labs, Inc.
Author URI: https://www.getspectrum.io
License: GPLv2 or later
Text Domain: spectrum
*/

if (!function_exists('add_action')) {
    print 'This is a WordPress plugin. You should not call it directly.';
    exit;
}

define('AUTOMOD_VERSION', '1.0.0');
define('AUTOMOD__PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AUTOMOD__API_BASE_URL', 'https://api.prod.getspectrum.io/api/v1/');
define('AUTOMOD__API_KEY_OPTION_NAME', 'automod-api-key');

require_once(AUTOMOD__PLUGIN_DIR . 'class.spectrum-api.php');
require_once(AUTOMOD__PLUGIN_DIR . 'class.automod.php');
require_once(AUTOMOD__PLUGIN_DIR . 'class.automod-admin.php');

add_action('init', array('Automod', 'init'));

if (is_admin()) {
    add_action('init', array('Automod_Admin', 'init'));
}

register_uninstall_hook(__FILE__, array('Automod', 'cleanup'));
