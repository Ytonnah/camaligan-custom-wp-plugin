<?php
/**
 * News REST API - Public endpoints for news and event feeds
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class News_REST_API {

    const API_NAMESPACE = 'camaligan/v1';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register custom REST routes.
     */
    public function register_routes() {
        register_rest_route(
            self::API_NAMESPACE,
            '/news',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_news'),
                'permission_callback' => '__return_true',
                'args' => $this->get_collection_args(false),
            )
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/news/(?P<id>\\d+)',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_news_item'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($value) {
                            return absint($value) > 0;
                        },
                    ),
                ),
            )
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/events',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_events'),
                'permission_callback' => '__return_true',
                'args' => $this->get_collection_args(true),
            )
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/events/(?P<id>\\d+)',
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_event_item'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'sanitize_callback' => 'absint',
                        'validate_callback' => function ($value) {
                            return absint($value) > 0;
                        },
                    ),
                ),
            )
        );
    }

    /**
     * REST arguments shared by list routes.
     */
    private function get_collection_args($event_mode) {
        return array(
            'page' => array(
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default' => $event_mode ? 6 : 10,
                'sanitize_callback' => array($this, 'sanitize_per_page'),
            ),
            'search' => array(
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'category' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
            'priority' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
            'featured' => array(
                'sanitize_callback' => array($this, 'sanitize_bool'),
            ),
            'office' => array(
                'sanitize_callback' => 'sanitize_key',
            ),
            'upcoming' => array(
                'default' => $event_mode,
                'sanitize_callback' => array($this, 'sanitize_bool'),
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

    public function sanitize_bool($value) {
        if (is_bool($value)) {
            return $value;
        }

        return rest_sanitize_boolean($value);
    }

    public function get_news(WP_REST_Request $request) {
        return $this->get_collection_response($request, false);
    }

    public function get_events(WP_REST_Request $request) {
        return $this->get_collection_response($request, true);
    }

    public function get_news_item(WP_REST_Request $request) {
        return $this->get_single_item_response($request, false);
    }

    public function get_event_item(WP_REST_Request $request) {
        return $this->get_single_item_response($request, true);
    }

    private function get_collection_response(WP_REST_Request $request, $event_mode) {
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));
        $query = new WP_Query($this->build_query_args($request, $event_mode));
        $items = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $items[] = News_Manager::format_news_item(get_the_ID(), false);
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
            'filters' => array(
                'search' => (string) $request->get_param('search'),
                'category' => (string) $request->get_param('category'),
                'priority' => (string) $request->get_param('priority'),
                'featured' => $this->sanitize_bool($request->get_param('featured')),
                'office' => (string) $request->get_param('office'),
                'upcoming' => $this->sanitize_bool($request->get_param('upcoming')),
                'type' => $event_mode ? 'event' : 'news',
            ),
        ));
    }

    private function get_single_item_response(WP_REST_Request $request, $event_mode) {
        $post_id = absint($request['id']);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'news_item' || $post->post_status !== 'publish') {
            return new WP_Error('news_not_found', 'Item not found.', array('status' => 404));
        }

        $item = News_Manager::format_news_item($post_id, true);
        if (!$item) {
            return new WP_Error('news_not_found', 'Item not found.', array('status' => 404));
        }

        if ($event_mode && $item['type'] !== 'event') {
            return new WP_Error('event_not_found', 'Event not found.', array('status' => 404));
        }

        if (!$event_mode && $item['type'] === 'event') {
            return new WP_Error('news_not_found', 'News item not found.', array('status' => 404));
        }

        return rest_ensure_response($item);
    }

    private function build_query_args(WP_REST_Request $request, $event_mode) {
        $meta_query = array('relation' => 'AND');
        $page = max(1, absint($request->get_param('page')));
        $per_page = $this->sanitize_per_page($request->get_param('per_page'));
        $search = (string) $request->get_param('search');
        $category = (string) $request->get_param('category');
        $priority = (string) $request->get_param('priority');
        $office = (string) $request->get_param('office');
        $featured = $this->sanitize_bool($request->get_param('featured'));
        $upcoming = $this->sanitize_bool($request->get_param('upcoming'));

        if ($event_mode) {
            $meta_query[] = array(
                'key' => 'news_category',
                'value' => 'event',
                'compare' => '=',
            );
        } elseif (!empty($category)) {
            $meta_query[] = array(
                'key' => 'news_category',
                'value' => $category,
                'compare' => '=',
            );
        } else {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'news_category',
                    'value' => 'event',
                    'compare' => '!=',
                ),
                array(
                    'key' => 'news_category',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        if (!empty($priority)) {
            $meta_query[] = array(
                'key' => 'news_priority',
                'value' => $priority,
                'compare' => '=',
            );
        }

        if (!empty($office)) {
            $meta_query[] = array(
                'key' => 'news_office',
                'value' => $office,
                'compare' => '=',
            );
        }

        if ($featured) {
            $meta_query[] = array(
                'key' => 'news_featured',
                'value' => 1,
                'compare' => '=',
            );
        }

        if ($event_mode && $upcoming) {
            $meta_query[] = array(
                'key' => 'news_date',
                'value' => current_time('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE',
            );
        }

        $query_args = array(
            'post_type' => 'news_item',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            's' => $search,
            'meta_query' => $meta_query,
        );

        if ($event_mode) {
            $query_args['meta_key'] = 'news_date';
            $query_args['orderby'] = 'meta_value';
            $query_args['meta_type'] = 'DATE';
            $query_args['order'] = 'ASC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        }

        return $query_args;
    }
}
