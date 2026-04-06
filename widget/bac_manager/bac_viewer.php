<?php
/**
 * BAC Viewer - Displays BAC items with PDF download
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class BAC_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_bac', array($this, 'handle_bac_search'));
    }

    /**
     * Display BAC viewer with list
     */
    public function display_bac_viewer($args = array()) {
        wp_enqueue_style('bac-viewer-style', plugin_dir_url(__FILE__) . 'css/bac-viewer-style.css');
        wp_enqueue_script('bac-viewer-script', plugin_dir_url(__FILE__) . 'js/bac-viewer.js', array('jquery'));
        
        wp_localize_script('bac-viewer-script', 'bacViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bac_viewer_nonce'),
        ));

        $defaults = array(
            'posts_per_page' => 10,
        );
        $args = wp_parse_args($args, $defaults);

        ?>
        <div class="bac-viewer-container">
            <div class="bac-viewer-header">
                <h2>BAC (Bids and Commissions) Documents</h2>
                <div class="bac-controls">
                    <input type="text" id="bac-search" class="bac-search-input" placeholder="Search BAC...">
                </div>
            </div>

            <div id="bac-list" class="bac-list">
                <?php $this->render_bac_list($args); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render BAC items
     */
    private function render_bac_list($args = array()) {
        $query_args = array(
            'post_type' => 'bac_item',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_bac_item(get_the_ID());
            }
            wp_reset_postdata();
        } else {
            echo '<div class="bac-empty"><p>No BAC documents found.</p></div>';
        }
    }

    /**
     * Render individual BAC item
     */
    private function render_bac_item($post_id) {
        $title = get_the_title($post_id);
        $date = get_the_date('F j, Y', $post_id);
        $pdf_id = get_post_meta($post_id, 'bac_pdf_id', true);
        $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
        $pdf_title = $pdf_id ? get_the_title($pdf_id) : '';

        ?>
        <div class="bac-item">
            <div class="bac-content">
                <h3 class="bac-title"><?php echo esc_html($title); ?></h3>
                <div class="bac-meta">
                    <span class="bac-date"><?php echo esc_html($date); ?></span>
                </div>
            </div>

            <div class="bac-actions">
                <?php if ($pdf_url): ?>
                    <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-download" download="<?php echo esc_attr($pdf_title ?: $title); ?>">
                        📥 Download PDF
                    </a>
                <?php else: ?>
                    <span class="btn btn-download disabled">No PDF</span>
                <?php endif; ?>
                
                <?php if (current_user_can('manage_options')): ?>
                    <button class="btn btn-delete" data-post-id="<?php echo esc_attr($post_id); ?>">
                        🗑️ Delete
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle BAC search AJAX request
     */
    public function handle_bac_search() {
        check_ajax_referer('bac_viewer_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        
        $query_args = array(
            'post_type' => 'bac_item',
            'posts_per_page' => 10,
            's' => $search_term,
            'post_status' => 'publish'
        );

        $query = new WP_Query($query_args);
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $pdf_id = get_post_meta($post_id, 'bac_pdf_id', true);
                $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
                
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'date' => get_the_date('F j, Y', $post_id),
                    'pdf_url' => $pdf_url,
                    'pdf_title' => $pdf_id ? get_the_title($pdf_id) : ''
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success($results);
    }
}

// Initialize viewer
new BAC_Viewer();
