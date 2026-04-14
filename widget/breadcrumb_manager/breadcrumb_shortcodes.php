<?php
/**
 * Breadcrumb Shortcodes
 */

class Breadcrumb_Shortcodes {
    public static function init() {
        add_shortcode('breadcrumb', array(__CLASS__, 'breadcrumb_shortcode'));
    }

    public static function breadcrumb_shortcode($atts) {
        $atts = shortcode_atts(array(
            'separator' => ' / ',
            'show_home' => 'true',
            'show_current' => 'true',
            'home_label' => 'Home'
        ), $atts);

        // Create temp instance for shortcode
        $instance = array(
            'separator' => $atts['separator'],
            'show_home' => $atts['show_home'] === 'true',
            'show_current' => $atts['show_current'] === 'true',
            'home_label' => $atts['home_label']
        );

        $widget = new Breadcrumb_Widget();
        ob_start();
        echo $widget->get_breadcrumbs($instance);
        return ob_get_clean();
    }
}
Breadcrumb_Shortcodes::init();
?>

