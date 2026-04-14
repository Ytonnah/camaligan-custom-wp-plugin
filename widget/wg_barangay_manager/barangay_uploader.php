<?php
/**
 * Barangay Uploader - Handles barangay profile creation and uploads
 */

// Prevent direct access
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

    /**
     * Display barangay upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        $css_url = plugin_dir_url(__DIR__) . 'css/barangay-upload-style.css';
        $js_url = plugin_dir_url(__DIR__) . 'js/barangay-upload.js';
        wp_enqueue_style('barangay-upload-style', $css_url);
        wp_enqueue_script('barangay-upload-script', $js_url, array('jquery'));
        
        wp_localize_script('barangay-upload-script', 'barangayUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('barangay_upload_nonce'),
        ));
        
        ?>
        <div class="barangay-uploader-container">
            <h2>Upload Barangay Profile</h2>
            <form id="barangay-upload-form" class="barangay-form">
                <?php wp_nonce_field('barangay_upload_nonce', 'nonce'); ?>
                
                <div class="form-group">
                    <label for="barangay_name">Barangay Name *</label>
                    <input type="text" id="barangay_name" name="barangay_name" placeholder="e.g., Poblacion" required>
                </div>

                <div class="form-group">
                    <label for="barangay_description">General Description *</label>
                    <textarea id="barangay_description" name="barangay_description" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay_demographics">Demographics</label>
                        <textarea id="barangay_demographics" name="barangay_demographics" rows="3" placeholder="Population, households, etc."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="barangay_patron_saint">Patron Saint</label>
                        <input type="text" id="barangay_patron_saint" name="barangay_patron_saint" placeholder="St. John Doe">
                    </div>
                </div>

                <div class="form-group">
                    <label for="barangay_topography">Topography</label>
                    <textarea id="barangay_topography" name="barangay_topography" rows="3" placeholder="Terrain description"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="barangay_location">Location/Map Coordinates</label>
                        <input type="text" id="barangay_location" name="barangay_location" placeholder="Lat/Long or address">
                    </div>
                    <div class="form-group">
                        <label for="barangay_population">Population (approx)</label>
                        <input type="number" id="barangay_population" name="barangay_population" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="barangay_image">Featured Image</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_barangay_image">Upload Image</button>
                        <input type="hidden" id="barangay_image_id" name="barangay_image_id">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="barangay_featured" name="barangay_featured" value="1">
                        Featured Barangay
                    </label>
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

    // AJAX handlers (mirroring tourism_uploader)
    public function handle_barangay_upload() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $name = sanitize_text_field($_POST['barangay_name'] ?? '');
        $desc = wp_kses_post($_POST['barangay_description'] ?? '');
        if (empty($name) || empty($desc)) wp_send_json_error('Name and description required');

        $data = array(
            'post_type' => 'barangay_profile',
            'post_title' => $name,
            'post_content' => $desc,
            'post_status' => !empty($_POST['barangay_active']) ? 'publish' : 'draft',
            'meta_input' => array(
                'barangay_demographics' => wp_kses_post($_POST['barangay_demographics'] ?? ''),
                'barangay_patron_saint' => sanitize_text_field($_POST['barangay_patron_saint'] ?? ''),
                'barangay_topography' => wp_kses_post($_POST['barangay_topography'] ?? ''),
                'barangay_location' => sanitize_text_field($_POST['barangay_location'] ?? ''),
                'barangay_population' => absint($_POST['barangay_population'] ?? 0),
                'barangay_featured' => !empty($_POST['barangay_featured']) ? 1 : 0,
            )
        );

        $post_id = wp_insert_post($data);
        if (is_wp_error($post_id)) wp_send_json_error('Upload failed');
        
        if (!empty($_POST['barangay_image_id'])) {
            set_post_thumbnail($post_id, absint($_POST['barangay_image_id']));
        }
        
        wp_send_json_success(array('message' => 'Barangay profile created!', 'post_id' => $post_id));
    }

    public function handle_barangay_update() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) wp_send_json_error('Invalid ID');

        $name = sanitize_text_field($_POST['barangay_name'] ?? '');
        $desc = wp_kses_post($_POST['barangay_description'] ?? '');
        if (empty($name) || empty($desc)) wp_send_json_error('Name and description required');

        $data = array(
            'ID' => $post_id,
            'post_title' => $name,
            'post_content' => $desc,
        );

        $updated = wp_update_post($data);
        if (is_wp_error($updated)) wp_send_json_error('Update failed');
        
        // Update meta (same as upload)
        $meta_fields = array('barangay_demographics', 'barangay_patron_saint', 'barangay_topography', 'barangay_location', 'barangay_population', 'barangay_featured');
        foreach ($meta_fields as $field) {
            $value = $_POST[$field] ?? '';
            if ($field === 'barangay_featured') {
                update_post_meta($post_id, $field, !empty($value) ? 1 : 0);
            } elseif ($field === 'barangay_population') {
                update_post_meta($post_id, $field, absint($value));
            } else {
                update_post_meta($post_id, $field, sanitize_text_field($value) ?: wp_kses_post($value));
            }
        }
        
        if (!empty($_POST['barangay_image_id'])) set_post_thumbnail($post_id, absint($_POST['barangay_image_id']));
        
        wp_send_json_success(array('message' => 'Updated!', 'post_id' => $post_id));
    }

    public function handle_barangay_delete() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) wp_send_json_error('Invalid ID');

        wp_delete_post($post_id, true);
        wp_send_json_success('Deleted');
    }

    public function handle_get_barangay_detail() {
        check_ajax_referer('barangay_upload_nonce', 'nonce');
        if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');

        $post_id = absint($_POST['post_id'] ?? 0);
        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'barangay_profile') wp_send_json_error('Not found');

        $image_id = get_post_thumbnail_id($post_id);
        $data = array(
            'ID' => $post_id,
            'post_title' => $post->post_title,
            'post_content' => $post->post_content,
            'barangay_demographics' => get_post_meta($post_id, 'barangay_demographics', true),
            'barangay_patron_saint' => get_post_meta($post_id, 'barangay_patron_saint', true),
            'barangay_topography' => get_post_meta($post_id, 'barangay_topography', true),
            'barangay_location' => get_post_meta($post_id, 'barangay_location', true),
            'barangay_population' => get_post_meta($post_id, 'barangay_population', true),
            'barangay_featured' => get_post_meta($post_id, 'barangay_featured', true),
            'featured_image_id' => $image_id,
            'featured_image_url' => $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '',
        );
        wp_send_json_success($data);
    }
}

// Init
new Barangay_Uploader();
?>

