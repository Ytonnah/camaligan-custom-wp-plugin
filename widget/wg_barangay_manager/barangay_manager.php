<?php
/**
 * Barangay Profile Manager - Shared helpers
 */

if (!defined('ABSPATH')) {
    exit;
}

class Barangay_Manager {
    private static $instance = null;

    private $uploader;
    private $viewer;

    private function __construct() {
        $this->uploader = new Barangay_Uploader();
        $this->viewer = new Barangay_Viewer();
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_uploader() {
        return $this->uploader;
    }

    public function get_viewer() {
        return $this->viewer;
    }

    public static function format_barangay_profile_item($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'barangay_profile') {
            return null;
        }

        $image_id = get_post_thumbnail_id($post_id);

        return array(
            'id' => (int) $post_id,
            'name' => get_the_title($post_id),
            'barangay_profile' => $post->post_content,
            'barangay_profile_html' => apply_filters('the_content', $post->post_content),
            'image_id' => (int) $image_id,
            'image_url' => $image_id ? wp_get_attachment_image_url($image_id, 'large') : '',
            'origin_of_name' => (string) get_post_meta($post_id, 'barangay_origin_of_name', true),
            'demographic_profile' => (string) get_post_meta($post_id, 'barangay_demographic_profile', true),
            'date' => get_the_date('c', $post_id),
            'modified' => get_the_modified_date('c', $post_id),
            'link' => get_permalink($post_id),
        );
    }
}