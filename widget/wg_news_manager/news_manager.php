<?php
/**
 * News Manager - Main controller for news functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class News_Manager {

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
     * Initialize the news manager
     */
    private function init() {
        // Register custom post type
        add_action('init', array($this, 'register_news_post_type'));
        add_action('init', array($this, 'register_news_taxonomies'));

        // Include uploader and viewer classes
        require_once dirname(__FILE__) . '/news_uploader.php';
        require_once dirname(__FILE__) . '/news_viewer.php';

        $this->uploader = new News_Uploader();
        $this->viewer = new News_Viewer();
    }

    /**
     * Register News Item custom post type
     */
    public function register_news_post_type() {
        $args = array(
            'label' => 'News Items',
            'description' => 'News and updates',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'news'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('news_item', $args);
    }

    /**
     * Register custom taxonomies
     */
    public function register_news_taxonomies() {
        register_taxonomy(
            'news_type',
            'news_item',
            array(
                'label' => 'News Types',
                'public' => true,
                'show_in_rest' => true,
                'rewrite' => array('slug' => 'news-type'),
            )
        );
    }

    /**
     * Get news uploader instance
     */
    public function get_uploader() {
        return $this->uploader;
    }

    /**
     * Get news viewer instance
     */
    public function get_viewer() {
        return $this->viewer;
    }

    /**
     * Get all news items
     */
    public static function get_news($args = array()) {
        $defaults = array(
            'post_type' => 'news_item',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        $args = wp_parse_args($args, $defaults);
        return new WP_Query($args);
    }

    /**
     * Get featured news items
     */
    public static function get_featured_news($limit = 5) {
        return self::get_news(array(
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'news_featured',
                    'value' => 1,
                    'compare' => '='
                )
            )
        ));
    }

    /**
     * Get news by category
     */
    public static function get_news_by_category($category, $limit = 10) {
        return self::get_news(array(
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'news_category',
                    'value' => $category,
                    'compare' => '='
                )
            )
        ));
    }

    /**
     * Get news by priority
     */
    public static function get_news_by_priority($priority, $limit = 10) {
        return self::get_news(array(
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => 'news_priority',
                    'value' => $priority,
                    'compare' => '='
                )
            )
        ));
    }

    /**
     * Get recent news
     */
    public static function get_recent_news($limit = 5) {
        return self::get_news(array(
            'posts_per_page' => $limit
        ));
    }
}

// Initialize the news manager
News_Manager::get_instance();
