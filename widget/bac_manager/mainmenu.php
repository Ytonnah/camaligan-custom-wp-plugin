<?php
/**
 * BAC Manager - Admin Dashboard Display
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

$bac_uploader = get_bac_uploader();
$bac_viewer = get_bac_viewer();

?>
<div class="wrap">
    <h1>BAC Manager</h1>
    <p>Manage BAC documents and procurement information for your website.</p>

    <div id="bac-admin-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="#bac-upload" class="nav-tab nav-tab-active">Upload Document</a>
            <a href="#bac-list" class="nav-tab">View Documents</a>
        </h2>

        <div id="bac-upload" class="bac-tab-content">
            <div class="bac-admin-section">
                <?php $bac_uploader->display_upload_form(); ?>
            </div>
        </div>

        <div id="bac-list" class="bac-tab-content" style="display: none;">
            <div class="bac-admin-section">
                <?php $bac_viewer->display_bac_viewer(array('posts_per_page' => 20)); ?>
            </div>
        </div>
    </div>
</div>

<style>
    #bac-admin-tabs {
        background: #fff;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .bac-tab-content {
        padding: 20px 0;
    }

    .bac-admin-section {
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
                $('.bac-tab-content').hide();
                
                // Add active class to clicked tab
                $(this).addClass('nav-tab-active');
                
                // Show corresponding content
                var targetId = $(this).attr('href');
                $(targetId).show();
            });
        });
    })(jQuery);
</script>
