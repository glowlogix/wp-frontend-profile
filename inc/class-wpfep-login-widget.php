<?php
/**
 * Login Widget.
 * Main widget class.
 */
class wpfep_login_Widget extends WP_Widget
{
    /**
     *  Refresh widget actions.
     */
    public function __construct()
    {
        parent::__construct(
            'wpfep-login-widget',
            __('WP Frontend profile Widget', 'wp-front-end-profile'),
            array(
                'customize_selective_refresh' => true,
            )
        );
    }
    // The widget form (for the backend ).
    public function form($instance)
    {
        // Set widget defaults.
        $defaults = array(
            'title' => __('Login', 'wp-front-end-profile'),
        );
        // Parse current settings with defaults.
        extract(wp_parse_args((array) $instance, $defaults)); ?>
        <?php // Widget Title.
            $field_id ='' ;
        if (! empty($this->get_field_id('title'))) {
            $field_id = $this->get_field_id('title');
        }
        $field_name ='' ;
        if (! empty($this->get_field_name('title'))) {
            $field_name = $this->get_field_name('title');
        }
        $instance_title ='' ;
        if (! empty($instance['title'])) {
            $instance_title = $instance['title'];
        } ?>
            <p>
            <label for="<?php echo $field_id; ?>"><?php _e('Title:', 'wp-front-end-profile'); ?></label>
            <input id="<?php echo $field_id; ?>" class="widefat" type="text" name="<?php echo $field_name; ?>" value="<?php echo $instance_title; ?>" style="width:100%;" />
        </p>
        <?php
    }
    /**
     * update widget title.
     *
     * @param array $new_instance  return new title.
     * @param array $old_instance return old title.
     *
     * @return array
     */
    public function update($new_instance, $old_instance)
    {
        $instance['title'] = strip_tags($new_instance['title']);
        do_action('wpfep_login_widget_update_action', $new_instance, $old_instance);
        return $instance;
    }
    /**
     * Widget shortcode
     *
     * @param array $args.
     * @param array $instance.
     */
    public function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('wpfep_login_widget_title', (isset($instance['title']) ? $instance['title'] : ''));
        echo $before_widget;
        if (! empty($title)) {
            echo $before_title . $title . $after_title;
        }
        echo do_shortcode('[wpfep-login display="false"]');
        do_action('wpfep_login_widget_display', $args, $instance);
        echo $after_widget;
    }
}
// Register the widget
function wpfep_login_Widget()
{
    register_widget('wpfep_login_Widget');
}
add_action('widgets_init', 'wpfep_login_Widget');
