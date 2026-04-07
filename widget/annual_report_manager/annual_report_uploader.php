<?php
/**
 * Annual Report Uploader - Handles report creation and PDF uploads
 */

if (!defined('ABSPATH')) {
    exit;
}

class Annual_Report_Uploader {

    public function __construct() {
        add_action('wp_ajax_upload_annual_report', array($this, 'handle_report_upload'));
        add_action('wp_ajax_delete_annual_report', array($this, 'handle_report_delete'));
    }

    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('annual-report-upload-style', plugin_dir_url(__FILE__) . 'css/annual-report-upload-style.css');
        wp_enqueue_script('annual-report-upload-script', plugin_dir_url(__FILE__) . 'js/annual-report-upload.js', array('jquery'));

        wp_localize_script('annual-report-upload-script', 'annualReportUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('annual_report_upload_nonce'),
        ));
        ?>
        <div class="annual-report-uploader-container">
            <h2>Upload Annual Report</h2>
            <form id="annual-report-upload-form" class="annual-report-form">
                <div class="form-group">
                    <label for="annual_report_title">Title *</label>
                    <input type="text" id="annual_report_title" name="annual_report_title" placeholder="Enter annual report title" required>
                </div>

                <div class="form-group">
                    <label for="annual_report_year">Year *</label>
                    <input type="number" id="annual_report_year" name="annual_report_year" min="1900" max="9999" placeholder="Enter report year" required>
                </div>

                <div class="form-group">
                    <label for="annual_report_pdf">PDF File *</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_annual_report_pdf">Upload PDF</button>
                        <input type="hidden" id="annual_report_pdf_id" name="annual_report_pdf_id" value="">
                        <div id="annual-report-file-preview" class="file-preview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload Annual Report</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="annual-report-upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    public function handle_report_upload() {
        check_ajax_referer('annual_report_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $title = sanitize_text_field($_POST['annual_report_title'] ?? '');
        $year = absint($_POST['annual_report_year'] ?? 0);
        $pdf_id = absint($_POST['annual_report_pdf_id'] ?? 0);

        if (empty($title)) {
            wp_send_json_error('Title is required');
        }

        if (empty($year)) {
            wp_send_json_error('Year is required');
        }

        if (empty($pdf_id)) {
            wp_send_json_error('PDF file is required');
        }

        $post_id = wp_insert_post(array(
            'post_type' => 'annual_report',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                'annual_report_pdf_id' => $pdf_id,
                'annual_report_year' => $year,
                'annual_report_date' => current_time('mysql'),
            ),
        ));

        if ($post_id) {
            wp_send_json_success(array(
                'message' => 'Annual report uploaded successfully',
                'post_id' => $post_id,
            ));
        }

        wp_send_json_error('Failed to upload annual report');
    }

    public function handle_report_delete() {
        check_ajax_referer('annual_report_viewer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('Annual report deleted successfully');
        }

        wp_send_json_error('Failed to delete annual report');
    }
}

new Annual_Report_Uploader();
