<?php
/**
 * Tourism Manager - Admin Dashboard Display
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

$tourism_uploader = get_tourism_uploader();
$tourism_viewer = get_tourism_viewer();

?>
<div class="wrap">
    <h1>Tourism Manager</h1>
    <p>Manage tourism destinations for your website.</p>

    <div id="tourism-admin-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="#tourism-upload" class="nav-tab nav-tab-active">Upload Destination</a>
            <a href="#tourism-list" class="nav-tab">View Destinations</a>
        </h2>

        <div id="tourism-upload" class="tourism-tab-content">
            <div class="tourism-admin-section">
                <?php $tourism_uploader->display_upload_form(); ?>
            </div>
        </div>

        <div id="tourism-list" class="tourism-tab-content" style="display: none;">
            <div class="tourism-admin-section">
                <?php $tourism_viewer->display_tourism_viewer(array('posts_per_page' => 20)); ?>
            </div>
        </div>
    </div>
</div>

<style>
    #tourism-admin-tabs {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .tourism-tab-content {
        padding: 20px 0;
    }

    .tourism-admin-section {
        background: #f9f9f9;
        padding: 20px;
        border-radius: 5px;
        border: 1px solid #ddd;
    }

    .nav-tab {
        padding: 12px 20px;
        margin-right: 5px;
        background-color: #f1f1f1;
        color: #333;
        text-decoration: none;
        border: 1px solid #ddd;
        border-bottom: none;
        border-radius: 4px 4px 0 0;
        cursor: pointer;
    }

    .nav-tab:hover {
        background-color: #e5e5e5;
    }

    .nav-tab.nav-tab-active {
        background-color: #fff;
        color: #0073aa;
        border: 1px solid #ddd;
        border-bottom-color: #fff;
        font-weight: bold;
    }
</style>

<script>
    (function($) {
        $(document).ready(function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                $('.nav-tab').removeClass('nav-tab-active');
                $('.tourism-tab-content').hide();
                
                // Add active class to clicked tab
                $(this).addClass('nav-tab-active');
                
                // Show corresponding content
                var targetId = $(this).attr('href');
                $(targetId).show();
            });
        });
    })(jQuery);
</script>
