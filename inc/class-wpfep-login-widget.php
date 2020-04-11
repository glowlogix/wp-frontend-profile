<?php
/**
 * Login Widget.
 */
// Main constructor.
// The widget class.
class wpfep_login_Widget extends WP_Widget
{
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

		<?php // Widget Title.?>
			<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'wp-front-end-profile'); ?></label>
			<input id="<?php echo $this->get_field_id('title'); ?>" class="widefat" type="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<?php
    }
    // Update widget settings.
    public function update($new_instance, $old_instance)
    {
        $instance['title'] = strip_tags($new_instance['title']);
        do_action('wpfep_login_widget_update_action', $new_instance, $old_instance);
        return $instance;
    }
    // Display the widget.
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
