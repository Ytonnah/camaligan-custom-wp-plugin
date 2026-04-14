<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include breadcrumb manager files
require_once plugin_dir_path(__FILE__) . 'breadcrumb_widget.php';
require_once plugin_dir_path(__FILE__) . 'breadcrumb_shortcodes.php';

function render_breadcrumb_manager_page() {
    $path = plugin_dir_path(__FILE__) . 'mainmenu.php';
    if (file_exists($path)) {
        include $path;
    } else {
        echo '<div class="wrap"><h1>Breadcrumb Manager</h1><p>Main menu not found.</p></div>';
    }
}
?>

