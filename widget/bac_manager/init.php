<?php
/**
 * BAC Manager Widget - Main Integration File
 * Include this file in your plugin's main file or functions.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include BAC manager classes
require_once __DIR__ . '/bac_manager.php';
require_once __DIR__ . '/bac_shortcodes.php';

/**
 * Helper function to get BAC uploader
 */
if (!function_exists('get_bac_uploader')) {
    function get_bac_uploader() {
        return BAC_Manager::get_instance()->get_uploader();
    }
}

/**
 * Helper function to get BAC viewer
 */
if (!function_exists('get_bac_viewer')) {
    function get_bac_viewer() {
        return BAC_Manager::get_instance()->get_viewer();
    }
}

/**
 * Helper function to display uploader form
 */
if (!function_exists('display_bac_uploader')) {
    function display_bac_uploader() {
        get_bac_uploader()->display_upload_form();
    }
}

/**
 * Helper function to display BAC viewer
 */
if (!function_exists('display_bac_viewer')) {
    function display_bac_viewer($args = array()) {
        get_bac_viewer()->display_bac_viewer($args);
    }
}

/**
 * Initialize admin menu (optional)
 */
if (!function_exists('register_bac_admin_menu')) {
    function register_bac_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page(
                'BAC Manager',
                'BAC Manager',
                'manage_options',
                'bac-manager',
                'render_bac_manager_page',
                'dashicons-media-document'
            );

            add_submenu_page(
                'bac-manager',
                'Upload BAC',
                'Upload BAC',
                'manage_options',
                'bac-upload',
                'render_bac_upload_page'
            );

            add_submenu_page(
                'bac-manager',
                'View BAC',
                'View BAC',
                'manage_options',
                'bac-view',
                'render_bac_view_page'
            );
        }
    }
}

/**
 * Render admin page - Upload BAC
 */
if (!function_exists('render_bac_upload_page')) {
    function render_bac_upload_page() {
        ?>
        <div class="wrap">
            <h1>Upload BAC Document</h1>
            <?php display_bac_uploader(); ?>
        </div>
        <?php
    }
}

/**
 * Render admin page - View BAC
 */
if (!function_exists('render_bac_view_page')) {
    function render_bac_view_page() {
        ?>
        <div class="wrap">
            <h1>View BAC Documents</h1>
            <?php display_bac_viewer(array('posts_per_page' => 20)); ?>
        </div>
        <?php
    }
}

/**
 * Render admin page - Dashboard
 */
if (!function_exists('render_bac_manager_page')) {
    function render_bac_manager_page() {
        $recent = BAC_Manager::get_recent_bac(10);
        ?>
        <div class="wrap">
            <h1>BAC Manager Dashboard</h1>
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                    <h3><?php echo $recent->found_posts; ?></h3>
                    <p>Total BAC Documents</p>
                </div>
            </div>
            <h2>Recent BAC Documents</h2>
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
                            $pdf_id = get_post_meta(get_the_ID(), 'bac_pdf_id', true);
                            $pdf_url = $pdf_id ? wp_get_attachment_url($pdf_id) : '';
                            ?>
                            <tr>
                                <td><strong><?php the_title(); ?></strong></td>
                                <td><?php the_date('F j, Y'); ?></td>
                                <td>
                                    <?php if ($pdf_url): ?>
                                        <a href="<?php echo esc_url($pdf_url); ?>" class="button" download>Download PDF</a>
                                    <?php endif; ?>
                                    <a href="<?php echo get_edit_post_link(); ?>" class="button">Edit</a>
                                    <a href="<?php echo get_delete_post_link(); ?>" class="button button-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No BAC documents found.</td>
                        </tr>
                    <?php endif; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

// Register admin menu on admin_menu hook
add_action('admin_menu', 'register_bac_admin_menu');
