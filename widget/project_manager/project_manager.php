<?php
/**
 * Project Manager - Main controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class Project_Manager {

    private static $instance = null;

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
        add_action('init', array($this, 'register_project_post_type'));
        add_action('init', array($this, 'register_project_status_taxonomy'));
        add_action('init', array($this, 'ensure_default_project_statuses'), 20);

        require_once dirname(__FILE__) . '/project_rest_api.php';

        new Project_REST_API();
    }

    public function register_project_post_type() {
        $args = array(
            'label' => 'Projects',
            'description' => 'Infrastructure project records',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'projects'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'excerpt', 'custom-fields', 'thumbnail'),
            'show_in_rest' => false,
        );

        register_post_type('project', $args);
    }

    public function register_project_status_taxonomy() {
        $args = array(
            'labels' => array(
                'name' => 'Project Statuses',
                'singular_name' => 'Project Status',
                'search_items' => 'Search Project Statuses',
                'all_items' => 'All Project Statuses',
                'edit_item' => 'Edit Project Status',
                'update_item' => 'Update Project Status',
                'add_new_item' => 'Add New Project Status',
                'new_item_name' => 'New Project Status',
                'menu_name' => 'Project Statuses',
            ),
            'public' => true,
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => false,
            'rewrite' => array('slug' => 'project-status'),
        );

        register_taxonomy('project_status', array('project'), $args);
    }

    public function ensure_default_project_statuses() {
        foreach (self::get_allowed_statuses() as $slug => $label) {
            if (!term_exists($slug, 'project_status')) {
                wp_insert_term($label, 'project_status', array('slug' => $slug));
            }
        }
    }

    public static function get_allowed_statuses() {
        return array(
            'awarded' => 'Awarded',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
        );
    }

    public static function get_projects($args = array()) {
        $defaults = array(
            'post_type' => 'project',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        );

        return new WP_Query(wp_parse_args($args, $defaults));
    }

    public static function get_recent_projects($limit = 5) {
        return self::get_projects(array(
            'posts_per_page' => $limit,
        ));
    }

    public static function get_status_counts() {
        $counts = array();

        foreach (self::get_allowed_statuses() as $slug => $label) {
            $term = get_term_by('slug', $slug, 'project_status');
            $counts[$slug] = $term && !is_wp_error($term) ? (int) $term->count : 0;
        }

        return $counts;
    }

    public static function format_project_item($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'project') {
            return null;
        }

        $terms = get_the_terms($post_id, 'project_status');
        $statuses = array();

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $statuses[] = array(
                    'id' => (int) $term->term_id,
                    'slug' => $term->slug,
                    'name' => $term->name,
                );
            }
        }

        return array(
            'id' => (int) $post_id,
            'title' => get_the_title($post_id),
            'content' => apply_filters('the_content', $post->post_content),
            'excerpt' => get_the_excerpt($post_id),
            'date' => get_the_date('c', $post_id),
            'modified' => get_the_modified_date('c', $post_id),
            'link' => get_permalink($post_id),
            'statuses' => $statuses,
            'contractor' => (string) get_post_meta($post_id, 'project_contractor', true),
            'contract_amount' => (string) get_post_meta($post_id, 'project_contract_amount', true),
            'timeline' => (string) get_post_meta($post_id, 'project_timeline', true),
            'completion_percent' => (int) get_post_meta($post_id, 'project_completion_percent', true),
            'target_date' => (string) get_post_meta($post_id, 'project_target_date', true),
        );
    }
}

Project_Manager::get_instance();
