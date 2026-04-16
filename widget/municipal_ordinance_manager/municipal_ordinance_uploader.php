<?php
/**
 * Municipal Ordinance Uploader - Handles ordinance creation and PDF selection
 */

if (!defined('ABSPATH')) {
    exit;
}

class Municipal_Ordinance_Uploader {

    public function __construct() {
        add_action('wp_ajax_upload_municipal_ordinance', array($this, 'handle_ordinance_upload'));
        add_action('wp_ajax_delete_municipal_ordinance', array($this, 'handle_ordinance_delete'));
    }

    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('municipal-ordinance-upload-style', plugin_dir_url(__FILE__) . 'css/municipal-ordinance-upload-style.css');
        wp_enqueue_script('municipal-ordinance-upload-script', plugin_dir_url(__FILE__) . 'js/municipal-ordinance-upload.js', array('jquery'));

        wp_localize_script('municipal-ordinance-upload-script', 'municipalOrdinanceUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('municipal_ordinance_upload_nonce'),
        ));
        ?>
        <div class="municipal-ordinance-uploader-container">
            <h2>Upload Municipal Ordinance</h2>
            <form id="municipal-ordinance-upload-form" class="municipal-ordinance-form">
                <div class="form-group">
                    <label for="municipal_ordinance_title">Title *</label>
                    <input type="text" id="municipal_ordinance_title" name="municipal_ordinance_title" placeholder="Enter ordinance title" required>
                </div>

                <div class="form-group">
                    <label for="municipal_ordinance_category">Category *</label>
                    <input type="text" id="municipal_ordinance_category" name="municipal_ordinance_category" placeholder="Enter ordinance category" required>
                </div>

                <div class="form-group">
                    <label for="municipal_ordinance_pdf">PDF File *</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_municipal_ordinance_pdf">Upload PDF</button>
                        <input type="hidden" id="municipal_ordinance_pdf_id" name="municipal_ordinance_pdf_id" value="">
                        <div id="municipal-ordinance-file-preview" class="file-preview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload Municipal Ordinance</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="municipal-ordinance-upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    public function handle_ordinance_upload() {
        check_ajax_referer('municipal_ordinance_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $title = sanitize_text_field($_POST['municipal_ordinance_title'] ?? '');
        $category = sanitize_text_field($_POST['municipal_ordinance_category'] ?? '');
        $pdf_id = absint($_POST['municipal_ordinance_pdf_id'] ?? 0);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        if (empty($category)) {
            wp_send_json_error('Category is required');
        }

        if (empty($pdf_id)) {
            wp_send_json_error('PDF file is required');
        }

        if (!$this->is_valid_pdf_attachment($pdf_id)) {
            wp_send_json_error('The selected file must be a PDF');
        }

        $category_id = $this->get_or_create_category_id($category);
        if (is_wp_error($category_id)) {
            wp_send_json_error($category_id->get_error_message());
        }

        $post_id = wp_insert_post(array(
            'post_type' => Municipal_Ordinance_Manager::POST_TYPE,
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                'municipal_ordinance_pdf_id' => $pdf_id,
                'municipal_ordinance_date' => current_time('mysql'),
            ),
        ));

        if ($post_id) {
            wp_set_object_terms($post_id, array(absint($category_id)), Municipal_Ordinance_Manager::CATEGORY_TAXONOMY, false);

            wp_send_json_success(array(
                'message' => 'Municipal ordinance uploaded successfully',
                'post_id' => $post_id,
            ));
        }

        wp_send_json_error('Failed to upload municipal ordinance');
    }

    public function handle_ordinance_delete() {
        check_ajax_referer('municipal_ordinance_viewer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('Municipal ordinance deleted successfully');
        }

        wp_send_json_error('Failed to delete municipal ordinance');
    }

    private function get_or_create_category_id($category) {
        $term = term_exists($category, Municipal_Ordinance_Manager::CATEGORY_TAXONOMY);

        if (!$term) {
            $term = wp_insert_term($category, Municipal_Ordinance_Manager::CATEGORY_TAXONOMY);
        }

        if (is_wp_error($term)) {
            return $term;
        }

        return absint(is_array($term) ? $term['term_id'] : $term);
    }

    private function is_valid_pdf_attachment($attachment_id) {
        $attachment = get_post($attachment_id);

        if (!$attachment || $attachment->post_type !== 'attachment') {
            return false;
        }

        return get_post_mime_type($attachment_id) === 'application/pdf';
    }
}
