<?php
/**
 * API settings.
 */
defined('ABSPATH') || exit;

if (!class_exists('WPFEP_Settings_API')) {
    /**
     * Settings API wrapper class.
     */
    class WPFEP_Settings_API
    {
        /**
         * Settings sections array.
         *
         * @var array
         */
        protected $settings_sections = [];

        /**
         * Settings fields array.
         *
         * @var array
         */
        protected $settings_fields = [];

        /**
         * WPFEP_Settings_API constructor.
         */
        public function __construct()
        {
            add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        }

        /**
         * Enqueue scripts and styles.
         */
        public function admin_enqueue_scripts()
        {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('wpfep_admin_styles', plugins_url('/assets/css/wpfep-admin-style.css', dirname(__FILE__)), [], WPFEP_VERSION, 'all');

            wp_enqueue_media();
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script('jquery-ui-tooltip');
            wp_enqueue_script('wpfep_admin', plugins_url('/assets/js/admin.js', dirname(__FILE__)), ['jquery'], WPFEP_VERSION, false);
            wp_enqueue_script('jquery');
        }

        /**
         * Set settings sections.
         *
         * @param array $sections sections value.
         */
        public function set_sections($sections)
        {
            $this->settings_sections = $sections;

            return $this;
        }

        /**
         * Add a single section.
         *
         * @param array $section return sections.
         */
        public function add_section($section)
        {
            $this->settings_sections[] = $section;

            return $this;
        }

        /**
         * Set settings fields.
         *
         * @param array $fields settings fields array.
         */
        public function set_fields($fields)
        {
            $this->settings_fields = $fields;

            return $this;
        }

        /**
         * Set settings fields.
         *
         * @param array $section settings.
         * @param array $field   fields array.
         */
        public function add_field($section, $field)
        {
            $defaults = [
                'name'  => '',
                'label' => '',
                'desc'  => '',
                'type'  => 'text',
            ];

            $arg = wp_parse_args($field, $defaults);
            $this->settings_fields[$section][] = $arg;

            return $this;
        }

        /**
         * Initialize and registers the settings sections and fields to WordPress.
         *
         * Usually this should be called at `admin_init` hook.
         *
         * This function gets the initiated settings sections and fields. Then
         * registers them to WordPress and ready for use.
         */
        public function admin_init()
        {
            // register settings sections.
            foreach ($this->settings_sections as $section) {
                if (false === get_option($section['id'])) {
                    add_option($section['id']);
                }

                if (isset($section['desc']) && !empty($section['desc'])) {
                    $section['desc'] = '<div class="inside">'.$section['desc'].'</div>';
                    $callback = function () {
                        echo esc_html(str_replace('"', '\"', $section['desc']));
                    };
                } elseif (isset($section['callback'])) {
                    $callback = $section['callback'];
                } else {
                    $callback = null;
                }

                add_settings_section($section['id'], $section['title'], $callback, $section['id']);
            }

            // register settings fields.
            foreach ($this->settings_fields as $section => $field) {
                foreach ($field as $option) {
                    $type = isset($option['type']) ? $option['type'] : 'text';

                    $args = [
                        'id'                => $option['name'],
                        'class'             => isset($option['class']) ? $option['class'] : '',
                        'label_for'         => $args['label_for'] = "{$section}[{$option['name']}]",
                        'desc'              => isset($option['desc']) ? $option['desc'] : '',
                        'name'              => $option['label'],
                        'section'           => $section,
                        'size'              => isset($option['size']) ? $option['size'] : null,
                        'options'           => isset($option['options']) ? $option['options'] : '',
                        'std'               => isset($option['default']) ? $option['default'] : '',
                        'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
                        'type'              => $type,
                        'placeholder'       => isset($option['placeholder']) ? $option['placeholder'] : '',
                        'min'               => isset($option['min']) ? $option['min'] : '',
                        'max'               => isset($option['max']) ? $option['max'] : '',
                        'step'              => isset($option['step']) ? $option['step'] : '',
                    ];

                    add_settings_field($section.'['.$option['name'].']', $option['label'], (isset($option['callback']) ? $option['callback'] : [$this, 'callback_'.$type]), $section, $section, $args);
                }
            }

            // Creates our settings in the options table.
            foreach ($this->settings_sections as $section) {
                register_setting($section['id'], $section['id'], [$this, 'sanitize_options']);
            }
        }

        /**
         * Get field description for display.
         *
         * @param array $args settings field args.
         */
        public function get_field_description($args)
        {
            if (!empty($args['desc'])) {
                $desc = sprintf('<p class="description">%s</p>', $args['desc']);
            } else {
                $desc = '';
            }

            return $desc;
        }

        /**
         * Get field tooltip for display.
         *
         * @param array $args settings field args.
         */
        public function get_field_tooltip($args)
        {
            if (!empty($args['desc'])) {
                $desc = sprintf('<span class="wpfep-help-tip dashicons dashicons-editor-help" title="%s"></span>', $args['desc']);
            } else {
                $desc = '';
            }

            return $desc;
        }

        /**
         * Displays a text field for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_text($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $type = isset($args['type']) ? $args['type'] : 'text';
            $placeholder = empty($args['placeholder']) ? '' : ' placeholder="'.$args['placeholder'].'"';

            $html = sprintf('<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder);
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'type'  => [],
                        'class' => [],
                        'id'    => [],
                        'name'  => [],
                        'value' => [],
                    ],
                    'p'     => [],
                    'a'     => [
                        'target' => [],
                        'href'   => [],
                    ],

                ]
            );
        }

        /**
         * Displays a url field for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_url($args)
        {
            $this->callback_text($args);
        }

        /**
         * Displays a number field for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_number($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $type = isset($args['type']) ? $args['type'] : 'number';
            $placeholder = empty($args['placeholder']) ? '' : ' placeholder="'.$args['placeholder'].'"';
            $min = empty($args['min']) ? '' : ' min="'.$args['min'].'"';
            $max = empty($args['max']) ? '' : ' max="'.$args['max'].'"';
            $step = empty($args['max']) ? '' : ' step="'.$args['step'].'"';

            $html = sprintf('<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step);
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'type'  => [],
                        'class' => [],
                        'id'    => [],
                        'name'  => [],
                        'value' => [],
                    ],
                ]
            );
        }

        /**
         * Displays a checkbox for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_checkbox($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

            $html = '<fieldset>';
            $html .= sprintf('<label for="%1$s[%2$s]">', $args['section'], $args['id']);
            $html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
            $html .= sprintf('<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked($value, 'on', false));
            $html .= sprintf('%1$s</label>', $args['desc']);
            $html .= '</fieldset>';

            echo wp_kses(
                $html,
                [
                    'fieldset' => [],
                    'label'    => [
                        'for' => [],
                    ],
                    'input'    => [
                        'type'  => [],
                        'name'  => [],
                        'value' => [],

                    ],
                    'input'    => [
                        'type'    => [],
                        'class'   => [],
                        'id'      => [],
                        'name'    => [],
                        'value'   => [],
                        'checked' => [],
                    ],

                ]
            );
        }

        /**
         * Displays a multicheckbox a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_multicheck($args)
        {
            $value = $this->get_option($args['id'], $args['section'], $args['std']);
            $value = $value ? $value : [];
            $html = '<fieldset>';
            $html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id']);
            foreach ($args['options'] as $key => $label) {
                $checked = in_array($key, $value, true) ? $key : '0';
                $html .= sprintf('<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
                $html .= sprintf('<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($checked, $key, false));
                $html .= sprintf('%1$s</label><br>', $label);
            }

            $html .= $this->get_field_description($args);
            $html .= '</fieldset>';

            echo wp_kses(
                $html,
                [
                    'fieldset' => [],
                    'input'    => [
                        'type'  => [],
                        'name'  => [],
                        'value' => [],

                    ],
                    'label'    => [
                        'for' => [],
                    ],
                    'input'    => [
                        'type'  => [],
                        'class' => [],
                        'id'    => [],
                        'name'  => [],
                        'value' => [],
                    ],

                ]
            );
        }

        /**
         * Displays a multicheckbox a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_radio($args)
        {
            $value = $this->get_option($args['id'], $args['section'], $args['std']);
            $html = '<fieldset>';

            foreach ($args['options'] as $key => $label) {
                $html .= sprintf('<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
                $html .= sprintf('<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($value, $key, false));
                $html .= sprintf('%1$s</label><br>', $label);
            }

            $html .= $this->get_field_description($args);
            $html .= '</fieldset>';

            echo wp_kses(
                $html,
                [
                    'fieldset' => [],
                    'label'    => [
                        'for' => [],
                    ],
                    'input'    => [

                        'type'  => [],
                        'class' => [],
                        'id'    => [],
                        'name'  => [],
                        'value' => [],
                    ],

                ]
            );
        }

        /**
         * Displays a selectbox for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_select($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $html = sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);
            foreach ($args['options'] as $key => $label) {
                $html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
            }

            $html .= sprintf('</select>');
            $html .= $this->get_field_description($args);
            echo wp_kses(
                $html,
                [
                    'select' => [
                        'class' => [],
                        'name'  => [],
                    ],
                    'option' => [
                        'value'    => [],
                        'selected' => [],
                    ],
                    'p'      => [
                        'href'  => [],
                        'class' => [],
                    ],

                ]
            );
        }

        /**
         * Displays a selectbox for a settings field with view and edit page button.
         *
         * @param array $args settings field args.
         */
        public function callback_select_page($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $wpfep_options = get_option('wpfep_profile');
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $html = $this->get_field_tooltip($args);
            $html .= sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);
            foreach ($args['options'] as $key => $label) {
                $html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
            }

            $html .= sprintf('</select>');
            if ('' !== $value) {
                $html .= sprintf(' <a href='.get_edit_post_link($value).' class="button"> '.__('Edit Page', 'wp-front-end-profile').'</a>');
                $html .= sprintf(' <a href='.get_permalink($value).' class="button"> '.__('View Page', 'wp-front-end-profile').'</a>');
            }

            echo wp_kses(
                $html,
                [
                        'span'   => [
                            'class' => [],
                            'title' => [],
                        ],
                        'select' => [
                            'class' => [],
                            'name'  => [],
                            'id'    => [],
                            'name'  => [],
                            'value' => [],
                        ],
                        'option' => [
                            'value'    => [],
                            'selected' => [],
                        ],
                        'a'      => [
                            'href'  => [],
                            'class' => [],
                        ],

                    ]
            );
        }

        /**
         * Displays a textarea for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_textarea($args)
        {
            $value = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $placeholder = empty($args['placeholder']) ? '' : ' placeholder="'.$args['placeholder'].'"';

            $html = sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value);
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'href'  => [],
                        'id'    => [],
                        'class' => [],
                        'name'  => [],
                        'value' => [],
                    ],

                ]
            );
        }

        /**
         * Displays a textarea for a settings field.
         *
         * @param array $args settings field args.
         *
         * @return void
         */
        public function callback_html($args)
        {
            echo esc_html($this->get_field_description($args));
        }

        /**
         * Displays a rich text textarea for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_wysiwyg($args)
        {
            $value = $this->get_option($args['id'], $args['section'], $args['std']);
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';

            echo '<div style="max-width: '.esc_html($size).';">';

            $editor_settings = [
                'teeny'         => true,
                'textarea_name' => $args['section'].'['.$args['id'].']',
                'textarea_rows' => 10,
            ];

            if (isset($args['options']) && is_array($args['options'])) {
                $editor_settings = array_merge($editor_settings, $args['options']);
            }

            wp_editor($value, $args['section'].'-'.$args['id'], $editor_settings);

            echo '</div>';

            echo esc_html($this->get_field_description($args));
        }

        /**
         * Displays a file upload field for a settings field.
         *
         * @param array $args settings field args.
         */
        public function callback_file($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
            $id = $args['section'].'['.$args['id'].']';
            $label = isset($args['options']['button_label']) ? $args['options']['button_label'] : __('Choose File', 'wp-front-end-profile');

            $html = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
            $html .= '<input type="button" class="button wpsa-browse" value="'.$label.'" />';
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'href'  => [],
                        'id'    => [],
                        'class' => [],
                        'name'  => [],
                        'value' => [],
                    ],
                    'br'    => [],

                    'input' => [
                        'class' => [],
                        'value' => [],
                    ],
                ]
            );
        }

        /**
         * Displays a password field for a settings field.
         *
         * @param array $args return arguments.
         */
        public function callback_password($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

            $html = sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'href'  => [],
                        'id'    => [],
                        'class' => [],
                        'name'  => [],
                        'value' => [],
                    ],
                ]
            );
        }

        /**
         * Displays a color picker field for a settings field.
         *
         * @param array $args return arguments.
         */
        public function callback_color($args)
        {
            $value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
            $size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

            $html = sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std']);
            $html .= $this->get_field_description($args);

            echo wp_kses(
                $html,
                [
                    'input' => [
                        'href'  => [],
                        'id'    => [],
                        'class' => [],
                        'name'  => [],
                        'value' => [],
                    ],
                ]
            );
        }

        /**
         * Sanitize callback for Settings API.
         *
         * @param array $options return options.
         *
         * @return mixed.
         */
        public function sanitize_options($options)
        {
            if (!$options) {
                return $options;
            }

            foreach ($options as $option_slug => $option_value) {
                $sanitize_callback = $this->get_sanitize_callback($option_slug);

                // If callback is set, call it.
                if ($sanitize_callback) {
                    $options[$option_slug] = call_user_func($sanitize_callback, $option_value);
                    continue;
                }
            }

            return $options;
        }

        /**
         * Get sanitization callback for given option slug.
         *
         * @param string $slug slugs of post.
         *
         * @return mixed string or bool false
         */
        public function get_sanitize_callback($slug = '')
        {
            if (empty($slug)) {
                return false;
            }

            // Iterate over registered fields and see if we can find proper callback.
            foreach ($this->settings_fields as $section => $options) {
                foreach ($options as $option) {
                    if ($option['name'] !== $slug) {
                        continue;
                    }

                    // Return the callback name.
                    return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
                }
            }

            return false;
        }

        /**
         * Get the value of a settings field.
         *
         * @param string $option  settings field name.
         * @param string $section the section name this field belongs to.
         * @param string $default default text if it's not found.
         *
         * @return string.
         */
        public function get_option($option, $section, $default = '')
        {
            $options = get_option($section);

            if (isset($options[$option])) {
                return $options[$option];
            }

            return $default;
        }

        /**
         * Show navigation as tab.
         *
         * Shows all the settings section labels as tab.
         */
        public function show_navigation()
        {
            $html = '<h2 class="nav-tab-wrapper">';

            $count = count($this->settings_sections);

            // don't show the navigation if only one section exists.
            if (1 === $count) {
                return;
            }

            foreach ($this->settings_sections as $tab) {
                $html .= sprintf('<a href="#%1$s" class="nav-tab" id="%1$s-tab"><span class="dashicons %3$s"></span> %2$s</a>', $tab['id'], $tab['title'], !empty($tab['icon']) ? $tab['icon'] : '');
            }

            $html .= '</h2>';

            echo wp_kses(
                $html,
                [
                    'a'    => [
                        'href'  => [],
                        'id'    => [],
                        'class' => [],
                    ],
                    'br'   => [],
                    'span' => [
                        'class' => [],
                    ],
                    'h2'   => [
                        'class' => [],
                    ],
                ]
            );
        }

        /**
         * Show the section settings forms.
         *
         * This function displays every sections in a different form.
         */
        public function show_forms()
        {
            ?>
			<div class="metabox-holder">
				<?php foreach ($this->settings_sections as $form) { ?>
					<div id="<?php echo esc_attr($form['id']); ?>" class="group" style="display: none;">
						<form method="post" action="options.php">
							<?php
                            do_action('wsa_form_top_'.$form['id'], $form);
                            settings_fields($form['id']);
                            do_settings_sections($form['id']);
                            do_action('wsa_form_bottom_'.$form['id'], $form);
                            if (isset($this->settings_fields[$form['id']])) {
                                ?>
								<?php submit_button(); ?>
							<?php
                            } ?>
						</form>
					</div>
				<?php } ?>
			</div>
			<?php
            $this->script();
        }

        /**
         * Tabbable JavaScript codes & Initiate Color Picker.
         *
         * This code uses localstorage for displaying active tabs.
         */
        public function script()
        {
            ?>
			<script>
				jQuery(document).ready(function($) {
					//Initiate Color Picker
					$('.wp-color-picker-field').wpColorPicker();
					$.urlParam = function (name) {
						var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.search);

						return (results !== null) ? results[1] || 0 : false;
					}

					var wpfep_page_installed = $.urlParam('wpfep_page_installed'); //wpfep_page_installed
					// Switches option sections
					$('.group').hide();
					var activetab = '';
					if (typeof(localStorage) !== 'undefined' ) {
						activetab = localStorage.getItem("activetab");
					}
					if (wpfep_page_installed == 1) {
						$('#wpfep_pages').fadeIn();
					}
					else if (activetab !== '' && $(activetab).length ) {
						$(activetab).fadeIn();
					} else {
						$('.group:first').fadeIn();
					}
					$('.group .collapsed').each(function(){
						$(this).find('input:checked').parent().parent().parent().nextAll().each(
						function(){
							if ($(this).hasClass('last')) {
								$(this).removeClass('hidden');
								return false;
							}
							$(this).filter('.hidden').removeClass('hidden');
						});
					});
					if (wpfep_page_installed == 1) {
						$('#wpfep_pages-tab').addClass('nav-tab-active');
					}
					else if (activetab !== '' && $(activetab + '-tab').length ) {
						$(activetab + '-tab').addClass('nav-tab-active');
					}
					else {
						$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
					}
					$('.nav-tab-wrapper a').click(function(evt) {
						$('.nav-tab-wrapper a').removeClass('nav-tab-active');
						$(this).addClass('nav-tab-active').blur();
						var clicked_group = $(this).attr('href');
						if (typeof(localStorage) !== 'undefined' ) {
							localStorage.setItem("activetab", $(this).attr('href'));
						}
						$('.group').hide();
						$(clicked_group).fadeIn();
						evt.preventDefault();
					});
			});
			</script>
			<?php
        }
    }
}
