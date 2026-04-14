<?php
/**
 * Project REST API - CRUD endpoints for project records
 */

if (!defined('ABSPATH')) {
    exit;
}

class Project_REST_API {

    const API_NAMESPACE = 'wp/v2';
    const REST_BASE = '/projects';

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
                    'permission_callback' => array($this, 'can_manage_projects'),
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
                    'permission_callback' => array($this, 'can_manage_projects'),
                    'args' => $this->get_write_args(true),
                ),
                array(
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => array($this, 'delete_item'),
                    'permission_callback' => array($this, 'can_manage_projects'),
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
            'status' => array(
                'sanitize_callback' => array($this, 'sanitize_project_status'),
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
            'content' => array(
                'sanitize_callback' => 'wp_kses_post',
            ),
            'excerpt' => array(
                'sanitize_callback' => 'sanitize_textarea_field',
            ),
            'post_status' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
            'status' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
            'project_status' => array(
                'sanitize_callback' => array($this, 'sanitize_project_status'),
            ),
            'statuses' => array(),
            'contractor' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'project_contractor' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'contract_amount' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'project_contract_amount' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'timeline' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'project_timeline' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'completion_percent' => array(
                'sanitize_callback' => 'absint',
            ),
            'project_completion_percent' => array(
                'sanitize_callback' => 'absint',
            ),
            'target_date' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'project_target_date' => array(
                'sanitize_callback' => 'sanitize_text_field',
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

    public function sanitize_project_status($value) {
        $value = sanitize_key((string) $value);

        if ($value === 'on-going') {
            $value = 'ongoing';
        }

        return $value;
    }

    public function sanitize_order($value) {
        $value = strtoupper((string) $value);
        return in_array($value, array('ASC', 'DESC'), true) ? $value : 'DESC';
    }

    public function sanitize_orderby($value) {
        $allowed = array('date', 'title', 'modified');
        $value = sanitize_key((string) $value);

        return in_array($value, $allowed, true) ? $value : 'date';
    }

    public function can_manage_projects() {
        return current_user_can('manage_options');
    }

    public function get_items(WP_REST_Request $request) {
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));
        $orderby = $this->sanitize_orderby($request->get_param('orderby'));
        $order = $this->sanitize_order($request->get_param('order'));
        $search = (string) $request->get_param('search');
        $status = $this->sanitize_project_status($request->get_param('status'));

        if ($status !== '' && !array_key_exists($status, Project_Manager::get_allowed_statuses())) {
            return new WP_Error('project_invalid_status', 'Status must be awarded, ongoing, or completed.', array('status' => 400));
        }

        $query_args = array(
            'post_type' => 'project',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => $search,
            'orderby' => $orderby,
            'order' => $order,
        );

        if ($status !== '') {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'project_status',
                    'field' => 'slug',
                    'terms' => $status,
                ),
            );
        }

        $query = new WP_Query($query_args);
        $items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = Project_Manager::format_project_item(get_the_ID());
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

        if (!$post || $post->post_type !== 'project' || $post->post_status !== 'publish') {
            return new WP_Error('project_not_found', 'Project not found.', array('status' => 404));
        }

        return rest_ensure_response(Project_Manager::format_project_item($post_id));
    }

    public function create_item(WP_REST_Request $request) {
        $prepared = $this->prepare_project_data($request, false);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $project_status = $prepared['project_status'];
        unset($prepared['project_status']);

        $post_id = wp_insert_post($prepared, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $this->set_project_status_terms($post_id, $project_status);

        return new WP_REST_Response(
            array(
                'message' => 'Project created successfully.',
                'item' => Project_Manager::format_project_item($post_id),
            ),
            201
        );
    }

    public function update_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'project') {
            return new WP_Error('project_not_found', 'Project not found.', array('status' => 404));
        }

        $prepared = $this->prepare_project_data($request, true, $post_id);
        if (is_wp_error($prepared)) {
            return $prepared;
        }

        $project_status = $prepared['project_status'];
        unset($prepared['project_status']);

        $updated = wp_update_post($prepared, true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        if (!empty($project_status)) {
            $this->set_project_status_terms($post_id, $project_status);
        }

        return rest_ensure_response(array(
            'message' => 'Project updated successfully.',
            'item' => Project_Manager::format_project_item($post_id),
        ));
    }

    public function delete_item(WP_REST_Request $request) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'project') {
            return new WP_Error('project_not_found', 'Project not found.', array('status' => 404));
        }

        $deleted = wp_delete_post($post_id, true);
        if (!$deleted) {
            return new WP_Error('project_delete_failed', 'Failed to delete project.', array('status' => 500));
        }

        return rest_ensure_response(array(
            'message' => 'Project deleted successfully.',
            'deleted_id' => $post_id,
        ));
    }

    private function prepare_project_data(WP_REST_Request $request, $is_update = false, $post_id = 0) {
        $existing_post = $post_id ? get_post($post_id) : null;
        $existing_item = $post_id ? Project_Manager::format_project_item($post_id) : null;

        $title = $request->has_param('title') ? sanitize_text_field($request->get_param('title')) : ($existing_post ? $existing_post->post_title : '');
        $content = $request->has_param('content') ? wp_kses_post($request->get_param('content')) : ($existing_post ? $existing_post->post_content : '');
        $excerpt = $request->has_param('excerpt') ? sanitize_textarea_field($request->get_param('excerpt')) : ($existing_post ? $existing_post->post_excerpt : '');
        $post_status = $this->get_request_value($request, array('post_status', 'status'), $is_update ? get_post_status($post_id) : 'publish');
        $post_status = sanitize_key($post_status);
        $project_status = $this->get_project_status_from_request($request, $existing_item, $is_update);

        if ($title === '') {
            return new WP_Error('project_invalid_title', 'Title is required.', array('status' => 400));
        }

        if (!in_array($post_status, array('publish', 'draft', 'private'), true)) {
            $post_status = 'publish';
        }

        if (!empty($project_status) && !array_key_exists($project_status, Project_Manager::get_allowed_statuses())) {
            return new WP_Error('project_invalid_status', 'Project status must be awarded, ongoing, or completed.', array('status' => 400));
        }

        $completion_percent = absint($this->get_request_value($request, array('completion_percent', 'project_completion_percent'), $existing_item ? $existing_item['completion_percent'] : 0));
        $completion_percent = min(100, $completion_percent);

        $post_data = array(
            'post_type' => 'project',
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_status' => $post_status,
            'meta_input' => array(
                'project_contractor' => sanitize_text_field($this->get_request_value($request, array('contractor', 'project_contractor'), $existing_item ? $existing_item['contractor'] : '')),
                'project_contract_amount' => sanitize_text_field($this->get_request_value($request, array('contract_amount', 'project_contract_amount'), $existing_item ? $existing_item['contract_amount'] : '')),
                'project_timeline' => sanitize_text_field($this->get_request_value($request, array('timeline', 'project_timeline'), $existing_item ? $existing_item['timeline'] : '')),
                'project_completion_percent' => $completion_percent,
                'project_target_date' => sanitize_text_field($this->get_request_value($request, array('target_date', 'project_target_date'), $existing_item ? $existing_item['target_date'] : '')),
            ),
            'project_status' => $project_status,
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

    private function get_project_status_from_request(WP_REST_Request $request, $existing_item = null, $is_update = false) {
        if ($request->has_param('project_status')) {
            return $this->sanitize_project_status($request->get_param('project_status'));
        }

        if ($request->has_param('statuses')) {
            $statuses = $request->get_param('statuses');
            if (is_array($statuses)) {
                $first_status = reset($statuses);
                if (is_array($first_status) && isset($first_status['slug'])) {
                    return $this->sanitize_project_status($first_status['slug']);
                }

                return $this->sanitize_project_status($first_status);
            }

            return $this->sanitize_project_status($statuses);
        }

        if ($existing_item && !empty($existing_item['statuses'][0]['slug'])) {
            return $this->sanitize_project_status($existing_item['statuses'][0]['slug']);
        }

        return $is_update ? '' : 'awarded';
    }

    private function set_project_status_terms($post_id, $project_status) {
        if (empty($project_status)) {
            return;
        }

        wp_set_object_terms($post_id, $project_status, 'project_status', false);
    }
}