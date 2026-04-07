<?php
/**
 * Tourism Uploader - Handles tourism item creation and uploads
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Tourism_Uploader {
    
    public function __construct() {
        add_action('wp_ajax_upload_tourism', array($this, 'handle_tourism_upload'));
        add_action('wp_ajax_update_tourism', array($this, 'handle_tourism_update'));
        add_action('wp_ajax_delete_tourism', array($this, 'handle_tourism_delete'));
        add_action('wp_ajax_get_tourism_detail', array($this, 'handle_get_tourism_detail'));
    }

    /**
     * Display tourism upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('tourism-upload-style', plugin_dir_url(__FILE__) . 'css/tourism-upload-style.css');
        wp_enqueue_script('tourism-upload-script', plugin_dir_url(__FILE__) . 'js/tourism-upload.js', array('jquery'));
        
        wp_localize_script('tourism-upload-script', 'tourismUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('tourism_upload_nonce'),
            'uploadedMedia' => array()
        ));
        
        ?>
        <div class="tourism-uploader-container">
            <h2>Upload Tourism Destination</h2>
            <form id="tourism-upload-form" class="tourism-form">
                <?php wp_nonce_field('tourism_upload_nonce', 'nonce'); ?>
                
                <div class="form-group">
                    <label for="tourism_title">Title *</label>
                    <input type="text" id="tourism_title" name="tourism_title" placeholder="Enter destination title" required>
                </div>

                <div class="form-group">
                    <label for="tourism_description">Description *</label>
                    <textarea id="tourism_description" name="tourism_description" placeholder="Enter destination description" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="tourism_type">Type</label>
                    <select id="tourism_type" name="tourism_type">
                        <option value="">Select Type</option>
                        <option value="beach">Beach</option>
                        <option value="mountain">Mountain</option>
                        <option value="cultural">Cultural</option>
                        <option value="historic">Historic</option>
                        <option value="natural">Natural</option>
                        <option value="adventure">Adventure</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tourism_location">Location</label>
                    <input type="text" id="tourism_location" name="tourism_location" placeholder="Enter location address or coordinates">
                </div>

                <div class="form-group">
                    <label for="tourism_image">Featured Image</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_tourism_image">Upload Image</button>
                        <input type="hidden" id="tourism_image_id" name="tourism_image_id" value="">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="tourism_rating">Rating (1-5)</label>
                    <input type="number" id="tourism_rating" name="tourism_rating" min="1" max="5" placeholder="5">
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="tourism_featured" name="tourism_featured" value="1">
                        Featured Destination
                    </label>
                    <label>
                        <input type="checkbox" id="tourism_active" name="tourism_active" value="1" checked>
                        Publish Immediately
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload Destination</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle AJAX tourism upload
     */
    public function handle_tourism_upload() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        // Validate required fields
        $title = sanitize_text_field($_POST['tourism_title'] ?? '');
        $description = wp_kses_post($_POST['tourism_description'] ?? '');

        if (empty($title) || empty($description)) {
            wp_send_json_error('Title and description are required');
        }

        $data = array(
            'post_type' => 'tourism_item',
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => isset($_POST['tourism_active']) ? 'publish' : 'draft',
            'meta_input' => array(
                'tourism_type' => sanitize_text_field($_POST['tourism_type'] ?? ''),
                'tourism_location' => sanitize_text_field($_POST['tourism_location'] ?? ''),
                'tourism_rating' => absint($_POST['tourism_rating'] ?? 0),
                'tourism_featured' => isset($_POST['tourism_featured']) ? 1 : 0,
                'tourism_image_id' => absint($_POST['tourism_image_id'] ?? 0),
            )
        );

        $post_id = wp_insert_post($data);

        if ($post_id) {
            // Set featured image if provided
            if (!empty($_POST['tourism_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['tourism_image_id']));
            }
            wp_send_json_success(array(
                'message' => 'Tourism destination uploaded successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to upload tourism destination');
        }
    }

    /**
     * Handle AJAX tourism update
     */
    public function handle_tourism_update() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $title = sanitize_text_field($_POST['tourism_title'] ?? '');
        $description = wp_kses_post($_POST['tourism_description'] ?? '');

        if (empty($title) || empty($description)) {
            wp_send_json_error('Title and description are required');
        }

        $data = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => isset($_POST['tourism_active']) ? 'publish' : 'draft',
        );

        $updated = wp_update_post($data);

        if ($updated) {
            update_post_meta($post_id, 'tourism_type', sanitize_text_field($_POST['tourism_type'] ?? ''));
            update_post_meta($post_id, 'tourism_location', sanitize_text_field($_POST['tourism_location'] ?? ''));
            update_post_meta($post_id, 'tourism_rating', absint($_POST['tourism_rating'] ?? 0));
            update_post_meta($post_id, 'tourism_featured', isset($_POST['tourism_featured']) ? 1 : 0);
            
            if (!empty($_POST['tourism_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['tourism_image_id']));
            }

            wp_send_json_success(array(
                'message' => 'Tourism destination updated successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to update tourism destination');
        }
    }

    /**
     * Handle AJAX tourism deletion
     */
    public function handle_tourism_delete() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('Tourism destination deleted successfully');
        } else {
            wp_send_json_error('Failed to delete tourism destination');
        }
    }

    /**
     * Handle AJAX get tourism detail request
     */
    public function handle_get_tourism_detail() {
        check_ajax_referer('tourism_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Tourism destination not found');
        }

        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';

        $data = array(
            'ID' => $post_id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'type' => get_post_meta($post_id, 'tourism_type', true),
            'location' => get_post_meta($post_id, 'tourism_location', true),
            'rating' => get_post_meta($post_id, 'tourism_rating', true),
            'featured' => get_post_meta($post_id, 'tourism_featured', true),
            'image' => $image_url
        );

        wp_send_json_success($data);
    }
}

// Initialize uploader
new Tourism_Uploader();
