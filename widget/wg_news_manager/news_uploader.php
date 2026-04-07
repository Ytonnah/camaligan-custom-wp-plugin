<?php
/**
 * News Uploader - Handles news item creation and uploads
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class News_Uploader {
    
    public function __construct() {
        add_action('wp_ajax_upload_news', array($this, 'handle_news_upload'));
        add_action('wp_ajax_update_news', array($this, 'handle_news_update'));
        add_action('wp_ajax_delete_news', array($this, 'handle_news_delete'));
    }

    /**
     * Display news upload form
     */
    public function display_upload_form() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        wp_enqueue_style('news-upload-style', plugin_dir_url(__FILE__) . 'css/news-upload-style.css');
        wp_enqueue_script('news-upload-script', plugin_dir_url(__FILE__) . 'js/news-upload.js', array('jquery'));
        
        wp_localize_script('news-upload-script', 'newsUploadData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('news_upload_nonce'),
            'uploadedMedia' => array()
        ));
        
        ?>
        <div class="news-uploader-container">
            <h2>Upload News</h2>
            <form id="news-upload-form" class="news-form">
                <div class="form-group">
                    <label for="news_title">News Title *</label>
                    <input type="text" id="news_title" name="news_title" placeholder="Enter news title" required>
                </div>

                <div class="form-group">
                    <label for="news_content">News Content *</label>
                    <textarea id="news_content" name="news_content" placeholder="Enter news content" rows="6" required></textarea>
                </div>

                <div class="form-group">
                    <label for="news_category">Category</label>
                    <select id="news_category" name="news_category">
                        <option value="">Select Category</option>
                        <option value="general">General</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                        <option value="update">Update</option>
                        <option value="alert">Alert</option>
                        <option value="news">News</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="news_priority">Priority</label>
                    <select id="news_priority" name="news_priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="news_image">Featured Image</label>
                    <div class="media-upload-wrapper">
                        <button type="button" class="btn btn-media-upload" id="upload_news_image">Upload Image</button>
                        <input type="hidden" id="news_image_id" name="news_image_id" value="">
                        <div id="image-preview" class="image-preview"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="news_date">Date</label>
                    <input type="date" id="news_date" name="news_date">
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="news_featured" name="news_featured" value="1">
                        Mark as Featured
                    </label>
                    <label>
                        <input type="checkbox" id="news_active" name="news_active" value="1" checked>
                        Publish Immediately
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Upload News</button>
                    <button type="reset" class="btn btn-secondary">Clear</button>
                </div>

                <div id="upload-status" class="upload-status" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Handle AJAX news upload
     */
    public function handle_news_upload() {
        check_ajax_referer('news_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        // Validate required fields
        $title = sanitize_text_field($_POST['news_title'] ?? '');
        $content = wp_kses_post($_POST['news_content'] ?? '');

        if (empty($title) || empty($content)) {
            wp_send_json_error('Title and content are required');
        }

        $data = array(
            'post_type' => 'news_item',
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => isset($_POST['news_active']) ? 'publish' : 'draft',
            'meta_input' => array(
                'news_category' => sanitize_text_field($_POST['news_category'] ?? ''),
                'news_priority' => sanitize_text_field($_POST['news_priority'] ?? 'normal'),
                'news_featured' => isset($_POST['news_featured']) ? 1 : 0,
                'news_image_id' => absint($_POST['news_image_id'] ?? 0),
                'news_date' => sanitize_text_field($_POST['news_date'] ?? current_time('mysql')),
            )
        );

        $post_id = wp_insert_post($data);

        if ($post_id) {
            // Set featured image if provided
            if (!empty($_POST['news_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['news_image_id']));
            }
            
            // Assign to 'News' WordPress category if selected
            $category = sanitize_text_field($_POST['news_category'] ?? '');
            if ($category === 'news') {
                $news_category = get_category_by_slug('news');
                if ($news_category) {
                    wp_set_post_terms($post_id, array($news_category->term_id), 'category');
                }
            }
            
            wp_send_json_success(array(
                'message' => 'News uploaded successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to upload news');
        }
    }

    /**
     * Handle AJAX news update
     */
    public function handle_news_update() {
        check_ajax_referer('news_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $title = sanitize_text_field($_POST['news_title'] ?? '');
        $content = wp_kses_post($_POST['news_content'] ?? '');

        if (empty($title) || empty($content)) {
            wp_send_json_error('Title and content are required');
        }

        $data = array(
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => isset($_POST['news_active']) ? 'publish' : 'draft',
        );

        $updated = wp_update_post($data);

        if ($updated) {
            $category = sanitize_text_field($_POST['news_category'] ?? '');
            update_post_meta($post_id, 'news_category', $category);
            update_post_meta($post_id, 'news_priority', sanitize_text_field($_POST['news_priority'] ?? 'normal'));
            update_post_meta($post_id, 'news_featured', isset($_POST['news_featured']) ? 1 : 0);
            
            if (!empty($_POST['news_image_id'])) {
                set_post_thumbnail($post_id, absint($_POST['news_image_id']));
            }
            
            // Assign to 'News' WordPress category if selected
            if ($category === 'news') {
                $news_category = get_category_by_slug('news');
                if ($news_category) {
                    wp_set_post_terms($post_id, array($news_category->term_id), 'category');
                }
            }

            wp_send_json_success(array(
                'message' => 'News updated successfully',
                'post_id' => $post_id
            ));
        } else {
            wp_send_json_error('Failed to update news');
        }
    }

    /**
     * Handle AJAX news deletion
     */
    public function handle_news_delete() {
        check_ajax_referer('news_upload_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
        }

        $post_id = absint($_POST['post_id'] ?? 0);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        $deleted = wp_delete_post($post_id, true);

        if ($deleted) {
            wp_send_json_success('News deleted successfully');
        } else {
            wp_send_json_error('Failed to delete news');
        }
    }
}

// Initialize uploader
new News_Uploader();
