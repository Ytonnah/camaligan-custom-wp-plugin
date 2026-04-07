<?php
/**
 * Budget Overview Uploader - Handles budget overview creation and PDF uploads
 */

if (!defined('ABSPATH')) {
    exit;
}

class Budget_Overview_Uploader {

    public function __construct() {
        add_action('wp_ajax_upload_budget_overview', array($this, 'handle_budget_upload'));
        add_action('wp_ajax_delete_budget_overview', array($this, 'handle_budget_delete'));
    }

    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('budget-overview-upload-style', plugin_dir_url(__FILE__) . 'css/budget-overview-upload-style.css');
        wp_enqueue_script('budget-overview-upload-script', plugin_dir_url(__FILE__) . 'js/budget-overview-upload.js', array('jquery'));

        wp_localize_script('budget-overview-upload-script', 'budgetOverviewUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('budget_overview_upload_nonce'),
        ));
        ?>
        <div class="budget-overview-uploader-container">
            <h2>Upload Budget Overview</h2>
            <form id="budget-overview-upload-form" class="budget-overview-form">
                <div class="form-group">
                    <label for="budget_overview_year">Year *</label>
                    <input type="number" id="budget_overview_year" name="budget_overview_year" min="1900" max="9999" placeholder="Enter budget year" required>
                </div>

                <div class="form-group">
                    <label for="budget_overview_ordinance_no">Ordinance No. *</label>
                    <input type="text" id="budget_overview_ordinance_no" name="budget_overview_ordinance_no" placeholder="Enter ordinance number" required>
                </div>

                <div class="form-group">
                    <label for="budget_overview_total_budget">Total Budget *</label>
                    <input type="text" id="budget_overview_total_budget" name="budget_overview_total_budget" placeholder="Enter total budget" required>
                </div>

                <div class="form-group">
                    <label for="budget_overview_pdf">PDF File *</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_budget_overview_pdf">Upload PDF</button>
                        <input type="hidden" id="budget_overview_pdf_id" name="budget_overview_pdf_id" value="">
                        <div id="budget-overview-file-preview" class="file-preview"></div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload Budget Overview</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="budget-overview-upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    public function handle_budget_upload() {
        check_ajax_referer('budget_overview_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $year = absint($_POST['budget_overview_year'] ?? 0);
        $ordinance_no = sanitize_text_field($_POST['budget_overview_ordinance_no'] ?? '');
        $total_budget = sanitize_text_field($_POST['budget_overview_total_budget'] ?? '');
        $pdf_id = absint($_POST['budget_overview_pdf_id'] ?? 0);

        if (empty($year)) {
            wp_send_json_error('Year is required');
        }

        if (empty($ordinance_no)) {
            wp_send_json_error('Ordinance number is required');
        }

        if (empty($total_budget)) {
            wp_send_json_error('Total budget is required');
        }

        if (empty($pdf_id)) {
            wp_send_json_error('PDF file is required');
        }

        $title = sprintf('Budget Overview %s - Ordinance %s', $year, $ordinance_no);

        $post_id = wp_insert_post(array(
            'post_type' => 'budget_overview',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                'budget_overview_year' => $year,
                'budget_overview_ordinance_no' => $ordinance_no,
                'budget_overview_total_budget' => $total_budget,
                'budget_overview_pdf_id' => $pdf_id,
                'budget_overview_date' => current_time('mysql'),
            ),
        ));

        if ($post_id) {
            wp_send_json_success(array(
                'message' => 'Budget overview uploaded successfully',
                'post_id' => $post_id,
            ));
        }

        wp_send_json_error('Failed to upload budget overview');
    }

    public function handle_budget_delete() {
        check_ajax_referer('budget_overview_viewer_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('Budget overview deleted successfully');
        }

        wp_send_json_error('Failed to delete budget overview');
    }
}

new Budget_Overview_Uploader();
