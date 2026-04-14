<?php
/**
 * Breadcrumb Widget - Customizable breadcrumb navigation
 */

class Breadcrumb_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'breadcrumb_widget',
            'Breadcrumb Navigator',
            array('description' => 'Customizable breadcrumb navigation for pages.')
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        echo $this->get_breadcrumbs($instance);
        
        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = $instance['title'] ?? '';
        $separator = $instance['separator'] ?? ' / ';
        $show_home = isset($instance['show_home']) ? (bool) $instance['show_home'] : true;
        $show_current = isset($instance['show_current']) ? (bool) $instance['show_current'] : true;
        $home_label = $instance['home_label'] ?? 'Home';
        
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('separator'); ?>">Separator:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('separator'); ?>" name="<?php echo $this->get_field_name('separator'); ?>" type="text" value="<?php echo esc_attr($separator); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('home_label'); ?>">Home Label:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('home_label'); ?>" name="<?php echo $this->get_field_name('home_label'); ?>" type="text" value="<?php echo esc_attr($home_label); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_home); ?> id="<?php echo $this->get_field_id('show_home'); ?>" name="<?php echo $this->get_field_name('show_home'); ?>">
            <label for="<?php echo $this->get_field_id('show_home'); ?>">Show Home</label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_current); ?> id="<?php echo $this->get_field_id('show_current'); ?>" name="<?php echo $this->get_field_name('show_current'); ?>">
            <label for="<?php echo $this->get_field_id('show_current'); ?>">Show Current Page</label>
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['separator'] = (!empty($new_instance['separator'])) ? sanitize_text_field($new_instance['separator']) : ' / ';
        $instance['home_label'] = (!empty($new_instance['home_label'])) ? sanitize_text_field($new_instance['home_label']) : 'Home';
        $instance['show_home'] = isset($new_instance['show_home']) ? (bool) $new_instance['show_home'] : true;
        $instance['show_current'] = isset($new_instance['show_current']) ? (bool) $new_instance['show_current'] : true;
        return $instance;
    }

    public function get_breadcrumbs($instance) {
        $separator = $instance['separator'] ?? ' / ';
        $show_home = $instance['show_home'] ?? true;
        $show_current = $instance['show_current'] ?? true;
        $home_label = $instance['home_label'] ?? 'Home';

        $breadcrumb = array();

        if ($show_home) {
            $breadcrumb[] = '<a href="' . esc_url(home_url()) . '">' . esc_html($home_label) . '</a>';
        }

        if (is_category() || is_tag()) {
            $breadcrumb[] = single_cat_title('', false);
        } elseif (is_page()) {
            $ancestors = get_post_ancestors(get_the_ID());
            foreach (array_reverse($ancestors) as $ancestor) {
                $breadcrumb[] = '<a href="' . esc_url(get_permalink($ancestor)) . '">' . esc_html(get_the_title($ancestor)) . '</a>';
            }
            if ($show_current) {
                $breadcrumb[] = esc_html(get_the_title());
            }
        } elseif (is_single() && !is_attachment()) {
            if (get_post_type() !== 'post') {
                $post_type_object = get_post_type_object(get_post_type());
                $breadcrumb[] = '<a href="' . esc_url(get_post_type_archive_link(get_post_type())) . '">' . $post_type_object->labels->name . '</a>';
            }
            if ($show_current) {
                $breadcrumb[] = esc_html(get_the_title());
            }
        } elseif (is_home()) {
            $breadcrumb[] = esc_html('Blog');
        }

        return '<nav class="breadcrumb-nav" aria-label="Breadcrumb"><ol class="breadcrumb-list">' . 
               implode($separator, $breadcrumb) . 
               '</ol></nav>';
    }
}
?>

