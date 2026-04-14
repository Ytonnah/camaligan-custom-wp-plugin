<?php
/**
 * Barangay Manager Admin Menu
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/barangay_uploader.php';
require_once __DIR__ . '/barangay_viewer.php';
require_once __DIR__ . '/barangay_manager.php';

if (!current_user_can('manage_options')) wp_die('No permission.');

?>
<div class="wrap">
    <h1>Barangay Profiles Manager</h1>

    <div id="barangay-admin-tabs">
        <h2 class="nav-tab-wrapper">
            <a href="#barangay-upload" class="nav-tab nav-tab-active">Upload Profile</a>
            <a href="#barangay-list" class="nav-tab">View Profiles</a>
        </h2>

        <div id="barangay-upload" class="tab-content">
            <?php $uploader = new Barangay_Uploader(); $uploader->display_upload_form(); ?>
        </div>

        <div id="barangay-list" class="tab-content" style="display:none;">
            <?php $viewer = new Barangay_Viewer(); $viewer->display_barangay_viewer(array('posts_per_page' => 20)); ?>
        </div>
    </div>
</div>

<style>
/* Tab styles */
#barangay-admin-tabs .nav-tab {padding:12px 20px;margin-right:5px;background:#f1f1f1;color:#333;border:1px solid #ddd;border-bottom:none;border-radius:4px 4px 0 0;}
#barangay-admin-tabs .nav-tab:hover {background:#e5e5e5;}
#barangay-admin-tabs .nav-tab-active {background:#fff;color:#0073aa;border-bottom-color:#fff;font-weight:bold;}
.tab-content {padding:20px;background:#fff;border:1px solid #ddd;border-radius:0 0 4px 4px;}
</style>

<script>
jQuery(document).ready(function($) {
    $('.nav-tab').click(function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });
});
</script>
<?php
?>

