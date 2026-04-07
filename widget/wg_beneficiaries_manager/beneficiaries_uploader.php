<?php
/**
 * Beneficiaries Uploader - Handles beneficiary item creation and updates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Beneficiaries_Uploader {
    
    public function __construct() {
        add_action('wp_ajax_upload_beneficiary', array($this, 'handle_beneficiary_upload'));
        add_action('wp_ajax_update_beneficiary', array($this, 'handle_beneficiary_update'));
        add_action('wp_ajax_delete_beneficiary', array($this, 'handle_beneficiary_delete'));
        add_action('wp_ajax_get_beneficiary_detail', array($this, 'handle_get_beneficiary_detail'));
    }

    /**
     * Display beneficiary upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('beneficiaries-upload-style', plugin_dir_url(__FILE__) . 'css/beneficiaries-upload-style.css');
        wp_enqueue_script('beneficiaries-upload-script', plugin_dir_url(__FILE__) . 'js/beneficiaries-upload.js', array('jquery'));
        
        wp_localize_script('beneficiaries-upload-script', 'beneficiariesUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('beneficiaries_nonce'),
            'uploadedMedia' => array()
        ));
        
        ?>
        <div class="beneficiaries-uploader-container">
            <h2>Add Beneficiary</h2>
            <form id="beneficiaries-upload-form" class="beneficiaries-form">
                <?php wp_nonce_field('beneficiaries_nonce', 'nonce'); ?>
                
                <div class="form-group">
                    <label for="beneficiary_name">Name *</label>
                    <input type="text" id="beneficiary_name" name="beneficiary_name" placeholder="Enter beneficiary name" required>
                </div>

                <div class="form-group">
                    <label for="beneficiary_description">Description *</label>
                    <textarea id="beneficiary_description" name="beneficiary_description" placeholder="Enter beneficiary details" rows="6" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="beneficiary_barangay">Barangay *</label>
                        <input type="text" id="beneficiary_barangay" name="beneficiary_barangay" placeholder="Enter barangay" required>
                    </div>

                    <div class="form-group">
                        <label for="beneficiary_type">Beneficiary Type *</label>
                        <select id="beneficiary_type" name="beneficiary_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="Individual">Individual</option>
                            <option value="Family">Family</option>
                            <option value="Organization">Organization</option>
                            <option value="Community">Community</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="beneficiary_contact">Contact Number</label>
                        <input type="tel" id="beneficiary_contact" name="beneficiary_contact" placeholder="Enter contact number">
                    </div>

                    <div class="form-group">
                        <label for="beneficiary_program">Program/Assistance *</label>
                        <input type="text" id="beneficiary_program" name="beneficiary_program" placeholder="e.g., Food Aid, Infrastructure" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="beneficiary_date">Date Registered *</label>
                        <input type="date" id="beneficiary_date" name="beneficiary_date" required>
                    </div>

                    <div class="form-group">
                        <label for="beneficiary_status">Status *</label>
                        <select id="beneficiary_status" name="beneficiary_status" required>
                            <option value="">-- Select Status --</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="beneficiary_image">Photo</label>
                    <div class="image-upload-area">
                        <input type="hidden" id="beneficiary_image_id" name="beneficiary_image_id" value="">
                        <button type="button" class="btn btn-secondary" id="upload-image-btn">Upload Photo</button>
                        <div id="image-preview"></div>
                    </div>
                </div>

                <div id="upload-status" class="status-message"></div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Beneficiary</button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle beneficiary upload
     */
    public function handle_beneficiary_upload() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $title = sanitize_text_field($_POST['beneficiary_name'] ?? '');
        $description = wp_kses_post($_POST['beneficiary_description'] ?? '');

        if (empty($title) || empty($description)) {
            wp_send_json_error('Name and description are required');
        }

        $post_id = wp_insert_post(array(
            'post_type' => 'beneficiary_item',
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish'
        ));

        if ($post_id) {
            update_post_meta($post_id, 'beneficiary_barangay', sanitize_text_field($_POST['beneficiary_barangay'] ?? ''));
            update_post_meta($post_id, 'beneficiary_type', sanitize_text_field($_POST['beneficiary_type'] ?? ''));
            update_post_meta($post_id, 'beneficiary_contact', sanitize_text_field($_POST['beneficiary_contact'] ?? ''));
            update_post_meta($post_id, 'beneficiary_program', sanitize_text_field($_POST['beneficiary_program'] ?? ''));
            update_post_meta($post_id, 'beneficiary_date', sanitize_text_field($_POST['beneficiary_date'] ?? ''));
            update_post_meta($post_id, 'beneficiary_status', sanitize_text_field($_POST['beneficiary_status'] ?? ''));
            
            if (!empty($_POST['beneficiary_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['beneficiary_image_id']));
            }

            wp_send_json_success(array(
                'message' => 'Beneficiary added successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to add beneficiary');
        }
    }

    /**
     * Handle beneficiary update
     */
    public function handle_beneficiary_update() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $title = sanitize_text_field($_POST['beneficiary_name'] ?? '');
        $description = wp_kses_post($_POST['beneficiary_description'] ?? '');

        if (empty($title) || empty($description)) {
            wp_send_json_error('Name and description are required');
        }

        $data = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $description,
            'post_status' => 'publish',
        );

        $updated = wp_update_post($data);

        if ($updated) {
            update_post_meta($post_id, 'beneficiary_barangay', sanitize_text_field($_POST['beneficiary_barangay'] ?? ''));
            update_post_meta($post_id, 'beneficiary_type', sanitize_text_field($_POST['beneficiary_type'] ?? ''));
            update_post_meta($post_id, 'beneficiary_contact', sanitize_text_field($_POST['beneficiary_contact'] ?? ''));
            update_post_meta($post_id, 'beneficiary_program', sanitize_text_field($_POST['beneficiary_program'] ?? ''));
            update_post_meta($post_id, 'beneficiary_date', sanitize_text_field($_POST['beneficiary_date'] ?? ''));
            update_post_meta($post_id, 'beneficiary_status', sanitize_text_field($_POST['beneficiary_status'] ?? ''));
            
            if (!empty($_POST['beneficiary_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['beneficiary_image_id']));
            }

            wp_send_json_success(array(
                'message' => 'Beneficiary updated successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to update beneficiary');
        }
    }

    /**
     * Handle beneficiary deletion
     */
    public function handle_beneficiary_delete() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('Beneficiary deleted successfully');
        } else {
            wp_send_json_error('Failed to delete beneficiary');
        }
    }

    /**
     * Get beneficiary detail via AJAX
     */
    public function handle_get_beneficiary_detail() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error('Beneficiary not found');
        }

        $image_id = get_post_thumbnail_id($post_id);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';

        $data = array(
            'ID' => $post_id,
            'name' => $post->post_title,
            'description' => $post->post_content,
            'barangay' => get_post_meta($post_id, 'beneficiary_barangay', true),
            'type' => get_post_meta($post_id, 'beneficiary_type', true),
            'contact' => get_post_meta($post_id, 'beneficiary_contact', true),
            'program' => get_post_meta($post_id, 'beneficiary_program', true),
            'date' => get_post_meta($post_id, 'beneficiary_date', true),
            'status' => get_post_meta($post_id, 'beneficiary_status', true),
            'image' => $image_url,
            'image_id' => $image_id
        );

        wp_send_json_success($data);
    }
}

// Initialize uploader
new Beneficiaries_Uploader();
