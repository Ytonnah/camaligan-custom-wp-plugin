<?php
/**
 * News Manager Widget - Main Integration File
 * Include this file in your plugin's main file or functions.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include news manager classes
require_once __DIR__ . '/news_manager.php';
require_once __DIR__ . '/news_shortcodes.php';

/**
 * Helper function to get news uploader
 */
if (!function_exists('get_news_uploader')) {
    function get_news_uploader() {
        return News_Manager::get_instance()->get_uploader();
    }
}

/**
 * Helper function to get news viewer
 */
if (!function_exists('get_news_viewer')) {
    function get_news_viewer() {
        return News_Manager::get_instance()->get_viewer();
    }
}

/**
 * Helper function to display uploader form
 */
if (!function_exists('display_news_uploader')) {
    function display_news_uploader() {
        get_news_uploader()->display_upload_form();
    }
}

/**
 * Helper function to display news viewer
 */
if (!function_exists('display_news_viewer')) {
    function display_news_viewer($args = array()) {
        get_news_viewer()->display_news_viewer($args);
    }
}

/**
 * Helper function to display single news
 */
if (!function_exists('display_single_news')) {
    function display_single_news($post_id = null) {
        if ($post_id === null) {
            $post_id = get_the_ID();
        }
        get_news_viewer()->display_single_news($post_id);
    }
}

/**
 * Initialize admin menu (optional)
 */
if (!function_exists('register_news_admin_menu')) {
    function register_news_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page(
                'News Manager',
                'News Manager',
                'manage_options',
                'news-manager',
                'render_news_manager_page',
                'dashicons-newspaper'
            );

            add_submenu_page(
                'news-manager',
                'Upload News',
                'Upload News',
                'manage_options',
                'news-upload',
                'render_news_upload_page'
            );

            add_submenu_page(
                'news-manager',
                'View News',
                'View News',
                'manage_options',
                'news-view',
                'render_news_view_page'
            );
        }
    }
}

/**
 * Render admin page - Upload News
 */
if (!function_exists('render_news_upload_page')) {
    function render_news_upload_page() {
        ?>
        <div class="wrap">
            <h1>Upload News Item</h1>
            <?php display_news_uploader(); ?>
        </div>
        <?php
    }
}

/**
 * Render admin page - View News
 */
if (!function_exists('render_news_view_page')) {
    function render_news_view_page() {
        ?>
        <div class="wrap">
            <h1>View News Items</h1>
            <?php display_news_viewer(array('posts_per_page' => 20)); ?>
        </div>
        <?php
    }
}

/**
 * Render admin page - Dashboard
 */
if (!function_exists('render_news_manager_page')) {
    function render_news_manager_page() {
        $recent = News_Manager::get_recent_news(5);
    ?>
    <div class="wrap">
        <h1>News Manager Dashboard</h1>
        <div style="display: flex; gap: 20px; margin: 20px 0;">
            <div style="flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                <h3><?php echo $recent->found_posts; ?></h3>
                <p>Total News Items</p>
            </div>
        </div>
        <h2>Recent News</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent->have_posts()): ?>
                    <?php while ($recent->have_posts()): $recent->the_post(); ?>
                        <?php
                        $category = get_post_meta(get_the_ID(), 'news_category', true);
                        $priority = get_post_meta(get_the_ID(), 'news_priority', true);
                        ?>
                        <tr>
                            <td><strong><?php the_title(); ?></strong></td>
                            <td><?php echo ucfirst($category ?: 'N/A'); ?></td>
                            <td><?php echo ucfirst($priority ?: 'Normal'); ?></td>
                            <td><?php the_date('F j, Y'); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link(); ?>" class="button">Edit</a>
                                <a href="<?php echo get_delete_post_link(); ?>" class="button button-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No news items found.</td>
                    </tr>
                <?php endif; wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
    <?php
    }
}

// Register admin menu on admin_menu hook
add_action('admin_menu', 'register_news_admin_menu');
