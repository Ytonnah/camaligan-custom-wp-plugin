<?php
/**
 * Media Gallery Shortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Media_Gallery_Shortcodes {
    
    public function __construct() {
        add_shortcode('media_gallery', array($this, 'shortcode_media_gallery'));
        add_shortcode('gallery_list', array($this, 'shortcode_gallery_list'));
        add_shortcode('gallery_slider', array($this, 'shortcode_gallery_slider'));
    }

    /**
     * Display specific gallery by ID
     * Usage: [media_gallery id="123"]
     */
    public function shortcode_media_gallery($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $gallery_id = absint($atts['id']);
        if (!$gallery_id) {
            return '<p>Gallery ID not provided</p>';
        }

        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'media_gallery') {
            return '<p>Gallery not found</p>';
        }

        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        if (!is_array($gallery_images) || empty($gallery_images)) {
            return '<p>No images in this gallery</p>';
        }

        ob_start();
        wp_enqueue_style('media-gallery-shortcode-style', plugin_dir_url(__FILE__) . 'css/media-gallery-shortcode-style.css');
        wp_enqueue_script('media-gallery-viewer-script', plugin_dir_url(__FILE__) . 'js/media-gallery-viewer.js', array('jquery'));
        ?>

        <div class="media-gallery-shortcode">
            <h3><?php echo esc_html($gallery->post_title); ?></h3>
            <?php if (!empty($gallery->post_content)): ?>
                <p class="gallery-intro"><?php echo wp_kses_post($gallery->post_content); ?></p>
            <?php endif; ?>

            <div class="gallery-grid">
                <?php foreach ($gallery_images as $image_id): ?>
                    <?php
                    $image_url = wp_get_attachment_image_url($image_id, 'medium');
                    $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    $caption = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    ?>
                    <div class="gallery-item">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($caption); ?>" class="gallery-thumbnail" 
                             data-full-url="<?php echo esc_url($image_url); ?>" 
                             data-caption="<?php echo esc_attr($caption); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Display all galleries as list
     * Usage: [gallery_list]
     */
    public function shortcode_gallery_list($atts) {
        $galleries = get_posts(array(
            'post_type' => 'media_gallery',
            'posts_per_page' => 12,
            'post_status' => 'publish'
        ));

        if (empty($galleries)) {
            return '<p>No galleries available</p>';
        }

        ob_start();
        wp_enqueue_style('media-gallery-list-style', plugin_dir_url(__FILE__) . 'css/media-gallery-list-style.css');
        ?>

        <div class="galleries-list-shortcode">
            <div class="galleries-grid">
                <?php foreach ($galleries as $gallery):
                    $gallery_images = get_post_meta($gallery->ID, 'gallery_images', true);
                    $cover_image = !empty($gallery_images) ? wp_get_attachment_image_url($gallery_images[0], 'medium') : '';
                    $image_count = is_array($gallery_images) ? count($gallery_images) : 0;
                    ?>
                    <div class="gallery-preview-card">
                        <?php if ($cover_image): ?>
                            <img src="<?php echo esc_url($cover_image); ?>" alt="<?php echo esc_attr($gallery->post_title); ?>" class="gallery-preview">
                        <?php else: ?>
                            <div class="gallery-preview placeholder">No images</div>
                        <?php endif; ?>
                        <div class="preview-overlay">
                            <h4><?php echo esc_html($gallery->post_title); ?></h4>
                            <p><?php echo esc_html($image_count) . ' image' . ($image_count !== 1 ? 's' : ''); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Display gallery as slider/carousel
     * Usage: [gallery_slider id="123"]
     */
    public function shortcode_gallery_slider($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $gallery_id = absint($atts['id']);
        if (!$gallery_id) {
            return '<p>Gallery ID not provided</p>';
        }

        $gallery = get_post($gallery_id);
        if (!$gallery || $gallery->post_type !== 'media_gallery') {
            return '<p>Gallery not found</p>';
        }

        $gallery_images = get_post_meta($gallery_id, 'gallery_images', true);
        if (!is_array($gallery_images) || empty($gallery_images)) {
            return '<p>No images in this gallery</p>';
        }

        op_start();
        wp_enqueue_style('media-gallery-slider-style', plugin_dir_url(__FILE__) . 'css/media-gallery-slider-style.css');
        wp_enqueue_script('media-gallery-slider-script', plugin_dir_url(__FILE__) . 'js/media-gallery-slider.js', array('jquery'));
        
        $slider_id = 'slider-' . $gallery_id;
        ?>

        <div class="media-gallery-slider" id="<?php echo esc_attr($slider_id); ?>">
            <div class="slider-container">
                <div class="slider-track">
                    <?php foreach ($gallery_images as $image_id): ?>
                        <?php $image_url = wp_get_attachment_image_url($image_id, 'large'); ?>
                        <div class="slider-item">
                            <img src="<?php echo esc_url($image_url); ?>" alt="">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="slider-nav prev" onclick="sliderPrev('<?php echo esc_js($slider_id); ?>')">❮</button>
                <button class="slider-nav next" onclick="sliderNext('<?php echo esc_js($slider_id); ?>')">❯</button>
            </div>
            <div class="slider-dots">
                <?php foreach ($gallery_images as $index => $image_id): ?>
                    <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="sliderGo('<?php echo esc_js($slider_id); ?>', <?php echo esc_js($index); ?>)"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}

// Initialize shortcodes
new Media_Gallery_Shortcodes();
