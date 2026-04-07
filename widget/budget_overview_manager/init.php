<?php
/**
 * Budget Overview Manager - Main integration file
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/budget_overview_manager.php';

if (!function_exists('get_budget_overview_uploader')) {
    function get_budget_overview_uploader() {
        return Budget_Overview_Manager::get_instance()->get_uploader();
    }
}

if (!function_exists('get_budget_overview_viewer')) {
    function get_budget_overview_viewer() {
        return Budget_Overview_Manager::get_instance()->get_viewer();
    }
}

if (!function_exists('display_budget_overview_uploader')) {
    function display_budget_overview_uploader() {
        get_budget_overview_uploader()->display_upload_form();
    }
}

if (!function_exists('display_budget_overview_viewer')) {
    function display_budget_overview_viewer($args = array()) {
        get_budget_overview_viewer()->display_budget_viewer($args);
    }
}

if (!function_exists('register_budget_overview_admin_menu')) {
    function register_budget_overview_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page('Budget Overview Manager', 'Budget Overview', 'manage_options', 'budget-overview-manager', 'render_budget_overview_manager_page', 'dashicons-chart-pie');
            add_submenu_page('budget-overview-manager', 'Upload Budget Overview', 'Upload Budget Overview', 'manage_options', 'budget-overview-upload', 'render_budget_overview_upload_page');
            add_submenu_page('budget-overview-manager', 'View Budget Overviews', 'View Budget Overviews', 'manage_options', 'budget-overview-view', 'render_budget_overview_view_page');
        }
    }
}

if (!function_exists('render_budget_overview_upload_page')) {
    function render_budget_overview_upload_page() {
        ?>
        <div class="wrap">
            <h1>Upload Budget Overview</h1>
            <?php display_budget_overview_uploader(); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_budget_overview_view_page')) {
    function render_budget_overview_view_page() {
        ?>
        <div class="wrap">
            <h1>View Budget Overviews</h1>
            <?php display_budget_overview_viewer(array('posts_per_page' => 20)); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_budget_overview_manager_page')) {
    function render_budget_overview_manager_page() {
        $recent = Budget_Overview_Manager::get_recent_budgets(10);
        ?>
        <div class="wrap">
            <h1>Budget Overview Manager Dashboard</h1>
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <h3><?php echo esc_html($recent->found_posts); ?></h3>
                    <p>Total Budget Overviews</p>
                </div>
            </div>
            <h2>Recent Budget Overviews</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Ordinance No.</th>
                        <th>Total Budget</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent->have_posts()): ?>
                        <?php while ($recent->have_posts()): $recent->the_post(); ?>
                            <?php
                            $pdf_id = get_post_meta(get_the_ID(), 'budget_overview_pdf_id', true);
                            $year = get_post_meta(get_the_ID(), 'budget_overview_year', true);
                            $ordinance_no = get_post_meta(get_the_ID(), 'budget_overview_ordinance_no', true);
                            $total_budget = get_post_meta(get_the_ID(), 'budget_overview_total_budget', true);
                            $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
                            ?>
                            <tr>
                                <td><?php echo esc_html($year ?: 'N/A'); ?></td>
                                <td><?php echo esc_html($ordinance_no ?: 'N/A'); ?></td>
                                <td><?php echo esc_html($total_budget ?: 'N/A'); ?></td>
                                <td><?php echo esc_html(get_the_date('F j, Y')); ?></td>
                                <td>
                                    <?php if ($pdf_url): ?>
                                        <a href="<?php echo esc_url($pdf_url); ?>" class="button" download>Download PDF</a>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="button">Edit</a>
                                    <a href="<?php echo esc_url(get_delete_post_link()); ?>" class="button button-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No budget overviews found.</td>
                        </tr>
                    <?php endif; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

add_action('admin_menu', 'register_budget_overview_admin_menu');
