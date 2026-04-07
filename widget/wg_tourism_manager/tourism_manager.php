<?php
/**
 * Tourism Manager - Main controller for tourism functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tourism_Manager {

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
     * Initialize the tourism manager
     */
    private function init() {
        // Register custom post type
        add_action('init', array($this, 'register_tourism_post_type'));
        add_action('init', array($this, 'register_tourism_taxonomies'));

        // Include uploader and viewer classes
        require_once dirname(__FILE__) . '/tourism_uploader.php';
        require_once dirname(__FILE__) . '/tourism_viewer.php';

        $this->uploader = new Tourism_Uploader();
        $this->viewer = new Tourism_Viewer();
    }

    /**
     * Register Tourism Item custom post type
     */
    public function register_tourism_post_type() {
        $args = array(
            'label' => 'Tourism Items',
            'description' => 'Tourism attractions, destinations and information',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'tourism'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('tourism_item', $args);
    }

    /**
     * Register custom taxonomies
     */
    public function register_tourism_taxonomies() {
        register_taxonomy(
            'tourism_type',
            'tourism_item',
            array(
                'label' => 'Tourism Types',
                'public' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'tourism-type'),
            )
        );
    }

    /**
     * Get tourism uploader instance
     */
    public function get_uploader() {
        return $this->uploader;
    }

    /**
     * Get tourism viewer instance
     */
    public function get_viewer() {
        return $this->viewer;
    }
}

// Initialize the manager
Tourism_Manager::get_instance();
