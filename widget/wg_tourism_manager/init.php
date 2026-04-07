<?php
/**
 * Tourism Manager Widget - Main Integration File
 * Include this file in your plugin's main file or functions.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include tourism manager classes
require_once __DIR__ . '/tourism_manager.php';
require_once __DIR__ . '/tourism_shortcodes.php';

/**
 * Helper function to get tourism uploader
 */
if (!function_exists('get_tourism_uploader')) {
    function get_tourism_uploader() {
        return Tourism_Manager::get_instance()->get_uploader();
    }
}

/**
 * Helper function to get tourism viewer
 */
if (!function_exists('get_tourism_viewer')) {
    function get_tourism_viewer() {
        return Tourism_Manager::get_instance()->get_viewer();
    }
}

/**
 * Helper function to display uploader form
 */
if (!function_exists('display_tourism_uploader')) {
    function display_tourism_uploader() {
        get_tourism_uploader()->display_upload_form();
    }
}

/**
 * Helper function to display tourism viewer
 */
if (!function_exists('display_tourism_viewer')) {
    function display_tourism_viewer($args = array()) {
        get_tourism_viewer()->display_tourism_viewer($args);
    }
}

/**
 * Helper function to display single tourism item
 */
if (!function_exists('display_single_tourism')) {
    function display_single_tourism($post_id) {
        get_tourism_viewer()->display_single_tourism($post_id);
    }
}
