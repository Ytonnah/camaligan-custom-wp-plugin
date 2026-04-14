<?php
/**
 * Barangay Profile REST API
 */

if (!defined('ABSPATH')) {
    exit;
}

class Barangay_REST_API {
    const API_NAMESPACE = 'wp/v2';
    const REST_BASE = '/barangay-profiles';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route(self::API_NAMESPACE, self::REST_BASE, array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => '__return_true',
                'args' => $this->get_collection_args(),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_item'),
                'permission_callback' => array($this, 'can_manage_barangay_profiles'),
                'args' => $this->get_write_args(false),
            ),
        ));

        register_rest_route(self::API_NAMESPACE, self::REST_BASE . '/(?P<id>\\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => '__return_true',
                'args' => $this->get_single_item_args(),
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array($this, 'update_item'),
                'permission_callback' => array($this, 'can_manage_barangay_profiles'),
                'args' => $this->get_write_args(true),
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array($this, 'delete_item'),
                'permission_callback' => array($this, 'can_manage_barangay_profiles'),
                'args' => $this->get_single_item_args(),
            ),
        ));
    }

    private function get_collection_args() {
        return array(
            'page' => array('default' => 1, 'sanitize_callback' => 'absint'),
            'per_page' => array('default' => 10, 'sanitize_callback' => array($this, 'sanitize_per_page')),
            'search' => array('sanitize_callback' => 'sanitize_text_field'),
            'orderby' => array('default' => 'title', 'sanitize_callback' => array($this, 'sanitize_orderby')),
            'order' => array('default' => 'ASC', 'sanitize_callback' => array($this, 'sanitize_order')),
        );
    }

    private function get_single_item_args() {
        return array(
            'id' => array(
                'required' => true,
                'sanitize_callback' => 'absint',
                'validate_callback' => function ($value) {
                    return absint($value) > 0;
                },
            ),
        );
    }

    private function get_write_args($include_id) {
        $args = array(
            'name' => array('sanitize_callback' => 'sanitize_text_field'),
            'title' => array('sanitize_callback' => 'sanitize_text_field'),
            'barangay_profile' => array('sanitize_callback' => 'wp_kses_post'),
            'profile' => array('sanitize_callback' => 'wp_kses_post'),
            'image_id' => array('sanitize_callback' => 'absint'),
            'featured_image_id' => array('sanitize_callback' => 'absint'),
            'origin_of_name' => array('sanitize_callback' => 'wp_kses_post'),
            'demographic_profile' => array('sanitize_callback' => 'wp_kses_post'),
            'status' => array('sanitize_callback' => 'sanitize_key'),
        );

        if ($include_id) {
            $args['id'] = $this->get_single_item_args()['id'];
        }

        return $args;
    }

    public function sanitize_per_page($value) {
        $value = absint($value);
        return min(max($value, 1), 50);
    }

    public function sanitize_order($value) {
        $value = strtoupper((string) $value);
        return in_array($value, array('ASC', 'DESC'), true) ? $value : 'ASC';
    }

    public function sanitize_orderby($value) {
        $value = sanitize_key((string) $value);
        return in_array($value, array('date', 'title', 'modified'), true) ? $value : 'title';
    }

    public function can_manage_barangay_profiles() {
        return current_user_can('manage_options');
    }

    public function get_items(WP_REST_Request $request) {
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));

        $query = new WP_Query(array(
            'post_type' => 'barangay_profile',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => (string) $request->get_param('search'),
            'orderby' => $this->sanitize_orderby($request->get_param('orderby')),
            'order' => $this->sanitize_order($request->get_param('order')),
        ));

        $items = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = Barangay_Manager::format_barangay_profile_item(get_the_ID());
            }
            wp_reset_postdata();
        }

        return rest_ensure_response(array(
            'items' => array_values(array_filter($items)),
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total_items' => (int) $query->found_posts,
                'total_pages' => (int) $query->max_num_pages,
            ),
        ));
    }

    public function get_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'barangay_profile' || $post->post_status !== 'publish') {
            return new WP_Error('barangay_profile_not_found', 'Barangay profile not found.', array('status' => 404));
        }

        return rest_ensure_response(Barangay_Manager::format_barangay_profile_item($post_id));
    }

    public function create_item(WP_REST_Request $request) {
        $prepared = $this->prepare_barangay_profile_data($request, false);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $image_id = $prepared['image_id'];
        unset($prepared['image_id']);

        $post_id = wp_insert_post($prepared, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        if ($image_id > 0) {
            set_post_thumbnail($post_id, $image_id);
        }

        return new WP_REST_Response(array(
            'message' => 'Barangay profile created successfully.',
            'item' => Barangay_Manager::format_barangay_profile_item($post_id),
        ), 201);
    }

    public function update_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'barangay_profile') {
            return new WP_Error('barangay_profile_not_found', 'Barangay profile not found.', array('status' => 404));
        }

        $prepared = $this->prepare_barangay_profile_data($request, true, $post_id);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $image_id = $prepared['image_id'];
        unset($prepared['image_id']);

        $updated = wp_update_post($prepared, true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        if ($request->has_param('image_id') || $request->has_param('featured_image_id')) {
            if ($image_id > 0) {
                set_post_thumbnail($post_id, $image_id);
            } else {
                delete_post_thumbnail($post_id);
            }
        }

        return rest_ensure_response(array(
            'message' => 'Barangay profile updated successfully.',
            'item' => Barangay_Manager::format_barangay_profile_item($post_id),
        ));
    }

    public function delete_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'barangay_profile') {
            return new WP_Error('barangay_profile_not_found', 'Barangay profile not found.', array('status' => 404));
        }

        $deleted = wp_delete_post($post_id, true);
        if (!$deleted) {
            return new WP_Error('barangay_profile_delete_failed', 'Failed to delete barangay profile.', array('status' => 500));
        }

        return rest_ensure_response(array(
            'message' => 'Barangay profile deleted successfully.',
            'deleted_id' => $post_id,
        ));
    }

    private function prepare_barangay_profile_data(WP_REST_Request $request, $is_update = false, $post_id = 0) {
        $existing_post = $post_id ? get_post($post_id) : null;
        $existing_item = $post_id ? Barangay_Manager::format_barangay_profile_item($post_id) : null;

        $name = $this->get_request_value($request, array('name', 'title'), $existing_post ? $existing_post->post_title : '');
        $profile = $this->get_request_value($request, array('barangay_profile', 'profile'), $existing_post ? $existing_post->post_content : '');
        $origin = $this->get_request_value($request, array('origin_of_name'), $existing_item ? $existing_item['origin_of_name'] : '');
        $demographic_profile = $this->get_request_value($request, array('demographic_profile'), $existing_item ? $existing_item['demographic_profile'] : '');
        $status = $request->has_param('status') ? sanitize_key($request->get_param('status')) : ($is_update ? get_post_status($post_id) : 'publish');

        if ($name === '') {
            return new WP_Error('barangay_profile_invalid_name', 'Name of Barangay is required.', array('status' => 400));
        }

        if ($profile === '') {
            return new WP_Error('barangay_profile_invalid_profile', 'Barangay Profile is required.', array('status' => 400));
        }

        if (!in_array($status, array('publish', 'draft', 'private'), true)) {
            $status = 'publish';
        }

        $image_id = $this->get_request_value($request, array('image_id', 'featured_image_id'), $existing_item ? $existing_item['image_id'] : 0);

        $post_data = array(
            'post_type' => 'barangay_profile',
            'post_title' => sanitize_text_field($name),
            'post_content' => wp_kses_post($profile),
            'post_status' => $status,
            'meta_input' => array(
                'barangay_origin_of_name' => wp_kses_post($origin),
                'barangay_demographic_profile' => wp_kses_post($demographic_profile),
            ),
            'image_id' => absint($image_id),
        );

        if ($is_update) {
            $post_data['ID'] = $post_id;
        }

        return $post_data;
    }

    private function get_request_value(WP_REST_Request $request, $keys, $default = '') {
        foreach ($keys as $key) {
            if ($request->has_param($key)) {
                return $request->get_param($key);
            }
        }

        return $default;
    }
}