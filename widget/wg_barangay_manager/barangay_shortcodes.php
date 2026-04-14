<?php
/**
 * Barangay Profile Shortcodes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Barangay_Shortcodes {
    public static function init() {
        add_shortcode('barangay_profile', array(__CLASS__, 'viewer_shortcode'));
        add_shortcode('barangay_viewer', array(__CLASS__, 'viewer_shortcode'));
        add_shortcode('barangay_uploader', array(__CLASS__, 'uploader_shortcode'));
    }

    public static function uploader_shortcode($atts) {
        if (!current_user_can('manage_options')) {
            return '<p>Admin only.</p>';
        }

        ob_start();
        display_barangay_uploader();
        return ob_get_clean();
    }

    public static function viewer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 12,
        ), $atts, 'barangay_profile');

        ob_start();
        display_barangay_viewer(array(
            'posts_per_page' => max(1, absint($atts['posts_per_page'])),
        ));
        return ob_get_clean();
    }
}

Barangay_Shortcodes::init();