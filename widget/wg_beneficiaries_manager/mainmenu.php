<?php
/**
 * Beneficiaries Manager Admin Dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

require_once 'beneficiaries_uploader.php';
require_once 'beneficiaries_viewer.php';

class Beneficiaries_Admin {
    
    private $uploader;
    private $viewer;
    
    public function __construct() {
        $this->uploader = new Beneficiaries_Uploader();
        $this->viewer = new Beneficiaries_Viewer();
    }

    /**
     * Display admin dashboard
     */
    public function display_dashboard() {
        ?>
        <div class="beneficiaries-dashboard">
            <div class="dashboard-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="add">➕ Add Beneficiary</button>
                    <button class="tab-btn" data-tab="view">👥 View Beneficiaries</button>
                </div>

                <div id="add-tab" class="tab-content active">
                    <?php $this->uploader->display_upload_form(); ?>
                </div>

                <div id="view-tab" class="tab-content">
                    <?php $this->viewer->display_beneficiaries_viewer(); ?>
                </div>
            </div>
        </div>

        <style>
            .beneficiaries-dashboard {
                background: white;
                border-radius: 8px;
                margin: 20px 0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .dashboard-tabs {
                padding: 20px;
            }

            .tab-buttons {
                display: flex;
                gap: 10px;
                margin-bottom: 20px;
                border-bottom: 2px solid #eee;
            }

            .tab-btn {
                background: none;
                border: none;
                padding: 12px 20px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                color: #666;
                border-bottom: 3px solid transparent;
                margin-bottom: -2px;
                transition: all 0.3s;
            }

            .tab-btn:hover {
                color: #0073aa;
            }

            .tab-btn.active {
                color: #0073aa;
                border-bottom-color: #0073aa;
            }

            .tab-content {
                display: none;
                animation: fadeIn 0.3s;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .tab-content.active {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
        </style>

        <script>
            jQuery(document).ready(function($) {
                $('.tab-btn').on('click', function() {
                    var tabName = $(this).data('tab');
                    
                    $('.tab-btn').removeClass('active');
                    $('.tab-content').removeClass('active');
                    
                    $(this).addClass('active');
                    $('#' + tabName + '-tab').addClass('active');
                });
            });
        </script>
        <?php
    }
}

// Initialize dashboard
$beneficiaries_admin = new Beneficiaries_Admin();
