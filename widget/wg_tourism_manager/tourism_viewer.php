<?php
/**
 * Tourism Viewer - Displays tourism items with filtering and search
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tourism_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_tourism', array($this, 'handle_tourism_search'));
        add_action('wp_ajax_filter_tourism', array($this, 'handle_tourism_filter'));
    }

    /**
     * Display tourism viewer with list and filters
     */
    public function display_tourism_viewer($args = array()) {
        wp_enqueue_style('tourism-viewer-style', plugin_dir_url(__FILE__) . 'css/tourism-viewer-style.css');
        wp_enqueue_script('tourism-viewer-script', plugin_dir_url(__FILE__) . 'js/tourism-viewer.js', array('jquery'));
        
        wp_localize_script('tourism-viewer-script', 'tourismViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tourism_upload_nonce'),
        ));

        $defaults = array(
            'posts_per_page' => 12,
            'featured_only' => false,
        );
        $args = wp_parse_args($args, $defaults);

        ?>
        <div class="tourism-viewer-container">
            <div class="tourism-viewer-header">
                <h2>Tourism Destinations</h2>
                <div class="tourism-controls">
                    <input type="text" id="tourism-search" class="tourism-search-input" placeholder="Search destinations...">
                    <select id="tourism-type-filter" class="tourism-filter-select">
                        <option value="">All Types</option>
                        <option value="beach">Beach</option>
                        <option value="mountain">Mountain</option>
                        <option value="cultural">Cultural</option>
                        <option value="historic">Historic</option>
                        <option value="natural">Natural</option>
                        <option value="adventure">Adventure</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div id="tourism-list" class="tourism-list">
                <?php $this->render_tourism_list($args); ?>
            </div>

            <div id="pagination" class="tourism-pagination"></div>
        </div>
        <?php
    }

    /**
     * Render tourism items
     */
    private function render_tourism_list($args = array()) {
        $query_args = array(
            'post_type' => 'tourism_item',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        if ($args['featured_only']) {
            $query_args['meta_query'] = array(
                array(
                    'key' => 'tourism_featured',
                    'value' => 1,
                    'compare' => '='
                )
            );
        }

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_tourism_item(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="tourism-empty"><p>No tourism destinations found.</p></div>';
        }
    }

    /**
     * Render individual tourism item
     */
    private function render_tourism_item($post_id) {
        $title = get_the_title($post_id);
        $description = get_the_content(null, false, $post_id);
        $type = get_post_meta($post_id, 'tourism_type', true);
        $location = get_post_meta($post_id, 'tourism_location', true);
        $rating = get_post_meta($post_id, 'tourism_rating', true);
        $featured = get_post_meta($post_id, 'tourism_featured', true);
        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';

        $featured_badge = $featured ? '<span class="featured-badge">Featured</span>' : '';

        ?>
        <article class="tourism-item">
            <?php if ($image_url): ?>
                <div class="tourism-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endif; ?>

            <div class="tourism-content">
                <div class="tourism-header">
                    <h3 class="tourism-title"><?php echo esc_html($title); ?></h3>
                    <?php echo $featured_badge; ?>
                </div>

                <div class="tourism-meta">
                    <?php if ($type): ?>
                        <span class="tourism-type type-<?php echo esc_attr($type); ?>">
                            <?php echo esc_html(ucfirst($type)); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($location): ?>
                        <span class="tourism-location">
                            📍 <?php echo esc_html($location); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($rating): ?>
                        <span class="tourism-rating">
                            ⭐ <?php echo esc_html($rating); ?>/5
                        </span>
                    <?php endif; ?>
                </div>

                <div class="tourism-text">
                    <?php echo wp_kses_post(wp_trim_words($description, 40)); ?>
                </div>

                <div class="tourism-actions">
                    <button class="btn btn-read-more" data-post-id="<?php echo esc_attr($post_id); ?>">
                        Learn More
                    </button>
                    <?php if (current_user_can('manage_options')): ?>
                        <button class="btn btn-edit" data-post-id="<?php echo esc_attr($post_id); ?>">
                            Edit
                        </button>
                        <button class="btn btn-delete" data-post-id="<?php echo esc_attr($post_id); ?>">
                            Delete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php
    }

    /**
     * Handle tourism search AJAX request
     */
    public function handle_tourism_search() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        $query_args = array(
            'post_type' => 'tourism_item',
            'posts_per_page' => 12,
            's' => $search_term,
            'post_status' => 'publish'
        );

        $query = new WP_Query($query_args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'type' => get_post_meta($post_id, 'tourism_type', true),
                    'location' => get_post_meta($post_id, 'tourism_location', true),
                    'rating' => get_post_meta($post_id, 'tourism_rating', true),
                    'image' => wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'medium'),
                    'excerpt' => wp_trim_words(get_the_content(null, false, $post_id), 40)
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }

    /**
     * Handle tourism filter AJAX request
     */
    public function handle_tourism_filter() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        $type = sanitize_text_field($_POST['type'] ?? '');

        $query_args = array(
            'post_type' => 'tourism_item',
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        if ($type) {
            $query_args['meta_query'] = array(
                array(
                    'key' => 'tourism_type',
                    'value' => $type,
                    'compare' => '='
                )
            );
        }

        $query = new WP_Query($query_args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'type' => get_post_meta($post_id, 'tourism_type', true),
                    'location' => get_post_meta($post_id, 'tourism_location', true),
                    'rating' => get_post_meta($post_id, 'tourism_rating', true),
                    'image' => wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'medium'),
                    'excerpt' => wp_trim_words(get_the_content(null, false, $post_id), 40)
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }

    /**
     * Display single tourism item
     */
    public function display_single_tourism($post_id) {
        wp_enqueue_style('tourism-single-style', plugin_dir_url(__FILE__) . 'css/tourism-single-style.css');

        $title = get_the_title($post_id);
        $description = get_the_content(null, false, $post_id);
        $type = get_post_meta($post_id, 'tourism_type', true);
        $location = get_post_meta($post_id, 'tourism_location', true);
        $rating = get_post_meta($post_id, 'tourism_rating', true);
        $featured = get_post_meta($post_id, 'tourism_featured', true);
        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';

        ?>
        <article class="tourism-single">
            <div class="tourism-single-header">
                <h1><?php echo esc_html($title); ?></h1>
                <div class="tourism-single-meta">
                    <?php if ($type): ?>
                        <span class="tourism-type"><?php echo esc_html(ucfirst($type)); ?></span>
                    <?php endif; ?>
                    <?php if ($location): ?>
                        <span class="tourism-location">📍 <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    <?php if ($rating): ?>
                        <span class="tourism-rating">⭐ <?php echo esc_html($rating); ?>/5</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($image_url): ?>
                <div class="tourism-single-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endif; ?>

            <div class="tourism-single-content">
                <?php echo wp_kses_post($description); ?>
            </div>
        </article>
        <?php
    }
}

// Initialize viewer
new Tourism_Viewer();
