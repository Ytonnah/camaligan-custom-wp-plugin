<?php
/**
 * Municipal Ordinance Manager - Main integration file
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/municipal_ordinance_manager.php';

if (!function_exists('get_municipal_ordinance_uploader')) {
    function get_municipal_ordinance_uploader() {
        return Municipal_Ordinance_Manager::get_instance()->get_uploader();
    }
}

if (!function_exists('get_municipal_ordinance_viewer')) {
    function get_municipal_ordinance_viewer() {
        return Municipal_Ordinance_Manager::get_instance()->get_viewer();
    }
}

if (!function_exists('display_municipal_ordinance_uploader')) {
    function display_municipal_ordinance_uploader() {
        get_municipal_ordinance_uploader()->display_upload_form();
    }
}

if (!function_exists('display_municipal_ordinance_viewer')) {
    function display_municipal_ordinance_viewer($args = array()) {
        get_municipal_ordinance_viewer()->display_ordinance_viewer($args);
    }
}

if (!function_exists('register_municipal_ordinance_admin_menu')) {
    function register_municipal_ordinance_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page('Municipal Ordinance Manager', 'Municipal Ordinances', 'manage_options', 'municipal-ordinance-manager', 'render_municipal_ordinance_manager_page', 'dashicons-media-document');
            add_submenu_page('municipal-ordinance-manager', 'Upload Municipal Ordinance', 'Upload Municipal Ordinance', 'manage_options', 'municipal-ordinance-upload', 'render_municipal_ordinance_upload_page');
            add_submenu_page('municipal-ordinance-manager', 'View Municipal Ordinances', 'View Municipal Ordinances', 'manage_options', 'municipal-ordinance-view', 'render_municipal_ordinance_view_page');
        }
    }
}

if (!function_exists('render_municipal_ordinance_upload_page')) {
    function render_municipal_ordinance_upload_page() {
        ?>
        <div class="wrap">
            <h1>Upload Municipal Ordinance</h1>
            <?php display_municipal_ordinance_uploader(); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_municipal_ordinance_view_page')) {
    function render_municipal_ordinance_view_page() {
        ?>
        <div class="wrap">
            <h1>View Municipal Ordinances</h1>
            <?php display_municipal_ordinance_viewer(array('posts_per_page' => 20)); ?>
        </div>
        <?php
    }
}

if (!function_exists('render_municipal_ordinance_manager_page')) {
    function render_municipal_ordinance_manager_page() {
        $recent = Municipal_Ordinance_Manager::get_recent_ordinances(10);
        ?>
        <div class="wrap">
            <h1>Municipal Ordinance Manager Dashboard</h1>
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <h3><?php echo esc_html($recent->found_posts); ?></h3>
                    <p>Total Municipal Ordinances</p>
                </div>
            </div>
            <h2>Recent Municipal Ordinances</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent->have_posts()): ?>
                        <?php while ($recent->have_posts()): $recent->the_post(); ?>
                            <?php
                            $pdf_id = get_post_meta(get_the_ID(), 'municipal_ordinance_pdf_id', true);
                            $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
                            ?>
                            <tr>
                                <td><strong><?php the_title(); ?></strong></td>
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
                            <td colspan="3">No municipal ordinances found.</td>
                        </tr>
                    <?php endif; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

add_action('admin_menu', 'register_municipal_ordinance_admin_menu');
