<?php
/**
 * Municipal Ordinance Manager - Main controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class Municipal_Ordinance_Manager {

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
        add_action('init', array($this, 'register_ordinance_post_type'));

        require_once dirname(__FILE__) . '/municipal_ordinance_uploader.php';
        require_once dirname(__FILE__) . '/municipal_ordinance_viewer.php';
        require_once dirname(__FILE__) . '/municipal_ordinance_rest_api.php';

        $this->uploader = new Municipal_Ordinance_Uploader();
        $this->viewer = new Municipal_Ordinance_Viewer();
        new Municipal_Ordinance_REST_API();
    }

    public function register_ordinance_post_type() {
        $args = array(
            'label' => 'Municipal Ordinances',
            'description' => 'Municipal ordinance PDF files',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'municipal-ordinances'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('municipal_ordinance', $args);
    }

    public function get_uploader() {
        return $this->uploader;
    }

    public function get_viewer() {
        return $this->viewer;
    }

    public static function get_ordinances($args = array()) {
        $defaults = array(
            'post_type' => 'municipal_ordinance',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        );

        return new WP_Query(wp_parse_args($args, $defaults));
    }

    public static function get_recent_ordinances($limit = 5) {
        return self::get_ordinances(array(
            'posts_per_page' => $limit,
        ));
    }

    public static function format_ordinance_item($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'municipal_ordinance') {
            return null;
        }

        $pdf_id = absint(get_post_meta($post_id, 'municipal_ordinance_pdf_id', true));

        return array(
            'id' => (int) $post_id,
            'title' => get_the_title($post_id),
            'status' => $post->post_status,
            'date' => get_the_date('c', $post_id),
            'modified' => get_the_modified_date('c', $post_id),
            'pdf_id' => $pdf_id,
            'pdf_url' => $pdf_id ? wp_get_attachment_url($pdf_id) : '',
            'pdf_title' => $pdf_id ? get_the_title($pdf_id) : '',
        );
    }
}

Municipal_Ordinance_Manager::get_instance();
