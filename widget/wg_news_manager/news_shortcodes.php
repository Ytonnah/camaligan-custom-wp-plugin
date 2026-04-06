<?php
/**
 * News Shortcodes - Register shortcodes for easy integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class News_Shortcodes {

    public static function init() {
        add_shortcode('news_uploader', array(__CLASS__, 'news_uploader_shortcode'));
        add_shortcode('news_viewer', array(__CLASS__, 'news_viewer_shortcode'));
        add_shortcode('featured_news', array(__CLASS__, 'featured_news_shortcode'));
    }

    /**
     * Shortcode: [news_uploader]
     * Display news upload form
     */
    public static function news_uploader_shortcode($atts) {
        // Only show to administrators
        if (!current_user_can('manage_options')) {
            return '<p>You do not have permission to upload news.</p>';
        }

        $uploader = News_Manager::get_instance()->get_uploader();
        
        ob_start();
        $uploader->display_upload_form();
        return ob_get_clean();
    }

    /**
     * Shortcode: [news_viewer]
     * Display news viewer with list and filters
     * 
     * Usage:
     *   [news_viewer]
     *   [news_viewer posts_per_page="15"]
     */
    public static function news_viewer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 10,
            'featured_only' => false,
        ), $atts, 'news_viewer');

        $viewer = News_Manager::get_instance()->get_viewer();
        
        ob_start();
        $viewer->display_news_viewer($atts);
        return ob_get_clean();
    }

    /**
     * Shortcode: [featured_news]
     * Display featured news items
     * 
     * Usage:
     *   [featured_news]
     *   [featured_news limit="3"]
     */
    public static function featured_news_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 5,
        ), $atts, 'featured_news');

        $viewer = News_Manager::get_instance()->get_viewer();
        
        ob_start();
        $viewer->display_news_viewer(array(
            'featured_only' => true,
            'posts_per_page' => $atts['limit']
        ));
        return ob_get_clean();
    }
}

// Initialize shortcodes
add_action('init', array('News_Shortcodes', 'init'));
