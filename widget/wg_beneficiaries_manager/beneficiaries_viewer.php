<?php
/**
 * Beneficiaries Viewer - Displays beneficiary items
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Beneficiaries_Viewer {
    
    public function __construct() {
        add_action('wp_ajax_search_beneficiaries', array($this, 'handle_search_beneficiaries'));
        add_action('wp_ajax_filter_beneficiaries', array($this, 'handle_filter_beneficiaries'));
    }

    /**
     * Display beneficiaries viewer
     */
    public function display_beneficiaries_viewer() {
        wp_enqueue_style('beneficiaries-viewer-style', plugin_dir_url(__FILE__) . 'css/beneficiaries-viewer-style.css');
        wp_enqueue_script('beneficiaries-viewer-script', plugin_dir_url(__FILE__) . 'js/beneficiaries-viewer.js', array('jquery'));
        
        wp_localize_script('beneficiaries-viewer-script', 'beneficiariesViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('beneficiaries_nonce')
        ));
        
        $beneficiaries = $this->get_beneficiaries();
        $types = array_unique(array_map(function($b) {
            return get_post_meta($b->ID, 'beneficiary_type', true);
        }, $beneficiaries));
        ?>
        
        <div class="beneficiaries-viewer-container">
            <h2>View Beneficiaries</h2>
            
            <!-- Search Section -->
            <div class="search-filter-section">
                <div class="search-box">
                    <input type="text" id="beneficiaries-search" class="search-input" placeholder="Search beneficiaries...">
                </div>

                <div class="filter-section">
                    <select id="beneficiaries-type-filter" class="filter-input">
                        <option value="">All Types</option>
                        <?php foreach (array_filter($types) as $type): ?>
                            <option value="<?php echo esc_attr($type); ?>"><?php echo esc_html($type); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="beneficiaries-status-filter" class="filter-input">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <!-- Beneficiaries List -->
            <div id="beneficiaries-list" class="beneficiaries-grid">
                <?php if (!empty($beneficiaries)): ?>
                    <?php foreach ($beneficiaries as $beneficiary): ?>
                        <?php $this->render_beneficiary_item($beneficiary); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="beneficiaries-empty">
                        <p>No beneficiaries found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detail Modal -->
        <div id="beneficiary-detail-modal" class="modal" style="display:none;">
            <div class="modal-content beneficiary-modal-content">
                <span class="modal-close">&times;</span>
                <div class="detail-header">
                    <img id="modal-image" src="" alt="" class="detail-image">
                    <div class="detail-header-info">
                        <h2 id="modal-name"></h2>
                        <div class="detail-badges">
                            <span class="badge badge-type" id="modal-type"></span>
                            <span class="badge badge-status" id="modal-status"></span>
                        </div>
                    </div>
                </div>
                <div class="detail-body">
                    <div class="detail-section">
                        <h3>Information</h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Barangay:</label>
                                <p id="modal-barangay"></p>
                            </div>
                            <div class="detail-item">
                                <label>Type:</label>
                                <p id="modal-detail-type"></p>
                            </div>
                            <div class="detail-item">
                                <label>Program/Assistance:</label>
                                <p id="modal-program"></p>
                            </div>
                            <div class="detail-item">
                                <label>Status:</label>
                                <p id="modal-detail-status"></p>
                            </div>
                            <div class="detail-item">
                                <label>Contact:</label>
                                <p id="modal-contact"></p>
                            </div>
                            <div class="detail-item">
                                <label>Date Registered:</label>
                                <p id="modal-date"></p>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3>Description</h3>
                        <div id="modal-description" class="detail-description"></div>
                    </div>
                </div>
                <div class="detail-actions">
                    <button class="btn btn-primary btn-sm" id="edit-beneficiary-btn">Edit</button>
                    <button class="btn btn-danger btn-sm" id="delete-beneficiary-btn">Delete</button>
                    <button class="btn btn-secondary btn-sm" onclick="closeBeneficiaryModal()">Close</button>
                </div>
            </div>
        </div>
        
        <?php
    }

    /**
     * Render individual beneficiary item
     */
    private function render_beneficiary_item($beneficiary) {
        $image_url = get_the_post_thumbnail_url($beneficiary->ID, 'medium');
        $type = get_post_meta($beneficiary->ID, 'beneficiary_type', true);
        $status = get_post_meta($beneficiary->ID, 'beneficiary_status', true);
        $barangay = get_post_meta($beneficiary->ID, 'beneficiary_barangay', true);
        $program = get_post_meta($beneficiary->ID, 'beneficiary_program', true);
        ?>
        
        <div class="beneficiary-card" data-post-id="<?php echo esc_attr($beneficiary->ID); ?>">
            <?php if ($image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($beneficiary->post_title); ?>" class="beneficiary-image">
            <?php else: ?>
                <div class="beneficiary-image placeholder">
                    <span>No Photo</span>
                </div>
            <?php endif; ?>
            
            <div class="beneficiary-badges">
                <span class="badge badge-type"><?php echo esc_html($type); ?></span>
                <span class="badge badge-status <?php echo esc_attr(strtolower($status)); ?>"><?php echo esc_html($status); ?></span>
            </div>

            <div class="beneficiary-info">
                <h3><?php echo esc_html($beneficiary->post_title); ?></h3>
                <p class="beneficiary-barangay">📍 <?php echo esc_html($barangay); ?></p>
                <p class="beneficiary-program"><?php echo esc_html($program); ?></p>
            </div>

            <div class="beneficiary-actions">
                <button class="btn btn-primary btn-sm view-beneficiary-btn" data-post-id="<?php echo esc_attr($beneficiary->ID); ?>">View</button>
                <button class="btn btn-secondary btn-sm edit-beneficiary-btn" data-post-id="<?php echo esc_attr($beneficiary->ID); ?>">Edit</button>
                <button class="btn btn-danger btn-sm delete-beneficiary-btn" data-post-id="<?php echo esc_attr($beneficiary->ID); ?>">Delete</button>
            </div>
        </div>
        
        <?php
    }

    /**
     * Get all beneficiaries
     */
    private function get_beneficiaries() {
        return get_posts(array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
    }

    /**
     * Search beneficiaries
     */
    public function handle_search_beneficiaries() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        $query_args = array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            's' => $search_term,
            'post_status' => 'publish'
        );

        $posts = get_posts($query_args);
        
        if (empty($posts)) {
            wp_send_json_success(array());
            return;
        }

        $result = array_map(function($post) {
            return $this->format_beneficiary_response($post);
        }, $posts);

        wp_send_json_success($result);
    }

    /**
     * Filter beneficiaries
     */
    public function handle_filter_beneficiaries() {
        check_ajax_referer('beneficiaries_nonce', 'nonce');

        $type = sanitize_text_field($_POST['type'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        $query_args = array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        if (!empty($type)) {
            $query_args['meta_query'][] = array(
                'key' => 'beneficiary_type',
                'value' => $type,
                'compare' => '='
            );
        }

        if (!empty($status)) {
            $query_args['meta_query'][] = array(
                'key' => 'beneficiary_status',
                'value' => $status,
                'compare' => '='
            );
        }

        $posts = get_posts($query_args);
        
        $result = array_map(function($post) {
            return $this->format_beneficiary_response($post);
        }, $posts);

        wp_send_json_success($result);
    }

    /**
     * Format beneficiary for AJAX response
     */
    private function format_beneficiary_response($post) {
        $image_url = get_the_post_thumbnail_url($post->ID, 'medium');
        $type = get_post_meta($post->ID, 'beneficiary_type', true);
        $status = get_post_meta($post->ID, 'beneficiary_status', true);
        $barangay = get_post_meta($post->ID, 'beneficiary_barangay', true);
        $program = get_post_meta($post->ID, 'beneficiary_program', true);

        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'image' => $image_url,
            'type' => $type,
            'status' => $status,
            'barangay' => $barangay,
            'program' => $program
        );
    }
}

// Initialize viewer
new Beneficiaries_Viewer();
