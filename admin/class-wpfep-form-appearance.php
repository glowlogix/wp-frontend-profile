<?php

defined('ABSPATH') || exit;

class WPFEP_Form_Appearance {

    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_init', [$this, 'register']);
        add_action('admin_enqueue_scripts', [$this, 'scripts']);
    }

    public function menu() {

        add_submenu_page(
            'wpfep-settings_dashboard',
            'Form Background',
            'Form Background',
            'manage_options',
            'wpfep-form-bg',
            [$this, 'page']
        );
    }

    public function register() {
        register_setting(
            'wpfep_form_bg_group',
            'wpfep_form_background',
            [
                'sanitize_callback' => [$this, 'sanitize_settings'],
            ]
        );
    }

    public function sanitize_settings($input) {
        $output = [];

        $output['enable'] = (isset($input['enable']) && $input['enable'] === 'on') ? 'on' : 'off';
        $output['type'] = in_array($input['type'] ?? '', ['image', 'color', 'gradient'], true) ? $input['type'] : 'image';
        
        // Sanitize value based on type
        if ($output['type'] === 'image') {
            $output['value'] = isset($input['value']) ? esc_url_raw($input['value']) : '';
        } else {
            // For color and gradient, don't escape URLs, just sanitize text
            $output['value'] = isset($input['value']) ? sanitize_text_field($input['value']) : '';
        }
        
        $output['blur'] = isset($input['blur']) ? max(0, min(10, intval($input['blur']))) : 0;
        $output['opacity'] = isset($input['opacity']) ? max(0, min(1, floatval($input['opacity']))) : 0.3;
        
        $pages = isset($input['pages']) && is_array($input['pages']) ? $input['pages'] : [];
        $output['pages'] = array_filter($pages, function ($page) {
            return in_array($page, ['login', 'register', 'lostpass', 'resetpass'], true);
        });

        return $output;
    }

    /**
     * Admin scripts
     */
    public function scripts($hook) {

        if (false === strpos($hook, 'wpfep-form-bg')) {
            return;
        }

        wp_enqueue_media();

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }

    public function page() {

    $data = get_option('wpfep_form_background', []);
    ?>

    <div class="wrap">
        <h1>Form Background Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('wpfep_form_bg_group'); ?>

            <table class="form-table">

                <!-- ENABLE -->
                <tr>
                    <th>Enable</th>
                    <td>
                        <select name="wpfep_form_background[enable]" id="bg-enable">
                            <option value="off" <?php selected($data['enable'] ?? '', 'off'); ?>>Off</option>
                            <option value="on" <?php selected($data['enable'] ?? '', 'on'); ?>>On</option>
                        </select>
                    </td>
                </tr>

                <!-- PRESET -->
                <tr>
                    <th>Preset</th>
                    <td>
                        <select id="bg-preset">
                            <option value="">Select Preset</option>
                            <option value="light">Light Gradient</option>
                            <option value="dark">Dark Gradient</option>
                            <option value="minimal">Minimal Gradient</option>
                            <option value="sunset">Sunset</option>
                            <option value="ocean">Ocean</option>
                            <option value="purple">Purple Dream</option>
                            <option value="mint">Mint</option>
                            <option value="warm">Warm Glow</option>
                        </select>
                        <p class="description">Choose a ready-made gradient sample. The value field will update automatically.</p>
                    </td>
                </tr>

                <!-- TYPE -->
                <tr>
                    <th>Type</th>
                    <td>
                        <select id="bg-type" name="wpfep_form_background[type]">
                            <option value="image" <?php selected($data['type'] ?? '', 'image'); ?>>Image</option>
                            <option value="color" <?php selected($data['type'] ?? '', 'color'); ?>>Color</option>
                            <option value="gradient" <?php selected($data['type'] ?? '', 'gradient'); ?>>Gradient</option>
                        </select>
                    </td>
                </tr>

                <!-- VALUE -->
                <tr>
                    <th>Value</th>
                    <td>
                        <input type="text" id="bg-value"
                               name="wpfep_form_background[value]"
                               value="<?php echo esc_attr($data['value'] ?? ''); ?>"
                               class="regular-text">

                        <button type="button" class="button" id="upload-bg-image">Upload Image</button>

                        <p>Image URL / Color / Gradient CSS</p>
                    </td>
                </tr>

                <!-- BLUR -->
                <tr>
                    <th>Blur</th>
                    <td>
                        <input type="range" id="bg-blur" name="wpfep_form_background[blur]" min="0" max="10" value="<?php echo esc_attr($data['blur'] ?? '0'); ?>">
                        <span id="blur-value"><?php echo esc_attr($data['blur'] ?? '0'); ?>px</span>
                    </td>
                </tr>

                <!-- OPACITY -->
                <tr>
                    <th>Overlay Opacity</th>
                    <td>
                        <input type="range" id="bg-overlay-opacity" name="wpfep_form_background[opacity]" min="0" max="1" step="0.1" value="<?php echo esc_attr($data['opacity'] ?? '0.3'); ?>">
                        <span id="opacity-value"><?php echo esc_attr($data['opacity'] ?? '0.3'); ?></span>
                    </td>
                </tr>

                <!-- PAGES -->
                <tr>
                    <th>Pages</th>
                    <td>
                        <?php $pages = $data['pages'] ?? []; ?>

                        <label><input type="checkbox" name="wpfep_form_background[pages][]" value="login" <?php checked(in_array('login', $pages)); ?>> Login</label><br>

                        <label><input type="checkbox" name="wpfep_form_background[pages][]" value="register" <?php checked(in_array('register', $pages)); ?>> Register</label><br>

                        <label><input type="checkbox" name="wpfep_form_background[pages][]" value="lostpass" <?php checked(in_array('lostpass', $pages)); ?>> Lost Password</label><br>

                        <label><input type="checkbox" name="wpfep_form_background[pages][]" value="resetpass" <?php checked(in_array('resetpass', $pages)); ?>> Reset Password</label>
                    </td>
                </tr>

                <!-- LIVE PREVIEW -->
                <tr>
                    <th>Live Preview</th>
                    <td>
                        <div id="bg-preview" style="height:200px;border:1px solid #ccc;"></div>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>

        <script>
            jQuery(function($){

                function updatePreview() {

                    let type = $('#bg-type').val();
                    let value = $('#bg-value').val();
                    let blur = $('#bg-blur').val();
                    let opacity = $('#bg-overlay-opacity').val();

                    let bgStyle = {};

                    if(type === 'image' && value){
                        bgStyle['background-image'] = `url('${value}')`;
                        bgStyle['background-size'] = 'cover';
                        bgStyle['background-position'] = 'center';
                        bgStyle['background-attachment'] = 'fixed';
                    }

                    if(type === 'color' && value){
                        bgStyle['background'] = value;
                    }

                    if(type === 'gradient' && value){
                        bgStyle['background'] = value;
                    }

                    bgStyle['filter'] = 'blur(' + blur + 'px)';
                    bgStyle['position'] = 'relative';
                    bgStyle['background-color'] = 'rgba(0, 0, 0, ' + opacity + ')';

                    $('#bg-preview').css(bgStyle);

                    $('#bg-preview').find('.preview-form').remove();
                    $('#bg-preview').append('<div class="preview-form"></div>');
                    $('#bg-preview .preview-form').css({
                        'width': '80%',
                        'max-width': '500px',
                        'margin': 'auto',
                        'height': '200px',
                        'background': 'rgba(255, 255, 255, 0.95)',
                        'border-radius': '10px',
                        'box-shadow': '0 10px 30px rgba(0, 0, 0, 0.2)'
                    });

                    $('#blur-value').text(blur + 'px');
                    $('#opacity-value').text(opacity);
                }

                $('#bg-type, #bg-value, #bg-blur, #bg-overlay-opacity').on('input change', updatePreview);

                updatePreview();

                // PRESETS
                $('#bg-preset').on('change', function(){

                    let val = $(this).val();

                    if(val === 'dark'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)');
                    }

                    if(val === 'light'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #ffffff 0%, #d6e7ff 100%)');
                    }

                    if(val === 'minimal'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #f8f8f8 0%, #e8e8e8 100%)');
                    }

                    if(val === 'sunset'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%)');
                    }

                    if(val === 'ocean'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #2b5876 0%, #4e4376 100%)');
                    }

                    if(val === 'purple'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)');
                    }

                    if(val === 'mint'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)');
                    }

                    if(val === 'warm'){
                        $('#bg-type').val('gradient');
                        $('#bg-value').val('linear-gradient(135deg, #f6d365 0%, #fda085 100%)');
                    }

                    if(val !== ''){
                        updatePreview();
                    }
                });

                // UPLOAD IMAGE
                $('#upload-bg-image').on('click', function(e) {
                    e.preventDefault();
                    var mediaUploader = wp.media({
                        title: 'Choose Background Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#bg-value').val(attachment.url);
                        updatePreview();
                    });
                    mediaUploader.open();
                });

            });
    </script>

        <?php
    }
}