<?php
/**
 * Barangay Shortcodes
 */

if (!defined('ABSPATH')) exit;

class Barangay_Shortcodes {
    public static function init() {
        add_shortcode('barangay_viewer', array(__CLASS__, 'viewer_shortcode'));
        add_shortcode('barangay_uploader', array(__CLASS__, 'uploader_shortcode'));
        add_shortcode('featured_barangay', array(__CLASS__, 'featured_shortcode'));
    }

    public static function uploader_shortcode($atts) {
        if (!current_user_can('manage_options')) return '<p>Admin only.</p>';
        ob_start();
        display_barangay_uploader();
        return ob_get_clean();
    }

    public static function viewer_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => 12,
            'featured_only' => 'false',
        ), $atts);
        $args = array('posts_per_page' => absint($atts['posts_per_page']), 'featured_only' => $atts['featured_only'] === 'true');
        ob_start();
        display_barangay_viewer($args);
        return ob_get_clean();
    }

    public static function featured_shortcode($atts) {
        $atts = shortcode_atts(array('posts_per_page' => 6), $atts);
        ob_start();
        display_barangay_viewer(array('posts_per_page' => absint($atts['posts_per_page']), 'featured_only' => true));
        return ob_get_clean();
    }
}

Barangay_Shortcodes::init();
?>

