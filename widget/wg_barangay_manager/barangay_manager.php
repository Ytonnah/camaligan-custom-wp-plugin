<?php
/**
 * Barangay Manager - Main Manager Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Barangay_Manager {
    private static $instance = null;
    
    private $uploader;
    private $viewer;
    
    private function __construct() {
        $this->uploader = new Barangay_Uploader();
        $this->viewer = new Barangay_Viewer();
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function get_uploader() {
        return $this->uploader;
    }
    
    public function get_viewer() {
        return $this->viewer;
    }
}
?>
</xai:function_call



<xai:function_call name="create_file">
<parameter name="absolute_path">c:/xampp/htdocs/wordpress/wp-content/plugins/camaligan-customization/widget/wg_barangay_manager/barangay_uploader.php
