<?php
/**
 * Budget Overview Manager - Main controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class Budget_Overview_Manager {

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

    private function init() {
        add_action('init', array($this, 'register_budget_post_type'));

        require_once dirname(__FILE__) . '/budget_overview_uploader.php';
        require_once dirname(__FILE__) . '/budget_overview_viewer.php';

        $this->uploader = new Budget_Overview_Uploader();
        $this->viewer = new Budget_Overview_Viewer();
    }

    public function register_budget_post_type() {
        $args = array(
            'label' => 'Budget Overviews',
            'description' => 'Budget overview PDF files',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'budget-overviews'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('budget_overview', $args);
    }

    public function get_uploader() {
        return $this->uploader;
    }

    public function get_viewer() {
        return $this->viewer;
    }

    public static function get_budgets($args = array()) {
        $defaults = array(
            'post_type' => 'budget_overview',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        );

        return new WP_Query(wp_parse_args($args, $defaults));
    }

    public static function get_recent_budgets($limit = 5) {
        return self::get_budgets(array(
            'posts_per_page' => $limit,
        ));
    }
}

Budget_Overview_Manager::get_instance();
