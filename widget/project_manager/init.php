<?php
/**
 * Project Manager - Main integration file
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/project_manager.php';

if (!function_exists('register_project_manager_admin_menu')) {
    function register_project_manager_admin_menu() {
        if (is_admin() && current_user_can('manage_options')) {
            add_menu_page('Project Manager', 'Projects', 'manage_options', 'project-manager', 'render_project_manager_page', 'dashicons-portfolio');
            add_submenu_page('project-manager', 'All Projects', 'All Projects', 'manage_options', 'edit.php?post_type=project');
            add_submenu_page('project-manager', 'Add New Project', 'Add New Project', 'manage_options', 'post-new.php?post_type=project');
            add_submenu_page('project-manager', 'Project Statuses', 'Project Statuses', 'manage_options', 'edit-tags.php?taxonomy=project_status&post_type=project');
        }
    }
}

if (!function_exists('render_project_manager_page')) {
    function render_project_manager_page() {
        $recent = Project_Manager::get_recent_projects(10);
        $status_counts = Project_Manager::get_status_counts();
        ?>
        <div class="wrap">
            <h1>Project Manager Dashboard</h1>
            <p>Manage project records for awarded, ongoing, and completed project listings.</p>

            <div style="display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap;">
                <?php foreach (Project_Manager::get_allowed_statuses() as $slug => $label): ?>
                    <div style="min-width: 180px; flex: 1; padding: 20px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
                        <h3><?php echo esc_html($status_counts[$slug]); ?></h3>
                        <p><?php echo esc_html($label); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <p>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=project')); ?>" class="button button-primary">Add New Project</a>
                <a href="<?php echo esc_url(rest_url('wp/v2/projects')); ?>" class="button">View REST Endpoint</a>
            </p>

            <h2>Recent Projects</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent->have_posts()): ?>
                        <?php while ($recent->have_posts()): $recent->the_post(); ?>
                            <?php $statuses = get_the_terms(get_the_ID(), 'project_status'); ?>
                            <tr>
                                <td><strong><?php the_title(); ?></strong></td>
                                <td><?php echo esc_html(!empty($statuses) && !is_wp_error($statuses) ? $statuses[0]->name : 'N/A'); ?></td>
                                <td><?php echo esc_html(get_the_date('F j, Y')); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="button">Edit</a>
                                    <a href="<?php echo esc_url(get_delete_post_link()); ?>" class="button button-danger">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No projects found.</td>
                        </tr>
                    <?php endif; wp_reset_postdata(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

add_action('admin_menu', 'register_project_manager_admin_menu');
