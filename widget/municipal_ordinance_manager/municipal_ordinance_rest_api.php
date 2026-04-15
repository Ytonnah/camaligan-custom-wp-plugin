<?php
/**
 * Municipal Ordinance REST API - CRUD endpoints for ordinance records
 */

if (!defined('ABSPATH')) {
    exit;
}

class Municipal_Ordinance_REST_API {

    const API_NAMESPACE = 'wp/v2';
    const REST_BASE = '/municipal_ordinance';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route(
            self::API_NAMESPACE,
            self::REST_BASE,
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_items'),
                    'permission_callback' => '__return_true',
                    'args' => $this->get_collection_args(),
                ),
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'create_item'),
                    'permission_callback' => array($this, 'can_manage_ordinances'),
                    'args' => $this->get_write_args(false),
                ),
            )
        );

        register_rest_route(
            self::API_NAMESPACE,
            self::REST_BASE . '/(?P<id>\\d+)',
            array(
                array(
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => array($this, 'get_item'),
                    'permission_callback' => '__return_true',
                    'args' => $this->get_single_item_args(),
                ),
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array($this, 'update_item'),
                    'permission_callback' => array($this, 'can_manage_ordinances'),
                    'args' => $this->get_write_args(true),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'can_manage_ordinances'),
                    'args' => $this->get_single_item_args(),
                ),
            )
        );
    }

    private function get_collection_args() {
        return array(
            'page' => array(
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default' => 10,
                'sanitize_callback' => array($this, 'sanitize_per_page'),
            ),
            'search' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'orderby' => array(
                'default' => 'date',
                'sanitize_callback' => array($this, 'sanitize_orderby'),
            ),
            'order' => array(
                'default' => 'DESC',
                'sanitize_callback' => array($this, 'sanitize_order'),
            ),
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
            'title' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'pdf_id' => array(
                'sanitize_callback' => 'absint',
            ),
            'status' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
        );

        if ($include_id) {
            $args['id'] = $this->get_single_item_args()['id'];
        }

        return $args;
    }

    public function sanitize_per_page($value) {
        $value = absint($value);

        if ($value < 1) {
            $value = 10;
        }

        return min($value, 50);
    }

    public function sanitize_order($value) {
        $value = strtoupper((string) $value);
        return in_array($value, array('ASC', 'DESC'), true) ? $value : 'DESC';
    }

    public function sanitize_orderby($value) {
        $allowed = array('date', 'title');
        $value = sanitize_key($value);

        return in_array($value, $allowed, true) ? $value : 'date';
    }

    public function can_manage_ordinances() {
        return current_user_can('manage_options');
    }

    public function get_items(WP_REST_Request $request) {
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));
        $orderby = $this->sanitize_orderby($request->get_param('orderby'));
        $order = $this->sanitize_order($request->get_param('order'));
        $search = (string) $request->get_param('search');

        $query = new WP_Query(array(
            'post_type' => 'municipal_ordinance',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => $search,
            'orderby' => $orderby,
            'order' => $order,
        ));
        $items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = Municipal_Ordinance_Manager::format_ordinance_item(get_the_ID());
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

        if (!$post || $post->post_type !== 'municipal_ordinance' || $post->post_status !== 'publish') {
            return new WP_Error('municipal_ordinance_not_found', 'Municipal ordinance not found.', array('status' => 404));
        }

        return rest_ensure_response(Municipal_Ordinance_Manager::format_ordinance_item($post_id));
    }

    public function create_item(WP_REST_Request $request) {
        $prepared = $this->prepare_ordinance_data($request, false);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $post_id = wp_insert_post($prepared, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        return new WP_REST_Response(
            array(
                'message' => 'Municipal ordinance created successfully.',
                'item' => Municipal_Ordinance_Manager::format_ordinance_item($post_id),
            ),
            201
        );
    }

    public function update_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'municipal_ordinance') {
            return new WP_Error('municipal_ordinance_not_found', 'Municipal ordinance not found.', array('status' => 404));
        }

        $prepared = $this->prepare_ordinance_data($request, true, $post_id);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $updated = wp_update_post($prepared, true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        return rest_ensure_response(array(
            'message' => 'Municipal ordinance updated successfully.',
            'item' => Municipal_Ordinance_Manager::format_ordinance_item($post_id),
        ));
    }

    public function delete_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'municipal_ordinance') {
            return new WP_Error('municipal_ordinance_not_found', 'Municipal ordinance not found.', array('status' => 404));
        }

        $deleted = wp_delete_post($post_id, true);
        if (!$deleted) {
            return new WP_Error('municipal_ordinance_delete_failed', 'Failed to delete municipal ordinance.', array('status' => 500));
        }

        return rest_ensure_response(array(
            'message' => 'Municipal ordinance deleted successfully.',
            'deleted_id' => $post_id,
        ));
    }

    private function prepare_ordinance_data(WP_REST_Request $request, $is_update = false, $post_id = 0) {
        $existing = $post_id ? Municipal_Ordinance_Manager::format_ordinance_item($post_id) : null;

        $title = $request->has_param('title') ? sanitize_text_field($request->get_param('title')) : ($existing ? $existing['title'] : '');
        $pdf_id = $request->has_param('pdf_id') ? absint($request->get_param('pdf_id')) : ($existing ? (int) $existing['pdf_id'] : 0);
        $status = $request->has_param('status') ? sanitize_key($request->get_param('status')) : ($is_update ? get_post_status($post_id) : 'publish');

        if ($title === '') {
            return new WP_Error('municipal_ordinance_invalid_title', 'Title is required.', array('status' => 400));
        }

        if ($pdf_id <= 0) {
            return new WP_Error('municipal_ordinance_invalid_pdf', 'PDF attachment ID is required.', array('status' => 400));
        }

        if (!$this->is_valid_pdf_attachment($pdf_id)) {
            return new WP_Error('municipal_ordinance_invalid_pdf_attachment', 'The provided PDF attachment is invalid.', array('status' => 400));
        }

        if (!in_array($status, array('publish', 'draft', 'private'), true)) {
            $status = 'publish';
        }

        $post_data = array(
            'post_type' => 'municipal_ordinance',
            'post_title' => $title,
            'post_status' => $status,
            'meta_input' => array(
                'municipal_ordinance_pdf_id' => $pdf_id,
                'municipal_ordinance_date' => current_time('mysql'),
            ),
        );

        if ($is_update) {
            $post_data['ID'] = $post_id;
        }

        return $post_data;
    }

    private function is_valid_pdf_attachment($attachment_id) {
        $attachment = get_post($attachment_id);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }

        return get_post_mime_type($attachment_id) === 'application/pdf';
    }
}
