<?php
/**
 * Tourism Shortcodes - Register shortcodes for easy integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tourism_Shortcodes {

    public static function init() {
        add_shortcode('tourism_uploader', array(__CLASS__, 'tourism_uploader_shortcode'));
        add_shortcode('tourism_viewer', array(__CLASS__, 'tourism_viewer_shortcode'));
        add_shortcode('featured_tourism', array(__CLASS__, 'featured_tourism_shortcode'));
    }

    /**
     * Shortcode: [tourism_uploader]
     * Display tourism upload form
     */
    public static function tourism_uploader_shortcode($atts) {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return '<p>You do not have permission to upload tourism destinations.</p>';
        }

        $uploader = Tourism_Manager::get_instance()->get_uploader();
        
        ob_start();
        $uploader->display_upload_form();
        return ob_get_clean();
    }

    /**
     * Shortcode: [tourism_viewer]
     * Display tourism viewer with list and filters
     * 
     * Usage:
     *   [tourism_viewer]
     *   [tourism_viewer posts_per_page="20"]
     *   [tourism_viewer featured_only="true"]
     */
    public static function tourism_viewer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 12,
            'featured_only' => false,
        ), $atts, 'tourism_viewer');

        $viewer = Tourism_Manager::get_instance()->get_viewer();
        
        ob_start();
        $viewer->display_tourism_viewer($atts);
        return ob_get_clean();
    }

    /**
     * Shortcode: [featured_tourism]
     * Display featured tourism destinations only
     * 
     * Usage:
     *   [featured_tourism]
     *   [featured_tourism posts_per_page="5"]
     */
    public static function featured_tourism_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 6,
        ), $atts, 'featured_tourism');

        $atts['featured_only'] = true;

        $viewer = Tourism_Manager::get_instance()->get_viewer();
        
        ob_start();
        $viewer->display_tourism_viewer($atts);
        return ob_get_clean();
    }
}

// Initialize shortcodes
Tourism_Shortcodes::init();
