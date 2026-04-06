<?php
/**
 * BAC Manager - Main controller for BAC functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BAC_Manager {

    private static $instance = null;
    private $uploader;
    private $viewer;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the BAC manager
     */
    private function init() {
        // Register custom post type
        add_action('init', array($this, 'register_bac_post_type'));

        // Include uploader and viewer classes
        require_once dirname(__FILE__) . '/bac_uploader.php';
        require_once dirname(__FILE__) . '/bac_viewer.php';

        $this->uploader = new BAC_Uploader();
        $this->viewer = new BAC_Viewer();
    }

    /**
     * Register BAC Item custom post type
     */
    public function register_bac_post_type() {
        $args = array(
            'label' => 'BAC Items',
            'description' => 'Bids and Commissions Documents',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'bac'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('bac_item', $args);
    }

    /**
     * Get BAC uploader instance
     */
    public function get_uploader() {
        return $this->uploader;
    }

    /**
     * Get BAC viewer instance
     */
    public function get_viewer() {
        return $this->viewer;
    }

    /**
     * Get all BAC items
     */
    public static function get_bac($args = array()) {
        $defaults = array(
            'post_type' => 'bac_item',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        $args = wp_parse_args($args, $defaults);
        return new WP_Query($args);
    }

    /**
     * Get recent BAC items
     */
    public static function get_recent_bac($limit = 5) {
        return self::get_bac(array(
            'posts_per_page' => $limit
        ));
    }
}

// Initialize the BAC manager
BAC_Manager::get_instance();
