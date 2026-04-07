<?php
/**
 * Budget Overview Viewer - Displays budget overview items with PDF download
 */

if (!defined('ABSPATH')) {
    exit;
}

class Budget_Overview_Viewer {

    public function __construct() {
        add_action('wp_ajax_search_budget_overviews', array($this, 'handle_budget_search'));
    }

    public function display_budget_viewer($args = array()) {
        wp_enqueue_style('budget-overview-viewer-style', plugin_dir_url(__FILE__) . 'css/budget-overview-viewer-style.css');
        wp_enqueue_script('budget-overview-viewer-script', plugin_dir_url(__FILE__) . 'js/budget-overview-viewer.js', array('jquery'));

        wp_localize_script('budget-overview-viewer-script', 'budgetOverviewViewerData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('budget_overview_viewer_nonce'),
            'canDelete' => current_user_can('manage_options'),
        ));

        $args = wp_parse_args($args, array(
            'posts_per_page' => 10,
        ));
        ?>
        <div class="budget-overview-viewer-container">
            <div class="budget-overview-viewer-header">
                <h2>Budget Overviews</h2>
                <div class="budget-overview-controls">
                    <input type="text" id="budget-overview-search" class="budget-overview-search-input" placeholder="Search budget overviews...">
                </div>
            </div>

            <div id="budget-overview-list" class="budget-overview-list">
                <?php $this->render_budget_list($args); ?>
            </div>
        </div>
        <?php
    }

    private function render_budget_list($args = array()) {
        $query = new WP_Query(array(
            'post_type' => 'budget_overview',
            'posts_per_page' => $args['posts_per_page'],
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
        ));

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_budget_item(get_the_ID());
            }
            wp_reset_postdata();
            return;
        }

        echo '<div class="budget-overview-empty"><p>No budget overviews found.</p></div>';
    }

    private function render_budget_item($post_id) {
        $title = get_the_title($post_id);
        $date = get_the_date('F j, Y', $post_id);
        $year = get_post_meta($post_id, 'budget_overview_year', true);
        $ordinance_no = get_post_meta($post_id, 'budget_overview_ordinance_no', true);
        $total_budget = get_post_meta($post_id, 'budget_overview_total_budget', true);
        $pdf_id = get_post_meta($post_id, 'budget_overview_pdf_id', true);
        $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
        $pdf_title = $pdf_id ? get_the_title($pdf_id) : '';
        ?>
        <div class="budget-overview-item">
            <div class="budget-overview-content">
                <h3 class="budget-overview-title"><?php echo esc_html($title); ?></h3>
                <div class="budget-overview-meta">
                    <span class="budget-overview-year"><?php echo esc_html($year ? 'Year: ' . $year : 'Year not set'); ?></span>
                    <span class="budget-overview-ordinance"><?php echo esc_html($ordinance_no ? 'Ordinance No.: ' . $ordinance_no : 'Ordinance not set'); ?></span>
                    <span class="budget-overview-total"><?php echo esc_html($total_budget ? 'Total Budget: ' . $total_budget : 'Budget not set'); ?></span>
                    <span class="budget-overview-date"><?php echo esc_html($date); ?></span>
                </div>
            </div>

            <div class="budget-overview-actions">
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

    public function handle_budget_search() {
        check_ajax_referer('budget_overview_viewer_nonce', 'nonce');

        $search_term = sanitize_text_field($_POST['search_term'] ?? '');
        $query = new WP_Query(array(
            'post_type' => 'budget_overview',
            'posts_per_page' => 10,
            's' => $search_term,
            'post_status' => 'publish',
        ));
        $results = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $pdf_id = get_post_meta($post_id, 'budget_overview_pdf_id', true);
                $results[] = array(
                    'ID' => $post_id,
                    'title' => get_the_title($post_id),
                    'date' => get_the_date('F j, Y', $post_id),
                    'year' => get_post_meta($post_id, 'budget_overview_year', true),
                    'ordinance_no' => get_post_meta($post_id, 'budget_overview_ordinance_no', true),
                    'total_budget' => get_post_meta($post_id, 'budget_overview_total_budget', true),
                    'pdf_url' => $pdf_id ? wp_get_attachment_url($pdf_id) : '',
                    'pdf_title' => $pdf_id ? get_the_title($pdf_id) : '',
                );
            }
        }

        wp_reset_postdata();

        wp_send_json_success($results);
    }
}

new Budget_Overview_Viewer();
