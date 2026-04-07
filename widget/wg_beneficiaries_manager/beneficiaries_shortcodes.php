<?php
/**
 * Beneficiaries Shortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Beneficiaries_Shortcodes {
    
    public function __construct() {
        add_shortcode('beneficiaries_list', array($this, 'shortcode_beneficiaries_list'));
        add_shortcode('beneficiaries_by_type', array($this, 'shortcode_beneficiaries_by_type'));
        add_shortcode('beneficiaries_by_barangay', array($this, 'shortcode_beneficiaries_by_barangay'));
    }

    /**
     * Display all beneficiaries
     * Usage: [beneficiaries_list]
     */
    public function shortcode_beneficiaries_list($atts) {
        $beneficiaries = get_posts(array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        if (empty($beneficiaries)) {
            return '<p>No beneficiaries available</p>';
        }

        ob_start();
        wp_enqueue_style('beneficiaries-shortcode-style', plugin_dir_url(__FILE__) . 'css/beneficiaries-shortcode-style.css');
        ?>

        <div class="beneficiaries-shortcode-list">
            <div class="beneficiaries-grid-shortcode">
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <?php $this->render_shortcode_item($beneficiary); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Display beneficiaries by type
     * Usage: [beneficiaries_by_type type="Individual"]
     */
    public function shortcode_beneficiaries_by_type($atts) {
        $atts = shortcode_atts(array(
            'type' => '',
        ), $atts);

        if (empty($atts['type'])) {
            return '<p>Please specify a beneficiary type</p>';
        }

        $beneficiaries = get_posts(array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'beneficiary_type',
                    'value' => $atts['type'],
                    'compare' => '='
                )
            )
        ));

        if (empty($beneficiaries)) {
            return '<p>No ' . esc_html($atts['type']) . ' beneficiaries found</p>';
        }

        ob_start();
        wp_enqueue_style('beneficiaries-shortcode-style', plugin_dir_url(__FILE__) . 'css/beneficiaries-shortcode-style.css');
        ?>

        <div class="beneficiaries-shortcode-list">
            <h3><?php echo esc_html($atts['type']); ?> Beneficiaries</h3>
            <div class="beneficiaries-grid-shortcode">
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <?php $this->render_shortcode_item($beneficiary); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Display beneficiaries by barangay
     * Usage: [beneficiaries_by_barangay barangay="Poblacion"]
     */
    public function shortcode_beneficiaries_by_barangay($atts) {
        $atts = shortcode_atts(array(
            'barangay' => '',
        ), $atts);

        if (empty($atts['barangay'])) {
            return '<p>Please specify a barangay</p>';
        }

        $beneficiaries = get_posts(array(
            'post_type' => 'beneficiary_item',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => 'beneficiary_barangay',
                    'value' => $atts['barangay'],
                    'compare' => '='
                )
            )
        ));

        if (empty($beneficiaries)) {
            return '<p>No beneficiaries from ' . esc_html($atts['barangay']) . '</p>';
        }

        ob_start();
        wp_enqueue_style('beneficiaries-shortcode-style', plugin_dir_url(__FILE__) . 'css/beneficiaries-shortcode-style.css');
        ?>

        <div class="beneficiaries-shortcode-list">
            <h3><?php echo esc_html($atts['barangay']); ?> Beneficiaries</h3>
            <div class="beneficiaries-grid-shortcode">
                <?php foreach ($beneficiaries as $beneficiary): ?>
                    <?php $this->render_shortcode_item($beneficiary); ?>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Render beneficiary item for shortcode
     */
    private function render_shortcode_item($beneficiary) {
        $image_url = get_the_post_thumbnail_url($beneficiary->ID, 'thumbnail');
        $type = get_post_meta($beneficiary->ID, 'beneficiary_type', true);
        $status = get_post_meta($beneficiary->ID, 'beneficiary_status', true);
        $barangay = get_post_meta($beneficiary->ID, 'beneficiary_barangay', true);
        $program = get_post_meta($beneficiary->ID, 'beneficiary_program', true);
        ?>
        
        <div class="beneficiary-shortcode-item">
            <?php if ($image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($beneficiary->post_title); ?>" class="beneficiary-thumbnail">
            <?php else: ?>
                <div class="beneficiary-thumbnail placeholder">
                    <span><?php echo strtoupper(substr($beneficiary->post_title, 0, 1)); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="shortcode-item-info">
                <h4><?php echo esc_html($beneficiary->post_title); ?></h4>
                <p class="item-barangay"><?php echo esc_html($barangay); ?></p>
                <p class="item-program"><?php echo esc_html($program); ?></p>
                <div class="item-badges">
                    <span class="badge small"><?php echo esc_html($type); ?></span>
                    <span class="badge small status-<?php echo esc_attr(strtolower($status)); ?>"><?php echo esc_html($status); ?></span>
                </div>
            </div>
        </div>
        
        <?php
    }
}

// Initialize shortcodes
new Beneficiaries_Shortcodes();
