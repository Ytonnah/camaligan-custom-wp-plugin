<?php
/**
 * Beneficiaries Manager Helper Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once 'beneficiaries_uploader.php';
require_once 'beneficiaries_viewer.php';
require_once 'beneficiaries_shortcodes.php';

/**
 * Register beneficiary_item custom post type
 */
function register_beneficiary_post_type() {
    $labels = array(
        'name' => _x('Beneficiaries', 'post type general name'),
        'singular_name' => _x('Beneficiary', 'post type singular name'),
        'add_new' => _x('Add New Beneficiary', 'post type'),
        'add_new_item' => __('Add New Beneficiary'),
    );

    $args = array(
        'labels' => $labels,
        'public' => false,
        'publicly_queryable' => true,
        'show_in_menu' => false,
        'show_in_rest' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'can_export' => true,
    );

    register_post_type('beneficiary_item', $args);
}
add_action('init', 'register_beneficiary_post_type');

/**
 * Get all beneficiaries
 */
function get_all_beneficiaries($per_page = -1) {
    return get_posts(array(
        'post_type' => 'beneficiary_item',
        'posts_per_page' => $per_page,
        'post_status' => 'publish'
    ));
}

/**
 * Get beneficiary by ID
 */
function get_beneficiary($beneficiary_id) {
    $beneficiary = get_post($beneficiary_id);
    if (!$beneficiary || $beneficiary->post_type !== 'beneficiary_item') {
        return null;
    }

    return array(
        'ID' => $beneficiary->ID,
        'name' => $beneficiary->post_title,
        'description' => $beneficiary->post_content,
        'barangay' => get_post_meta($beneficiary->ID, 'beneficiary_barangay', true),
        'type' => get_post_meta($beneficiary->ID, 'beneficiary_type', true),
        'contact' => get_post_meta($beneficiary->ID, 'beneficiary_contact', true),
        'program' => get_post_meta($beneficiary->ID, 'beneficiary_program', true),
        'date' => get_post_meta($beneficiary->ID, 'beneficiary_date', true),
        'status' => get_post_meta($beneficiary->ID, 'beneficiary_status', true),
        'image_id' => get_post_thumbnail_id($beneficiary->ID)
    );
}

/**
 * Get beneficiaries by type
 */
function get_beneficiaries_by_type($type) {
    return get_posts(array(
        'post_type' => 'beneficiary_item',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'beneficiary_type',
                'value' => $type,
                'compare' => '='
            )
        )
    ));
}

/**
 * Get beneficiaries by barangay
 */
function get_beneficiaries_by_barangay($barangay) {
    return get_posts(array(
        'post_type' => 'beneficiary_item',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'beneficiary_barangay',
                'value' => $barangay,
                'compare' => '='
            )
        )
    ));
}

/**
 * Get beneficiaries by status
 */
function get_beneficiaries_by_status($status) {
    return get_posts(array(
        'post_type' => 'beneficiary_item',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'beneficiary_status',
                'value' => $status,
                'compare' => '='
            )
        )
    ));
}

/**
 * Get unique barangays
 */
function get_all_barangays() {
    global $wpdb;
    $results = $wpdb->get_results("
        SELECT DISTINCT pm.meta_value
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'beneficiary_item'
        AND pm.meta_key = 'beneficiary_barangay'
        ORDER BY pm.meta_value
    ");

    return wp_list_pluck($results, 'meta_value');
}

/**
 * Get beneficiary count
 */
function get_beneficiary_count($status = '') {
    $args = array(
        'post_type' => 'beneficiary_item',
        'post_status' => 'publish'
    );

    if (!empty($status)) {
        $args['meta_query'] = array(
            array(
                'key' => 'beneficiary_status',
                'value' => $status,
                'compare' => '='
            )
        );
    }

    return count(get_posts($args));
}
