<?php
/**
 * Annual Report REST API - CRUD endpoints for annual report records
 */

if (!defined('ABSPATH')) {
    exit;
}

class Annual_Report_REST_API {

    const API_NAMESPACE = 'wp/v2';
    const REST_BASE = '/annual_report';

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
                    'permission_callback' => array($this, 'can_manage_annual_reports'),
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
                    'permission_callback' => array($this, 'can_manage_annual_reports'),
                    'args' => $this->get_write_args(true),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'can_manage_annual_reports'),
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
            'year' => array(
                'sanitize_callback' => 'absint',
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
            'year' => array(
                'sanitize_callback' => 'absint',
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
        $allowed = array('date', 'title', 'year');
        $value = sanitize_key($value);

        return in_array($value, $allowed, true) ? $value : 'date';
    }

    public function can_manage_annual_reports() {
        return current_user_can('manage_options');
    }

    public function get_items(WP_REST_Request $request) {
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));
        $orderby = $this->sanitize_orderby($request->get_param('orderby'));
        $order = $this->sanitize_order($request->get_param('order'));
        $search = (string) $request->get_param('search');
        $year = absint($request->get_param('year'));

        $query_args = array(
            'post_type' => 'annual_report',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => $search,
            'order' => $order,
        );

        if ($orderby === 'year') {
            $query_args['meta_key'] = 'annual_report_year';
            $query_args['orderby'] = 'meta_value_num';
        } else {
            $query_args['orderby'] = $orderby;
        }

        if ($year > 0) {
            $query_args['meta_query'] = array(
                array(
                    'key' => 'annual_report_year',
                    'value' => $year,
                    'compare' => '=',
                    'type' => 'NUMERIC',
                ),
            );
        }

        $query = new WP_Query($query_args);
        $items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = Annual_Report_Manager::format_annual_report_item(get_the_ID());
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

        if (!$post || $post->post_type !== 'annual_report' || $post->post_status !== 'publish') {
            return new WP_Error('annual_report_not_found', 'Annual report not found.', array('status' => 404));
        }

        return rest_ensure_response(Annual_Report_Manager::format_annual_report_item($post_id));
    }

    public function create_item(WP_REST_Request $request) {
        $prepared = $this->prepare_report_data($request, false);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $post_id = wp_insert_post($prepared, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        return new WP_REST_Response(
            array(
                'message' => 'Annual report created successfully.',
                'item' => Annual_Report_Manager::format_annual_report_item($post_id),
            ),
            201
        );
    }

    public function update_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'annual_report') {
            return new WP_Error('annual_report_not_found', 'Annual report not found.', array('status' => 404));
        }

        $prepared = $this->prepare_report_data($request, true, $post_id);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $updated = wp_update_post($prepared, true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        return rest_ensure_response(array(
            'message' => 'Annual report updated successfully.',
            'item' => Annual_Report_Manager::format_annual_report_item($post_id),
        ));
    }

    public function delete_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'annual_report') {
            return new WP_Error('annual_report_not_found', 'Annual report not found.', array('status' => 404));
        }

        $deleted = wp_delete_post($post_id, true);
        if (!$deleted) {
            return new WP_Error('annual_report_delete_failed', 'Failed to delete annual report.', array('status' => 500));
        }

        return rest_ensure_response(array(
            'message' => 'Annual report deleted successfully.',
            'deleted_id' => $post_id,
        ));
    }

    private function prepare_report_data(WP_REST_Request $request, $is_update = false, $post_id = 0) {
        $existing = $post_id ? Annual_Report_Manager::format_annual_report_item($post_id) : null;

        $title = $request->has_param('title') ? sanitize_text_field($request->get_param('title')) : ($existing ? $existing['title'] : '');
        $year = $request->has_param('year') ? absint($request->get_param('year')) : ($existing ? (int) $existing['year'] : 0);
        $pdf_id = $request->has_param('pdf_id') ? absint($request->get_param('pdf_id')) : ($existing ? (int) $existing['pdf_id'] : 0);
        $status = $request->has_param('status') ? sanitize_key($request->get_param('status')) : ($is_update ? get_post_status($post_id) : 'publish');

        if ($title === '') {
            return new WP_Error('annual_report_invalid_title', 'Title is required.', array('status' => 400));
        }

        if ($year <= 0) {
            return new WP_Error('annual_report_invalid_year', 'Year is required.', array('status' => 400));
        }

        if ($pdf_id <= 0) {
            return new WP_Error('annual_report_invalid_pdf', 'PDF attachment ID is required.', array('status' => 400));
        }

        if (!$this->is_valid_pdf_attachment($pdf_id)) {
            return new WP_Error('annual_report_invalid_pdf_attachment', 'The provided PDF attachment is invalid.', array('status' => 400));
        }

        if (!in_array($status, array('publish', 'draft', 'private'), true)) {
            $status = 'publish';
        }

        $post_data = array(
            'post_type' => 'annual_report',
            'post_title' => $title,
            'post_status' => $status,
            'meta_input' => array(
                'annual_report_pdf_id' => $pdf_id,
                'annual_report_year' => $year,
                'annual_report_date' => current_time('mysql'),
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

        $mime_type = get_post_mime_type($attachment_id);

        return $mime_type === 'application/pdf';
    }
}
