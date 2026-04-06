<?php
/**
 * BAC Uploader - Handles BAC item creation and PDF uploads
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BAC_Uploader {
    
    public function __construct() {
        add_action('wp_ajax_upload_bac', array($this, 'handle_bac_upload'));
        add_action('wp_ajax_delete_bac', array($this, 'handle_bac_delete'));
    }

    /**
     * Display BAC upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('bac-upload-style', plugin_dir_url(__FILE__) . 'css/bac-upload-style.css');
        wp_enqueue_script('bac-upload-script', plugin_dir_url(__FILE__) . 'js/bac-upload.js', array('jquery'));
        
        wp_localize_script('bac-upload-script', 'bacUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bac_upload_nonce'),
        ));
        
        ?>
        <div class="bac-uploader-container">
            <h2>Upload BAC Document</h2>
            <form id="bac-upload-form" class="bac-form">
                <div class="form-group">
                    <label for="bac_title">Title *</label>
                    <input type="text" id="bac_title" name="bac_title" placeholder="Enter BAC document title" required>
                </div>

                <div class="form-group">
                    <label for="bac_pdf">PDF File *</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_bac_pdf">Upload PDF</button>
                        <input type="hidden" id="bac_pdf_id" name="bac_pdf_id" value="">
                        <div id="file-preview" class="file-preview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload BAC</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle AJAX BAC upload
     */
    public function handle_bac_upload() {
        check_ajax_referer('bac_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        // Validate required fields
        $title = sanitize_text_field($_POST['bac_title'] ?? '');
        $pdf_id = absint($_POST['bac_pdf_id'] ?? 0);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        if (empty($pdf_id)) {
            wp_send_json_error('PDF file is required');
        }

        $data = array(
            'post_type' => 'bac_item',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                'bac_pdf_id' => $pdf_id,
                'bac_date' => current_time('mysql'),
            )
        );

        $post_id = wp_insert_post($data);

        if ($post_id) {
            wp_send_json_success(array(
                'message' => 'BAC uploaded successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to upload BAC');
        }
    }

    /**
     * Handle AJAX BAC deletion
     */
    public function handle_bac_delete() {
        check_ajax_referer('bac_viewer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('BAC deleted successfully');
        } else {
            wp_send_json_error('Failed to delete BAC');
        }
    }
}

// Initialize uploader
new BAC_Uploader();
