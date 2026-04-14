<?php
/**
 * Barangay Viewer - Displays barangay profiles with filtering
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

class Barangay_Viewer {
    public function __construct() {
        add_action('wp_ajax_search_barangay', array($this, 'handle_search'));
        add_action('wp_ajax_filter_barangay', array($this, 'handle_filter'));
    }

    public function display_barangay_viewer($args = array()) {
        wp_enqueue_style('barangay-viewer-style', plugin_dir_url(__DIR__) . 'css/barangay-viewer-style.css');
        wp_enqueue_script('barangay-viewer-script', plugin_dir_url(__DIR__) . 'js/barangay-viewer.js', array('jquery'));
        wp_localize_script('barangay-viewer-script', 'barangayViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('barangay_upload_nonce'),
        ));

        $defaults = array('posts_per_page' => 12, 'featured_only' => false);
        $args = wp_parse_args($args, $defaults);

        ?>
        <div class="barangay-viewer-container">
            <div class="barangay-header">
                <h2>Barangay Profiles</h2>
                <div class="barangay-controls">
                    <input type="text" id="barangay-search" placeholder="Search barangays..." class="search-input">
                    <select id="barangay-filter" class="filter-select">
                        <option value="">All</option>
                        <option value="featured">Featured Only</option>
                    </select>
                </div>
            </div>
            <div id="barangay-list" class="barangay-grid">
                <?php $this->render_list($args); ?>
            </div>
        </div>
        <?php
    }

    private function render_list($args) {
        $meta_query = array();
        if ($args['featured_only']) {
            $meta_query[] = array('key' => 'barangay_featured', 'value' => 1, 'compare' => '=');
        }

        $query_args = array(
            'post_type' => 'barangay_profile',
            'posts_per_page' => $args['posts_per_page'],
            'post_status' => 'publish',
            'meta_query' => $meta_query,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_item(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<p>No barangay profiles found.</p>';
        }
    }

    private function render_item($post_id) {
        $post = get_post($post_id);
        $demographics = get_post_meta($post_id, 'barangay_demographics', true);
        $patron = get_post_meta($post_id, 'barangay_patron_saint', true);
        $pop = get_post_meta($post_id, 'barangay_population', true);
        $featured = get_post_meta($post_id, 'barangay_featured', true);
        $image_url = get_the_post_thumbnail_url($post_id, 'medium');

        ?>
        <div class="barangay-card" data-id="<?php echo $post_id; ?>">
            <?php if ($image_url): ?>
                <div class="barangay-image"><img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>"></div>
            <?php endif; ?>
            <div class="barangay-info">
                <h3><?php echo esc_html($post->post_title); ?> <?php echo $featured ? '<span class="featured">★</span>' : ''; ?></h3>
                <p><?php echo wp_trim_words($post->post_content, 20); ?></p>
                <?php if ($demographics): ?><p><strong>Demographics:</strong> <?php echo esc_html(wp_trim_words($demographics, 15)); ?></p><?php endif; ?>
                <?php if ($patron): ?><p><strong>Patron:</strong> <?php echo esc_html($patron); ?></p><?php endif; ?>
                <?php if ($pop): ?><p><strong>Population:</strong> <?php echo number_format($pop); ?></p><?php endif; ?>
                <div class="barangay-actions">
                    <button class="btn-edit" data-id="<?php echo $post_id; ?>">Edit</button>
                    <?php if (current_user_can('manage_options')): ?>
                        <button class="btn-delete" data-id="<?php echo $post_id; ?>">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function handle_search() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        $term = sanitize_text_field($_POST['search_term'] ?? '');
        $query_args = array(
            'post_type' => 'barangay_profile',
            'posts_per_page' => 12,
            's' => $term,
            'post_status' => 'publish',
        );
        $query = new WP_Query($query_args);
        $results = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'ID' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_content(), 20),
                    'image' => get_the_post_thumbnail_url(null, 'medium'),
                );
            }
        }
        wp_reset_postdata();
        wp_send_json_success($results);
    }

    public function handle_filter() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        $featured = sanitize_text_field($_POST['featured'] ?? '') === 'featured';
        $meta_query = $featured ? array(array('key' => 'barangay_featured', 'value' => 1, 'compare' => '=')) : array();
        $query_args = array(
            'post_type' => 'barangay_profile',
            'posts_per_page' => 12,
            'post_status' => 'publish',
            'meta_query' => $meta_query,
        );
        // ... similar to render_list query, collect results as array
        $query = new WP_Query($query_args);
        $results = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = array(
                    'ID' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => wp_trim_words(get_the_content(), 20),
                    'image' => get_the_post_thumbnail_url(null, 'medium'),
                );
            }
        }
        wp_reset_postdata();
        wp_send_json_success($results);
    }
}

new Barangay_Viewer();
?>

