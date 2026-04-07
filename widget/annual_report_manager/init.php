<?php
/**
 * Annual Report Manager - Main integration file
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/annual_report_manager.php';

if (!function_exists('get_annual_report_uploader')) {
    function get_annual_report_uploader() {
        return Annual_Report_Manager::get_instance()->get_uploader();
    }
}

if (!function_exists('get_annual_report_viewer')) {
    function get_annual_report_viewer() {
        return Annual_Report_Manager::get_instance()->get_viewer();
    }
}

if (!function_exists('display_annual_report_uploader')) {
    function display_annual_report_uploader() {
        get_annual_report_uploader()->display_upload_form();
    }
}

if (!function_exists('display_annual_report_viewer')) {
    function display_annual_report_viewer($args = array()) {
        get_annual_report_viewer()->display_report_viewer($args);
    }
}

if (!function_exists('register_annual_report_admin_menu')) {
    function register_annual_report_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page('Annual Report Manager', 'Annual Reports', 'manage_options', 'annual-report-manager', 'render_annual_report_manager_page', 'dashicons-media-document');
            add_submenu_page('annual-report-manager', 'Upload Annual Report', 'Upload Annual Report', 'manage_options', 'annual-report-upload', 'render_annual_report_upload_page');
            add_submenu_page('annual-report-manager', 'View Annual Reports', 'View Annual Reports', 'manage_options', 'annual-report-view', 'render_annual_report_view_page');
        }
    }
}

if (!function_exists('render_annual_report_upload_page')) {
    function render_annual_report_upload_page() {
        ?>
        <div class="wrap">
            <h1>Upload Annual Report</h1>
            <?php display_annual_report_uploader(); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_annual_report_view_page')) {
    function render_annual_report_view_page() {
        ?>
        <div class="wrap">
            <h1>View Annual Reports</h1>
            <?php display_annual_report_viewer(array('posts_per_page' => 20)); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_annual_report_manager_page')) {
    function render_annual_report_manager_page() {
        $recent = Annual_Report_Manager::get_recent_reports(10);
        ?>
        <div class="wrap">
            <h1>Annual Report Manager Dashboard</h1>
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <h3><?php echo esc_html($recent->found_posts); ?></h3>
                    <p>Total Annual Reports</p>
                </div>
            </div>
            <h2>Recent Annual Reports</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Year</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent->have_posts()): ?>
                        <?php while ($recent->have_posts()): $recent->the_post(); ?>
                            <?php
                            $pdf_id = get_post_meta(get_the_ID(), 'annual_report_pdf_id', true);
                            $year = get_post_meta(get_the_ID(), 'annual_report_year', true);
                            $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
                            ?>
                            <tr>
                                <td><strong><?php the_title(); ?></strong></td>
                                <td><?php echo esc_html($year ?: 'N/A'); ?></td>
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
                            <td colspan="4">No annual reports found.</td>
                        </tr>
                    <?php endif; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

add_action('admin_menu', 'register_annual_report_admin_menu');
