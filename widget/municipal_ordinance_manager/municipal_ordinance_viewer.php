<?php
/**
 * Municipal Ordinance Viewer - Displays ordinance items with PDF download
 */

if (!defined('ABSPATH')) {
    exit;
}

class Municipal_Ordinance_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_municipal_ordinances', array($this, 'handle_ordinance_search'));
    }

    public function display_ordinance_viewer($args = array()) {
        wp_enqueue_style('municipal-ordinance-viewer-style', plugin_dir_url(__FILE__) . 'css/municipal-ordinance-viewer-style.css');
        wp_enqueue_script('municipal-ordinance-viewer-script', plugin_dir_url(__FILE__) . 'js/municipal-ordinance-viewer.js', array('jquery'));

        wp_localize_script('municipal-ordinance-viewer-script', 'municipalOrdinanceViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('municipal_ordinance_viewer_nonce'),
            'canDelete' => current_user_can('manage_options'),
        ));

        $args = wp_parse_args($args, array(
            'posts_per_page' => 10,
        ));
        ?>
        <div class="municipal-ordinance-viewer-container">
            <div class="municipal-ordinance-viewer-header">
                <h2>Municipal Ordinances</h2>
                <div class="municipal-ordinance-controls">
                    <input type="text" id="municipal-ordinance-search" class="municipal-ordinance-search-input" placeholder="Search municipal ordinances...">
                </div>
            </div>

            <div id="municipal-ordinance-list" class="municipal-ordinance-list">
                <?php $this->render_ordinance_list($args); ?>
            </div>
        </div>
        <?php
    }

    private function render_ordinance_list($args = array()) {
        $query = Municipal_Ordinance_Manager::get_ordinances(array(
            'posts_per_page' => $args['posts_per_page'],
        ));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_ordinance_item(get_the_ID());
            }
            wp_reset_postdata();
            return;
        }

        echo '<div class="municipal-ordinance-empty"><p>No municipal ordinances found.</p></div>';
    }

    private function render_ordinance_item($post_id) {
        $title = get_the_title($post_id);
        $date = get_the_date('F j, Y', $post_id);
        $pdf_id = get_post_meta($post_id, 'municipal_ordinance_pdf_id', true);
        $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
        $pdf_title = $pdf_id ? get_the_title($pdf_id) : '';
        ?>
        <div class="municipal-ordinance-item">
            <div class="municipal-ordinance-content">
                <h3 class="municipal-ordinance-title"><?php echo esc_html($title); ?></h3>
                <div class="municipal-ordinance-meta">
                    <span class="municipal-ordinance-date"><?php echo esc_html($date); ?></span>
                </div>
            </div>

            <div class="municipal-ordinance-actions">
                <?php if ($pdf_url): ?>
                    <a href="<?php echo esc_url($pdf_url); ?>" class="btn btn-download" download="<?php echo esc_attr($pdf_title ?: $title); ?>">
                        Download PDF
                    </a>
                <?php else: ?>
                    <span class="btn btn-download disabled">No PDF</span>
                <?php endif; ?>

                <?php if (current_user_can('manage_options')): ?>
                    <button class="btn btn-delete" data-post-id="<?php echo esc_attr($post_id); ?>">
                        Delete
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function handle_ordinance_search() {
        check_ajax_referer('municipal_ordinance_viewer_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        $query = Municipal_Ordinance_Manager::get_ordinances(array(
            'posts_per_page' => 10,
            's' => $search_term,
        ));
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $pdf_id = get_post_meta($post_id, 'municipal_ordinance_pdf_id', true);
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'date' => get_the_date('F j, Y', $post_id),
                    'pdf_url' => $pdf_id ? wp_get_attachment_url($pdf_id) : '',
                    'pdf_title' => $pdf_id ? get_the_title($pdf_id) : '',
                );
            }
        }

        wp_reset_postdata();

        wp_send_json_success($results);
    }
}
