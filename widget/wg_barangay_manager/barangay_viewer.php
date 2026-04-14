<?php
/**
 * Barangay Viewer - Displays barangay profiles
 */

if (!defined('ABSPATH')) {
    exit;
}

class Barangay_Viewer {
    public function __construct() {
        add_action('wp_ajax_search_barangay', array($this, 'handle_search'));
        add_action('wp_ajax_nopriv_search_barangay', array($this, 'handle_search'));
    }

    public function display_barangay_viewer($args = array()) {
        wp_enqueue_style('barangay-viewer-style', plugin_dir_url(__FILE__) . 'css/barangay-viewer-style.css');
        wp_enqueue_script('barangay-viewer-script', plugin_dir_url(__FILE__) . 'js/barangay-viewer.js', array('jquery'), false, true);
        wp_localize_script('barangay-viewer-script', 'barangayViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('barangay_upload_nonce'),
        ));

        $args = wp_parse_args($args, array('posts_per_page' => 12));
        ?>
        <div class="barangay-viewer-container">
            <div class="barangay-header">
                <h2>Barangay Profiles</h2>
                <div class="barangay-controls">
                    <input type="text" id="barangay-search" placeholder="Search barangays..." class="search-input">
                </div>
            </div>
            <div id="barangay-list" class="barangay-grid">
                <?php $this->render_list($args); ?>
            </div>
        </div>
        <?php
    }

    private function render_list($args) {
        $query = new WP_Query(array(
            'post_type' => 'barangay_profile',
            'posts_per_page' => max(1, absint($args['posts_per_page'])),
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

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
        $origin = get_post_meta($post_id, 'barangay_origin_of_name', true);
        $demographic_profile = get_post_meta($post_id, 'barangay_demographic_profile', true);
        $image_url = get_the_post_thumbnail_url($post_id, 'medium');
        ?>
        <article class="barangay-card" data-id="<?php echo esc_attr($post_id); ?>">
            <?php if ($image_url): ?>
                <div class="barangay-image"><img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($post->post_title); ?>"></div>
            <?php endif; ?>
            <div class="barangay-info">
                <h3><?php echo esc_html($post->post_title); ?></h3>
                <div class="barangay-profile-text"><?php echo wp_kses_post(wpautop($post->post_content)); ?></div>
                <?php if ($origin): ?><p><strong>Origin of Name:</strong> <?php echo esc_html(wp_trim_words($origin, 30)); ?></p><?php endif; ?>
                <?php if ($demographic_profile): ?><p><strong>Demographic Profile:</strong> <?php echo esc_html(wp_trim_words($demographic_profile, 30)); ?></p><?php endif; ?>
                <?php if (current_user_can('manage_options')): ?>
                    <div class="barangay-actions">
                        <button class="btn-edit" data-id="<?php echo esc_attr($post_id); ?>">Edit</button>
                        <button class="btn-delete" data-id="<?php echo esc_attr($post_id); ?>">Delete</button>
                    </div>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    public function handle_search() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        $term = sanitize_text_field($_POST['search_term'] ?? '');

        $query = new WP_Query(array(
            'post_type' => 'barangay_profile',
            'posts_per_page' => 12,
            's' => $term,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $results = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results[] = Barangay_Manager::format_barangay_profile_item(get_the_ID());
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }
}

new Barangay_Viewer();