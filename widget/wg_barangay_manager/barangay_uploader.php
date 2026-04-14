<?php
/**
 * Barangay Uploader - Handles barangay profile creation and updates
 */

if (!defined('ABSPATH')) {
    exit;
}

class Barangay_Uploader {
    public function __construct() {
        add_action('wp_ajax_upload_barangay', array($this, 'handle_barangay_upload'));
        add_action('wp_ajax_update_barangay', array($this, 'handle_barangay_update'));
        add_action('wp_ajax_delete_barangay', array($this, 'handle_barangay_delete'));
        add_action('wp_ajax_get_barangay_detail', array($this, 'handle_get_barangay_detail'));
    }

    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('barangay-upload-style', plugin_dir_url(__FILE__) . 'css/barangay-upload-style.css');
        wp_enqueue_script('barangay-upload-script', plugin_dir_url(__FILE__) . 'js/barangay-upload.js', array('jquery'), false, true);

        wp_localize_script('barangay-upload-script', 'barangayUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('barangay_upload_nonce'),
        ));
        ?>
        <div class="barangay-uploader-container">
            <h2>Barangay Profile Manager</h2>
            <form id="barangay-upload-form" class="barangay-form">
                <?php wp_nonce_field('barangay_upload_nonce', 'nonce'); ?>

                <div class="form-group">
                    <label for="barangay_name">Name of Barangay *</label>
                    <input type="text" id="barangay_name" name="barangay_name" placeholder="e.g., Poblacion" required>
                </div>

                <div class="form-group">
                    <label for="barangay_profile">Barangay Profile *</label>
                    <textarea id="barangay_profile" name="barangay_profile" rows="5" required></textarea>
                </div>

                <div class="form-group">
                    <label for="barangay_image">Image of Barangay</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_barangay_image">Upload Image</button>
                        <input type="hidden" id="barangay_image_id" name="barangay_image_id">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="barangay_origin_of_name">Origin of Name</label>
                    <textarea id="barangay_origin_of_name" name="barangay_origin_of_name" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="barangay_demographic_profile">Demographic Profile</label>
                    <textarea id="barangay_demographic_profile" name="barangay_demographic_profile" rows="4"></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="barangay_active" name="barangay_active" value="1" checked>
                        Publish
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Barangay Profile</button>
                    <button type="button" class="btn btn-secondary" id="clear-form">Clear Form</button>
                </div>

                <div id="upload-status"></div>
            </form>
        </div>
        <?php
    }

    public function handle_barangay_upload() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $prepared = $this->prepare_post_data($_POST);
        if (is_wp_error($prepared)) {
            wp_send_json_error($prepared->get_error_message());
        }

        $image_id = $prepared['image_id'];
        unset($prepared['image_id']);

        $post_id = wp_insert_post($prepared, true);
        if (is_wp_error($post_id)) {
            wp_send_json_error('Upload failed');
        }

        if ($image_id > 0) {
            set_post_thumbnail($post_id, $image_id);
        }

        wp_send_json_success(array('message' => 'Barangay profile created.', 'post_id' => $post_id));
    }

    public function handle_barangay_update() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'barangay_profile') {
            wp_send_json_error('Invalid barangay profile ID');
        }

        $prepared = $this->prepare_post_data($_POST, $post_id);
        if (is_wp_error($prepared)) {
            wp_send_json_error($prepared->get_error_message());
        }

        $image_id = $prepared['image_id'];
        unset($prepared['image_id']);

        $updated = wp_update_post($prepared, true);
        if (is_wp_error($updated)) {
            wp_send_json_error('Update failed');
        }

        if ($image_id > 0) {
            set_post_thumbnail($post_id, $image_id);
        } elseif (array_key_exists('barangay_image_id', $_POST)) {
            delete_post_thumbnail($post_id);
        }

        wp_send_json_success(array('message' => 'Barangay profile updated.', 'post_id' => $post_id));
    }

    public function handle_barangay_delete() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid ID');
        }

        wp_delete_post($post_id, true);
        wp_send_json_success('Deleted');
    }

    public function handle_get_barangay_detail() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'barangay_profile') {
            wp_send_json_error('Not found');
        }

        $image_id = get_post_thumbnail_id($post_id);
        wp_send_json_success(array(
            'ID' => $post_id,
            'barangay_name' => $post->post_title,
            'barangay_profile' => $post->post_content,
            'barangay_origin_of_name' => get_post_meta($post_id, 'barangay_origin_of_name', true),
            'barangay_demographic_profile' => get_post_meta($post_id, 'barangay_demographic_profile', true),
            'barangay_image_id' => $image_id,
            'barangay_image_url' => $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '',
        ));
    }

    private function prepare_post_data($data, $post_id = 0) {
        $name = sanitize_text_field($data['barangay_name'] ?? '');
        $profile = wp_kses_post($data['barangay_profile'] ?? '');

        if ($name === '') {
            return new WP_Error('barangay_name_required', 'Name of Barangay is required.');
        }

        if ($profile === '') {
            return new WP_Error('barangay_profile_required', 'Barangay Profile is required.');
        }

        $post_data = array(
            'post_type' => 'barangay_profile',
            'post_title' => $name,
            'post_content' => $profile,
            'post_status' => !empty($data['barangay_active']) ? 'publish' : 'draft',
            'meta_input' => array(
                'barangay_origin_of_name' => wp_kses_post($data['barangay_origin_of_name'] ?? ''),
                'barangay_demographic_profile' => wp_kses_post($data['barangay_demographic_profile'] ?? ''),
            ),
            'image_id' => absint($data['barangay_image_id'] ?? 0),
        );

        if ($post_id > 0) {
            $post_data['ID'] = $post_id;
        }

        return $post_data;
    }
}

new Barangay_Uploader();