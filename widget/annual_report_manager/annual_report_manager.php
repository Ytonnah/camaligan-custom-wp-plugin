<?php
/**
 * Annual Report Manager - Main controller
 */

if (!defined('ABSPATH')) {
    exit;
}

class Annual_Report_Manager {

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
        add_action('init', array($this, 'register_report_post_type'));

        require_once dirname(__FILE__) . '/annual_report_uploader.php';
        require_once dirname(__FILE__) . '/annual_report_viewer.php';
        require_once dirname(__FILE__) . '/annual_report_rest_api.php';

        $this->uploader = new Annual_Report_Uploader();
        $this->viewer = new Annual_Report_Viewer();
        new Annual_Report_REST_API();
    }

    public function register_report_post_type() {
        $args = array(
            'label' => 'Annual Reports',
            'description' => 'Annual report PDF files',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => array('slug' => 'annual-reports'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('annual_report', $args);
    }

    public function get_uploader() {
        return $this->uploader;
    }

    public function get_viewer() {
        return $this->viewer;
    }

    public static function get_reports($args = array()) {
        $defaults = array(
            'post_type' => 'annual_report',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        );

        return new WP_Query(wp_parse_args($args, $defaults));
    }

    public static function get_recent_reports($limit = 5) {
        return self::get_reports(array(
            'posts_per_page' => $limit,
        ));
    }

    public static function format_annual_report_item($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'annual_report') {
            return null;
        }

        $pdf_id = absint(get_post_meta($post_id, 'annual_report_pdf_id', true));

        return array(
            'id' => (int) $post_id,
            'title' => get_the_title($post_id),
            'status' => $post->post_status,
            'date' => get_the_date('c', $post_id),
            'modified' => get_the_modified_date('c', $post_id),
            'year' => (int) get_post_meta($post_id, 'annual_report_year', true),
            'pdf_id' => $pdf_id,
            'pdf_url' => $pdf_id ? wp_get_attachment_url($pdf_id) : '',
            'pdf_title' => $pdf_id ? get_the_title($pdf_id) : '',
        );
    }
}

Annual_Report_Manager::get_instance();
