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

        // POST routes for creating news
        register_rest_route(
            self::API_NAMESPACE,
            '/news',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_news'),
                'permission_callback' => array($this, 'check_news_create_permission'),
                'args' => $this->get_create_args(),
            )
        );

        register_rest_route(
            self::API_NAMESPACE,
            '/events',
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array($this, 'create_event'),
                'permission_callback' => array($this, 'check_news_create_permission'),
                'args' => $this->get_create_args(true),
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

    /**
     * Check permission for creating news items
     */
    public function check_news_create_permission(WP_REST_Request $request) {
        // Allow anyone with api_access capability or authenticated users
        // Modify this based on your permission requirements
        if (is_user_logged_in()) {
            return current_user_can('edit_posts');
        }
        
        // Allow API key authentication
        $api_key = $request->get_header('X-API-Key');
        if (!empty($api_key)) {
            $valid_key = get_option('camaligan_api_key');
            return $api_key === $valid_key;
        }
        
        return false;
    }

    /**
     * Arguments for creating news items
     */
    private function get_create_args($event_mode = false) {
        return array(
            'title' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ($param) {
                    return !empty($param) && strlen($param) <= 200;
                },
            ),
            'content' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post',
            ),
            'excerpt' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'category' => array(
                'type' => 'string',
                'enum' => array('announcement', 'event', 'update'),
                'default' => $event_mode ? 'event' : 'announcement',
            ),
            'priority' => array(
                'type' => 'string',
                'enum' => array('normal', 'high', 'urgent'),
                'default' => 'normal',
            ),
            'featured' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'office' => array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_key',
            ),
            'date' => array(
                'type' => 'string',
                'format' => 'date',
            ),
            'image_url' => array(
                'type' => 'string',
                'format' => 'uri',
                'sanitize_callback' => 'esc_url_raw',
            ),
            'status' => array(
                'type' => 'string',
                'enum' => array('publish', 'draft', 'pending'),
                'default' => 'publish',
            ),
        );
    }

    /**
     * Create a news item
     */
    public function create_news(WP_REST_Request $request) {
        return $this->create_item($request, false);
    }

    /**
     * Create an event item
     */
    public function create_event(WP_REST_Request $request) {
        return $this->create_item($request, true);
    }

    /**
     * Generic method to create news/event items
     */
    private function create_item(WP_REST_Request $request, $is_event = false) {
        // Sanitize inputs
        $title = sanitize_text_field($request->get_param('title'));
        $content = wp_kses_post($request->get_param('content'));
        $excerpt = sanitize_text_field($request->get_param('excerpt'));
        $category = sanitize_key($request->get_param('category'));
        $priority = sanitize_key($request->get_param('priority'));
        $featured = rest_sanitize_boolean($request->get_param('featured'));
        $office = sanitize_key($request->get_param('office'));
        $date = sanitize_text_field($request->get_param('date'));
        $image_url = esc_url_raw($request->get_param('image_url'));
        $status = sanitize_key($request->get_param('status'));

        // Validate required fields
        if (empty($title) || empty($content)) {
            return new WP_Error(
                'missing_fields',
                'Title and content are required.',
                array('status' => 400)
            );
        }

        // Default to event category if creating event
        if ($is_event && empty($category)) {
            $category = 'event';
        }

        // Create the post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_type' => 'news_item',
            'post_status' => $status,
        );

        $post_id = wp_insert_post($post_data, true);

        if (is_wp_error($post_id)) {
            return new WP_Error(
                'post_creation_failed',
                'Failed to create news item.',
                array('status' => 500)
            );
        }

        // Set post metadata
        if (!empty($category)) {
            update_post_meta($post_id, 'news_category', $category);
        }
        
        if (!empty($priority)) {
            update_post_meta($post_id, 'news_priority', $priority);
        }

        if ($featured) {
            update_post_meta($post_id, 'news_featured', 1);
        }

        if (!empty($office)) {
            update_post_meta($post_id, 'news_office', $office);
        }

        if (!empty($date)) {
            update_post_meta($post_id, 'news_date', $date);
        }

        if (!empty($image_url)) {
            update_post_meta($post_id, 'news_image_url', $image_url);
        }

        // Return the created item
        $item = News_Manager::format_news_item($post_id, true);
        
        $response = rest_ensure_response($item);
        $response->set_status(201);
        
        return $response;
    }
}
