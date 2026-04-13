<?php
/**
 * Project REST API - public project listing endpoint
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
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_items'),
                'permission_callback' => '__return_true',
                'args' => $this->get_collection_args(),
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
}
