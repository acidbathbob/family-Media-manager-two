<?php
/**
 * Plugin Name: Family Media Manager
 * Plugin URI: https://github.com/yourusername/family-media-manager
 * Description: A family-friendly media sharing system with cloud storage integration
 * Version: 0.1.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: family-media-manager
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('FAMILY_MEDIA_MANAGER_VERSION', '0.1.0');

// Plugin directory path
define('FAMILY_MEDIA_MANAGER_PATH', plugin_dir_path(__FILE__));

// Plugin directory URL
define('FAMILY_MEDIA_MANAGER_URL', plugin_dir_url(__FILE__));

/**
 * Plugin activation hook
 */
function activate_family_media_manager() {
    require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-activator.php';
    Family_Media_Manager_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_family_media_manager');

/**
 * Plugin deactivation hook
 */
function deactivate_family_media_manager() {
    require_once FAMILY_MEDIA_MANAGER_PATH . 'includes/class-deactivator.php';
    Family_Media_Manager_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_family_media_manager');

/**
 * The core plugin class
 */
require FAMILY_MEDIA_MANAGER_PATH . 'includes/class-family-media-manager.php';

/**
 * Begin execution of the plugin
 */
function run_family_media_manager() {
    $plugin = new Family_Media_Manager();
    $plugin->run();
}
run_family_media_manager();
