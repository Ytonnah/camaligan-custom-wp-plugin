<?php
/**
 * News Viewer - Displays news items with filtering and search
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class News_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_news', array($this, 'handle_news_search'));
        add_action('wp_ajax_filter_news', array($this, 'handle_news_filter'));
    }

    /**
     * Display news viewer with list and filters
     */
    public function display_news_viewer($args = array()) {
        wp_enqueue_style('news-viewer-style', plugin_dir_url(__FILE__) . 'css/news-viewer-style.css');
        wp_enqueue_script('news-viewer-script', plugin_dir_url(__FILE__) . 'js/news-viewer.js', array('jquery'));
        
        wp_localize_script('news-viewer-script', 'newsViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('news_viewer_nonce'),
        ));

        $defaults = array(
            'posts_per_page' => 10,
            'featured_only' => false,
        );
        $args = wp_parse_args($args, $defaults);

        ?>
        <div class="news-viewer-container">
            <div class="news-viewer-header">
                <h2>Latest News</h2>
                <div class="news-controls">
                    <input type="text" id="news-search" class="news-search-input" placeholder="Search news...">
                    <select id="news-category-filter" class="news-filter-select">
                        <option value="">All Categories</option>
                        <option value="general">General</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                        <option value="update">Update</option>
                        <option value="alert">Alert</option>
                        <option value="news">News</option>
                    </select>
                </div>
            </div>

            <div id="news-list" class="news-list">
                <?php $this->render_news_list($args); ?>
            </div>

            <div id="pagination" class="news-pagination"></div>
        </div>
        <?php
    }

    /**
     * Render news items
     */
    private function render_news_list($args = array()) {
        $query_args = array(
            'post_type' => 'news_item',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        if ($args['featured_only']) {
            $query_args['meta_query'] = array(
                array(
                    'key' => 'news_featured',
                    'value' => 1,
                    'compare' => '='
                )
            );
        }

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_news_item(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="news-empty"><p>No news items found.</p></div>';
        }
    }

    /**
     * Render individual news item
     */
    private function render_news_item($post_id) {
        $title = get_the_title($post_id);
        $content = get_the_content(null, false, $post_id);
        $date = get_the_date('F j, Y', $post_id);
        $category = get_post_meta($post_id, 'news_category', true);
        $priority = get_post_meta($post_id, 'news_priority', true);
        $featured = get_post_meta($post_id, 'news_featured', true);
        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';

        $priority_class = 'priority-' . $priority;
        $featured_badge = $featured ? '<span class="featured-badge">Featured</span>' : '';

        ?>
        <article class="news-item <?php echo esc_attr($priority_class); ?>">
            <?php if ($image_url): ?>
                <div class="news-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endif; ?>

            <div class="news-content">
                <div class="news-header">
                    <h3 class="news-title"><?php echo esc_html($title); ?></h3>
                    <?php echo $featured_badge; ?>
                </div>

                <div class="news-meta">
                    <?php if ($category): ?>
                        <span class="news-category category-<?php echo esc_attr($category); ?>">
                            <?php echo esc_html(ucfirst($category)); ?>
                        </span>
                    <?php endif; ?>
                    <span class="news-priority priority-<?php echo esc_attr($priority); ?>">
                        <?php echo esc_html(ucfirst($priority)); ?>
                    </span>
                    <span class="news-date"><?php echo esc_html($date); ?></span>
                </div>

                <div class="news-text">
                    <?php echo wp_kses_post(wp_trim_words($content, 30)); ?>
                </div>

                <div class="news-actions">
                    <button class="btn btn-read-more" data-post-id="<?php echo esc_attr($post_id); ?>">
                        Read More
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
     * Handle news search AJAX request
     */
    public function handle_news_search() {
        check_ajax_referer('news_viewer_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        $query_args = array(
            'post_type' => 'news_item',
            'posts_per_page' => 10,
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
                    'category' => get_post_meta($post_id, 'news_category', true),
                    'priority' => get_post_meta($post_id, 'news_priority', true),
                    'date' => get_the_date('F j, Y', $post_id),
                    'image' => wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'medium'),
                    'excerpt' => wp_trim_words(get_the_content(null, false, $post_id), 30)
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }

    /**
     * Handle news filter AJAX request
     */
    public function handle_news_filter() {
        check_ajax_referer('news_viewer_nonce', 'nonce');

        $category = sanitize_text_field($_POST['category'] ?? '');
        $priority = sanitize_text_field($_POST['priority'] ?? '');

        $query_args = array(
            'post_type' => 'news_item',
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $meta_query = array('relation' => 'AND');

        if ($category) {
            $meta_query[] = array(
                'key' => 'news_category',
                'value' => $category,
                'compare' => '='
            );
        }

        if ($priority) {
            $meta_query[] = array(
                'key' => 'news_priority',
                'value' => $priority,
                'compare' => '='
            );
        }

        if (count($meta_query) > 1) {
            $query_args['meta_query'] = $meta_query;
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
                    'category' => get_post_meta($post_id, 'news_category', true),
                    'priority' => get_post_meta($post_id, 'news_priority', true),
                    'date' => get_the_date('F j, Y', $post_id),
                    'image' => wp_get_attachment_image_url(get_post_thumbnail_id($post_id), 'medium'),
                    'excerpt' => wp_trim_words(get_the_content(null, false, $post_id), 30)
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }

    /**
     * Display single news item
     */
    public function display_single_news($post_id) {
        wp_enqueue_style('news-single-style', plugin_dir_url(__FILE__) . 'css/news-single-style.css');

        $title = get_the_title($post_id);
        $content = get_the_content(null, false, $post_id);
        $date = get_the_date('F j, Y l', $post_id);
        $category = get_post_meta($post_id, 'news_category', true);
        $priority = get_post_meta($post_id, 'news_priority', true);
        $featured = get_post_meta($post_id, 'news_featured', true);
        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';

        ?>
        <article class="news-single">
            <div class="news-single-header">
                <h1><?php echo esc_html($title); ?></h1>
                <div class="news-single-meta">
                    <span class="news-date"><?php echo esc_html($date); ?></span>
                    <?php if ($category): ?>
                        <span class="news-category"><?php echo esc_html(ucfirst($category)); ?></span>
                    <?php endif; ?>
                    <?php if ($priority): ?>
                        <span class="news-priority"><?php echo esc_html(ucfirst($priority)); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($image_url): ?>
                <div class="news-single-image">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                </div>
            <?php endif; ?>

            <div class="news-single-content">
                <?php echo wp_kses_post($content); ?>
            </div>
        </article>
        <?php
    }
}

// Initialize viewer
new News_Viewer();
