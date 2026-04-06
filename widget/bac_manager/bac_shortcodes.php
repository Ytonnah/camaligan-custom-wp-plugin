<?php
/**
 * BAC Shortcodes - Register shortcodes for easy integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BAC_Shortcodes {

    public static function init() {
        add_shortcode('bac_uploader', array(__CLASS__, 'bac_uploader_shortcode'));
        add_shortcode('bac_viewer', array(__CLASS__, 'bac_viewer_shortcode'));
    }

    /**
     * Shortcode: [bac_uploader]
     * Display BAC upload form
     */
    public static function bac_uploader_shortcode($atts) {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return '<p>You do not have permission to upload BAC documents.</p>';
        }

        $uploader = BAC_Manager::get_instance()->get_uploader();
        
        ob_start();
        $uploader->display_upload_form();
        return ob_get_clean();
    }

    /**
     * Shortcode: [bac_viewer]
     * Display BAC viewer with list
     * 
     * Usage:
     *   [bac_viewer]
     *   [bac_viewer posts_per_page="15"]
     */
    public static function bac_viewer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
        ), $atts, 'bac_viewer');

        $viewer = BAC_Manager::get_instance()->get_viewer();
        
        ob_start();
        $viewer->display_bac_viewer($atts);
        return ob_get_clean();
    }
}

// Initialize shortcodes
add_action('init', array('BAC_Shortcodes', 'init'));
