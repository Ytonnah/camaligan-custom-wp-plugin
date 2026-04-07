<?php
/**
 * Media Gallery Helper Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register media_gallery custom post type
 */
function register_media_gallery_post_type() {
    $labels = array(
        'name' => _x('Media Galleries', 'post type general name'),
        'singular_name' => _x('Media Gallery', 'post type singular name'),
        'add_new' => _x('Add New Gallery', 'post type'),
        'add_new_item' => __('Add New Gallery'),
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_in_menu' => false,
        'supports' => array('title', 'editor'),
        'can_export' => true,
    );

    register_post_type('media_gallery', $args);
}
add_action('init', 'register_media_gallery_post_type');

/**
 * Enqueue common styles
 */
function media_gallery_enqueue_styles() {
    wp_enqueue_style('media-gallery-common', plugin_dir_url(__FILE__) . 'css/media-gallery-common.css');
}
add_action('admin_enqueue_scripts', 'media_gallery_enqueue_styles');

/**
 * Get gallery by ID
 */
function get_media_gallery($gallery_id) {
    $gallery = get_post($gallery_id);
    if (!$gallery || $gallery->post_type !== 'media_gallery') {
        return null;
    }

    $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
    if (!is_array($gallery_images)) {
        $gallery_images = array();
    }

    return array(
        'id' => $gallery->ID,
        'title' => $gallery->post_title,
        'description' => $gallery->post_content,
        'images' => $gallery_images,
        'image_count' => count($gallery_images)
    );
}

/**
 * Get all galleries
 */
function get_all_media_galleries($per_page = -1) {
    $galleries = get_posts(array(
        'post_type' => 'media_gallery',
        'posts_per_page' => $per_page,
        'post_status' => 'publish'
    ));

    $result = array();
    foreach ($galleries as $gallery) {
        $result[] = get_media_gallery($gallery->ID);
    }

    return $result;
}

/**
 * Display gallery images
 */
function display_gallery_images($gallery_id, $class = '') {
    $gallery = get_media_gallery($gallery_id);
    if (!$gallery || empty($gallery['images'])) {
        return '';
    }

    $html = '<div class="gallery-images ' . esc_attr($class) . '">';
    foreach ($gallery['images'] as $image_id) {
        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        $caption = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $html .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($caption) . '" class="gallery-image">';
    }
    $html .= '</div>';

    return $html;
}
