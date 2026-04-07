<?php
/**
 * Annual Report Viewer - Displays report items with PDF download
 */

if (!defined('ABSPATH')) {
    exit;
}

class Annual_Report_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_annual_reports', array($this, 'handle_report_search'));
    }

    public function display_report_viewer($args = array()) {
        wp_enqueue_style('annual-report-viewer-style', plugin_dir_url(__FILE__) . 'css/annual-report-viewer-style.css');
        wp_enqueue_script('annual-report-viewer-script', plugin_dir_url(__FILE__) . 'js/annual-report-viewer.js', array('jquery'));

        wp_localize_script('annual-report-viewer-script', 'annualReportViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('annual_report_viewer_nonce'),
            'canDelete' => current_user_can('manage_options'),
        ));

        $args = wp_parse_args($args, array(
            'posts_per_page' => 10,
        ));
        ?>
        <div class="annual-report-viewer-container">
            <div class="annual-report-viewer-header">
                <h2>Annual Reports</h2>
                <div class="annual-report-controls">
                    <input type="text" id="annual-report-search" class="annual-report-search-input" placeholder="Search annual reports...">
                </div>
            </div>

            <div id="annual-report-list" class="annual-report-list">
                <?php $this->render_report_list($args); ?>
            </div>
        </div>
        <?php
    }

    private function render_report_list($args = array()) {
        $query = new WP_Query(array(
            'post_type' => 'annual_report',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        ));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_report_item(get_the_ID());
            }
            wp_reset_postdata();
            return;
        }

        echo '<div class="annual-report-empty"><p>No annual reports found.</p></div>';
    }

    private function render_report_item($post_id) {
        $title = get_the_title($post_id);
        $date = get_the_date('F j, Y', $post_id);
        $year = get_post_meta($post_id, 'annual_report_year', true);
        $pdf_id = get_post_meta($post_id, 'annual_report_pdf_id', true);
        $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
        $pdf_title = $pdf_id ? get_the_title($pdf_id) : '';
        ?>
        <div class="annual-report-item">
            <div class="annual-report-content">
                <h3 class="annual-report-title"><?php echo esc_html($title); ?></h3>
                <div class="annual-report-meta">
                    <span class="annual-report-year"><?php echo esc_html($year ? 'Year: ' . $year : 'Year not set'); ?></span>
                    <span class="annual-report-date"><?php echo esc_html($date); ?></span>
                </div>
            </div>

            <div class="annual-report-actions">
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

    public function handle_report_search() {
        check_ajax_referer('annual_report_viewer_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        $query = new WP_Query(array(
            'post_type' => 'annual_report',
            'posts_per_page' => 10,
            's' => $search_term,
            'post_status' => 'publish',
        ));
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $pdf_id = get_post_meta($post_id, 'annual_report_pdf_id', true);
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'date' => get_the_date('F j, Y', $post_id),
                    'year' => get_post_meta($post_id, 'annual_report_year', true),
                    'pdf_url' => $pdf_id ? wp_get_attachment_url($pdf_id) : '',
                    'pdf_title' => $pdf_id ? get_the_title($pdf_id) : '',
                );
            }
        }

        wp_reset_postdata();

        wp_send_json_success($results);
    }
}

new Annual_Report_Viewer();
