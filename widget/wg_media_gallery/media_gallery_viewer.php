<?php
/**
 * Media Gallery Viewer - Displays galleries and images
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Media_Gallery_Viewer {
    
    public function __construct() {
        add_action('wp_ajax_search_galleries', array($this, 'handle_search_galleries'));
        add_action('wp_ajax_delete_gallery', array($this, 'handle_delete_gallery'));
    }

    /**
     * Display gallery viewer
     */
    public function display_gallery_viewer() {
        wp_enqueue_style('media-gallery-viewer-style', plugin_dir_url(__FILE__) . 'css/media-gallery-viewer-style.css');
        wp_enqueue_script('media-gallery-viewer-script', plugin_dir_url(__FILE__) . 'js/media-gallery-viewer.js', array('jquery'));
        
        wp_localize_script('media-gallery-viewer-script', 'mediaGalleryViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('media_gallery_nonce')
        ));
        
        $galleries = $this->get_all_galleries();
        ?>
        
        <div class="media-gallery-viewer-container">
            <h2>View Galleries</h2>
            
            <!-- Search -->
            <div class="search-section">
                <input type="text" id="gallery-search" class="search-input" placeholder="Search galleries...">
            </div>

            <!-- Gallery List -->
            <div id="gallery-list" class="gallery-list">
                <?php if (!empty($galleries)): ?>
                    <?php foreach ($galleries as $gallery): ?>
                        <?php $this->render_gallery_card($gallery); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="gallery-empty">
                        <p>No galleries created yet. Create one in "Upload Media".</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Image Detail Modal -->
        <div id="image-detail-modal" class="modal" style="display:none;">
            <div class="modal-content image-modal-content">
                <span class="modal-close">&times;</span>
                <img id="modal-image" src="" alt="" class="modal-image">
                <div class="modal-info">
                    <h3 id="modal-title"></h3>
                    <p id="modal-caption"></p>
                    <div class="modal-actions">
                        <input type="text" id="caption-input" class="caption-input" placeholder="Edit caption...">
                        <button class="btn btn-primary btn-sm" id="save-caption-btn">Save Caption</button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
    }

    /**
     * Render individual gallery card
     */
    private function render_gallery_card($gallery) {
        $gallery_images = get_post_meta($gallery->ID, 'gallery_images', true);
        if (!is_array($gallery_images)) {
            $gallery_images = array();
        }

        $cover_image = !empty($gallery_images) ? wp_get_attachment_image_url($gallery_images[0], 'medium') : '';
        $image_count = count($gallery_images);
        ?>
        
        <div class="gallery-card" data-gallery-id="<?php echo esc_attr($gallery->ID); ?>">
            <?php if ($cover_image): ?>
                <img src="<?php echo esc_url($cover_image); ?>" alt="<?php echo esc_attr($gallery->post_title); ?>" class="gallery-cover">
            <?php else: ?>
                <div class="gallery-cover placeholder">No images</div>
            <?php endif; ?>
            
            <div class="gallery-info">
                <h3><?php echo esc_html($gallery->post_title); ?></h3>
                <p class="image-count"><?php echo esc_html($image_count) . ' image' . ($image_count !== 1 ? 's' : ''); ?></p>
                <?php if (!empty($gallery->post_content)): ?>
                    <p class="gallery-description"><?php echo esc_html(wp_trim_words($gallery->post_content, 15)); ?></p>
                <?php endif; ?>
            </div>

            <div class="gallery-actions">
                <button class="btn btn-primary btn-sm view-gallery-btn" data-gallery-id="<?php echo esc_attr($gallery->ID); ?>">View</button>
                <button class="btn btn-danger btn-sm delete-gallery-btn" data-gallery-id="<?php echo esc_attr($gallery->ID); ?>">Delete</button>
            </div>
        </div>
        
        <?php
    }

    /**
     * Get all galleries
     */
    private function get_all_galleries() {
        return get_posts(array(
            'post_type' => 'media_gallery',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
    }

    /**
     * Search galleries
     */
    public function handle_search_galleries() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        $args = array(
            'post_type' => 'media_gallery',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        if (!empty($search_term)) {
            $args['s'] = $search_term;
        }

        $galleries = get_posts($args);

        if (empty($galleries)) {
            wp_send_json_success(array());
            return;
        }

        $result = array();
        foreach ($galleries as $gallery) {
            $gallery_images = get_post_meta($gallery->ID, 'gallery_images', true);
            $cover_image = !empty($gallery_images) ? wp_get_attachment_image_url($gallery_images[0], 'medium') : '';

            $result[] = array(
                'id' => $gallery->ID,
                'title' => $gallery->post_title,
                'description' => $gallery->post_content,
                'cover_image' => $cover_image,
                'image_count' => is_array($gallery_images) ? count($gallery_images) : 0
            );
        }

        wp_send_json_success($result);
    }

    /**
     * Delete gallery
     */
    public function handle_delete_gallery() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $gallery_id = absint($_POST['gallery_id'] ?? 0);
        if (!$gallery_id) {
            wp_send_json_error('Invalid gallery ID');
        }

        $deleted = wp_delete_post($gallery_id, true);

        if ($deleted) {
            wp_send_json_success('Gallery deleted successfully');
        } else {
            wp_send_json_error('Failed to delete gallery');
        }
    }
}

// Initialize viewer
new Media_Gallery_Viewer();
