<?php
/**
 * Media Gallery Uploader - Handles media uploads and management
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Media_Gallery_Uploader {
    
    public function __construct() {
        add_action('wp_ajax_upload_gallery_image', array($this, 'handle_image_upload'));
        add_action('wp_ajax_delete_gallery_image', array($this, 'handle_image_delete'));
        add_action('wp_ajax_create_gallery', array($this, 'handle_create_gallery'));
        add_action('wp_ajax_get_gallery_images', array($this, 'handle_get_gallery_images'));
        add_action('wp_ajax_update_image_caption', array($this, 'handle_update_caption'));
    }

    /**
     * Display gallery upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('media-gallery-style', plugin_dir_url(__FILE__) . 'css/media-gallery-style.css');
        wp_enqueue_script('media-gallery-script', plugin_dir_url(__FILE__) . 'js/media-gallery.js', array('jquery'));
        
        wp_localize_script('media-gallery-script', 'mediaGalleryData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('media_gallery_nonce')
        ));
        
        // Get available galleries
        $galleries = $this->get_galleries();
        
        ?>
        <div class="media-gallery-uploader-container">
            <h2>Media Gallery Manager</h2>
            
            <!-- Gallery Selection -->
            <div class="gallery-section">
                <h3>Select or Create Gallery</h3>
                <div class="gallery-controls">
                    <select id="gallery-selector" class="gallery-input">
                        <option value="">-- Select Gallery --</option>
                        <?php foreach ($galleries as $gallery): ?>
                            <option value="<?php echo esc_attr($gallery->ID); ?>">
                                <?php echo esc_html($gallery->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-secondary" id="new-gallery-btn">New Gallery</button>
                </div>
                
                <!-- New Gallery Form (Hidden) -->
                <div id="new-gallery-form" class="new-gallery-form" style="display:none;">
                    <input type="text" id="gallery-name" class="gallery-input" placeholder="Gallery Name" />
                    <textarea id="gallery-description" class="gallery-input" placeholder="Gallery Description (optional)" rows="3"></textarea>
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary" id="create-gallery-btn">Create Gallery</button>
                        <button type="button" class="btn btn-secondary" id="cancel-gallery-btn">Cancel</button>
                    </div>
                </div>
            </div>

            <!-- Image Upload -->
            <div class="image-upload-section" id="image-upload-section" style="display:none;">
                <h3>Upload Images to Gallery</h3>
                <form id="media-upload-form" class="media-form">
                    <?php wp_nonce_field('media_gallery_nonce', 'nonce'); ?>
                    
                    <div class="form-group">
                        <label for="gallery-images">Select Images</label>
                        <input type="file" id="gallery-images" name="gallery-images" multiple accept="image/*" />
                        <small>Upload one or more images to your gallery</small>
                    </div>

                    <div id="upload-progress" class="progress-container" style="display:none;">
                        <div class="progress-bar"></div>
                        <span class="progress-text">0%</span>
                    </div>

                    <div id="upload-status" class="status-message"></div>

                    <button type="submit" class="btn btn-primary">Upload Images</button>
                </form>
            </div>

            <!-- Display uploaded images for current gallery -->
            <div class="gallery-images-section" id="gallery-images-section" style="display:none;">
                <h3>Gallery Images</h3>
                <div id="gallery-images-list" class="gallery-images-grid">
                    <!-- Images populated via JavaScript -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get all galleries
     */
    private function get_galleries() {
        return get_posts(array(
            'post_type' => 'media_gallery',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
    }

    /**
     * Handle image upload
     */
    public function handle_image_upload() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        if (empty($_FILES['images'])) {
            wp_send_json_error('No images provided');
        }

        $gallery_id = absint($_POST['gallery_id'] ?? 0);
        if (!$gallery_id) {
            wp_send_json_error('Gallery not selected');
        }

        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $uploaded_ids = array();
        $files = $_FILES['images'];

        // Handle multiple files
        $file_count = count($files['name']);
        for ($i = 0; $i < $file_count; $i++) {
            $_FILES['single_image'] = array(
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            );

            if ($_FILES['single_image']['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            $attachment_id = media_handle_upload('single_image', $gallery_id);

            if (!is_wp_error($attachment_id)) {
                $uploaded_ids[] = $attachment_id;
                
                // Add to gallery
                $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
                if (!is_array($gallery_images)) {
                    $gallery_images = array();
                }
                $gallery_images[] = $attachment_id;
                update_post_meta($gallery_id, 'gallery_images', $gallery_images);
            }
        }

        if (!empty($uploaded_ids)) {
            wp_send_json_success(array(
                'message' => count($uploaded_ids) . ' image(s) uploaded successfully',
                'count' => count($uploaded_ids),
                'ids' => $uploaded_ids
            ));
        } else {
            wp_send_json_error('Failed to upload images');
        }
    }

    /**
     * Handle image deletion from gallery
     */
    public function handle_image_delete() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $image_id = absint($_POST['image_id'] ?? 0);
        $gallery_id = absint($_POST['gallery_id'] ?? 0);

        if (!$image_id || !$gallery_id) {
            wp_send_json_error('Invalid parameters');
        }

        // Remove from gallery
        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        if (is_array($gallery_images)) {
            $gallery_images = array_filter($gallery_images, function($id) use ($image_id) {
                return $id != $image_id;
            });
            update_post_meta($gallery_id, 'gallery_images', array_values($gallery_images));
        }

        // Optionally delete the attachment
        wp_delete_attachment($image_id, true);

        wp_send_json_success('Image removed from gallery');
    }

    /**
     * Handle create new gallery
     */
    public function handle_create_gallery() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $gallery_name = sanitize_text_field($_POST['gallery_name'] ?? '');
        $gallery_desc = wp_kses_post($_POST['gallery_description'] ?? '');

        if (empty($gallery_name)) {
            wp_send_json_error('Gallery name is required');
        }

        $gallery_id = wp_insert_post(array(
            'post_type' => 'media_gallery',
            'post_title' => $gallery_name,
            'post_content' => $gallery_desc,
            'post_status' => 'publish'
        ));

        if ($gallery_id) {
            add_post_meta($gallery_id, 'gallery_images', array());
            wp_send_json_success(array(
                'message' => 'Gallery created successfully',
                'gallery_id' => $gallery_id
            ));
        } else {
            wp_send_json_error('Failed to create gallery');
        }
    }

    /**
     * Get all images in a gallery
     */
    public function handle_get_gallery_images() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $gallery_id = absint($_POST['gallery_id'] ?? 0);
        if (!$gallery_id) {
            wp_send_json_error('Invalid gallery ID');
        }

        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        if (!is_array($gallery_images)) {
            $gallery_images = array();
        }

        $images = array();
        foreach ($gallery_images as $image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'medium');
            $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            $caption = get_post_meta($image_id, '_wp_attachment_image_alt', true);

            $images[] = array(
                'id' => $image_id,
                'url' => $image_url,
                'thumb' => $thumb_url,
                'caption' => $caption,
                'title' => get_the_title($image_id)
            );
        }

        wp_send_json_success($images);
    }

    /**
     * Update image caption/alt text
     */
    public function handle_update_caption() {
        check_ajax_referer('media_gallery_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $image_id = absint($_POST['image_id'] ?? 0);
        $caption = sanitize_text_field($_POST['caption'] ?? '');

        if (!$image_id) {
            wp_send_json_error('Invalid image ID');
        }

        update_post_meta($image_id, '_wp_attachment_image_alt', $caption);
        wp_send_json_success('Caption updated successfully');
    }
}

// Initialize uploader
new Media_Gallery_Uploader();
