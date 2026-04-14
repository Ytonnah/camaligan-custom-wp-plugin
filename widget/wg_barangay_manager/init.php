<?php
/**
 * Barangay Profile Manager - Main Integration File
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('register_barangay_profile_post_type')) {
    function register_barangay_profile_post_type() {
        $labels = array(
            'name' => _x('Barangay Profiles', 'post type general name'),
            'singular_name' => _x('Barangay Profile', 'post type singular name'),
            'add_new' => _x('Add New', 'barangay profile'),
            'add_new_item' => __('Add New Barangay Profile'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'barangay-profile'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => false,
        );

        register_post_type('barangay_profile', $args);
    }
}
add_action('init', 'register_barangay_profile_post_type');

require_once __DIR__ . '/barangay_uploader.php';
require_once __DIR__ . '/barangay_manager.php';
require_once __DIR__ . '/barangay_viewer.php';
require_once __DIR__ . '/barangay_shortcodes.php';
require_once __DIR__ . '/barangay_rest_api.php';

new Barangay_REST_API();

if (!function_exists('render_barangay_manager_page')) {
    function render_barangay_manager_page() {
        include __DIR__ . '/mainmenu.php';
    }
}

if (!function_exists('get_barangay_uploader')) {
    function get_barangay_uploader() {
        static $uploader = null;
        if (!$uploader) {
            $uploader = new Barangay_Uploader();
        }
        return $uploader;
    }
}

if (!function_exists('get_barangay_viewer')) {
    function get_barangay_viewer() {
        static $viewer = null;
        if (!$viewer) {
            $viewer = new Barangay_Viewer();
        }
        return $viewer;
    }
}

if (!function_exists('display_barangay_uploader')) {
    function display_barangay_uploader() {
        get_barangay_uploader()->display_upload_form();
    }
}

if (!function_exists('display_barangay_viewer')) {
    function display_barangay_viewer($args = array()) {
        get_barangay_viewer()->display_barangay_viewer($args);
    }
}