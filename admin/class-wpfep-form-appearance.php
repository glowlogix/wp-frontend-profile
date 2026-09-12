<?php

if (! defined('ABSPATH')) {
    return;
}

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
        
        $output['blur'] = isset($input['blur']) ? max(0, min(10, (int) $input['blur'])) : 0;
        $output['opacity'] = isset($input['opacity']) ? max(0, min(1, floatval($input['opacity']))) : 0.3;
        
        $pages = isset($input['pages']) && is_array($input['pages']) ? $input['pages'] : [];
        $output['pages'] = [];

        foreach ($pages as $page) {
            if (in_array($page, ['login', 'register', 'lostpass', 'resetpass'], true)) {
                $output['pages'][] = $page;
            }
        }

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

                <!-- GRADIENT PRESET -->
                <tr>
                    <th>Gradient Preset</th>
                    <td>
                        <select id="bg-preset">
                            <option value="">Select Gradient</option>
                            <option value="light">Light Gradient</option>
                            <option value="dark">Dark Gradient</option>
                            <option value="minimal">Minimal Gradient</option>
                            <option value="sunset">Sunset</option>
                            <option value="ocean">Ocean</option>
                            <option value="purple">Purple Dream</option>
                            <option value="mint">Mint</option>
                            <option value="warm">Warm Glow</option>
                        </select>
                        <p class="description">Choose a ready-made gradient. The value field will update automatically.</p>
                    </td>
                </tr>

                <!-- WALLPAPER PRESET -->
                <tr>
                    <th>Wallpaper</th>
                    <td>
                        <select id="bg-wallpaper">
                            <option value="">Select Wallpaper</option>
                            <option value="mountains">Mountains</option>
                            <option value="city">City</option>
                            <option value="abstract">Abstract</option>
                            <option value="workspace">Modern Workspace</option>
                            <option value="studio">Clean Studio</option>
                            <option value="architecture">Glass Architecture</option>
                            <option value="skyline">Business Skyline</option>
                            <option value="forest">Calm Forest</option>
                            <option value="coastal">Coastal Blue</option>
                            <option value="marble">Soft Marble</option>
                            <option value="neutral_abstract">Neutral Abstract</option>
                            <option value="executive_office">Executive Office</option>
                            <option value="conference">Conference Room</option>
                            <option value="minimal_desk">Minimal Desk</option>
                            <option value="creative_workspace">Creative Workspace</option>
                            <option value="corporate_lobby">Corporate Lobby</option>
                            <option value="dark_workspace">Dark Workspace</option>
                            <option value="glass_tower">Glass Tower</option>
                            <option value="soft_gradient_wall">Soft Gradient Wall</option>
                            <option value="premium_lounge">Premium Lounge</option>
                            <option value="calm_abstract">Calm Abstract</option>
                        </select>
                        <p class="description">Choose a wallpaper background. The image URL field will update automatically.</p>
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
                               value="<?php esc_attr_e($data['value'] ?? ''); ?>"
                               class="regular-text">

                        <button type="button" class="button" id="upload-bg-image">Upload Image</button>

                        <p>Image URL / Color / Gradient CSS</p>
                    </td>
                </tr>

                <!-- BLUR -->
                <tr>
                    <th>Blur</th>
                    <td>
                        <input type="range" id="bg-blur" name="wpfep_form_background[blur]" min="0" max="10" value="<?php esc_attr_e($data['blur'] ?? '0'); ?>">
                        <span id="blur-value"><?php esc_html_e($data['blur'] ?? '0'); ?>px</span>
                    </td>
                </tr>

                <!-- OPACITY -->
                <tr>
                    <th>Overlay Opacity</th>
                    <td>
                        <input type="range" id="bg-overlay-opacity" name="wpfep_form_background[opacity]" min="0" max="1" step="0.1" value="<?php esc_attr_e($data['opacity'] ?? '0.3'); ?>">
                        <span id="opacity-value"><?php esc_html_e($data['opacity'] ?? '0.3'); ?></span>
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
                        <div id="bg-preview" style="height:350px;border:1px solid #ccc;"></div>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>

        <script>
            jQuery(function($){

                const presetValues = {
                    dark: { type: 'gradient', value: 'linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%)' },
                    light: { type: 'gradient', value: 'linear-gradient(135deg, #ffffff 0%, #d6e7ff 100%)' },
                    minimal: { type: 'gradient', value: 'linear-gradient(135deg, #f8f8f8 0%, #e8e7e8 100%)' },
                    sunset: { type: 'gradient', value: 'linear-gradient(135deg, #ff7e5f 0%, #feb47b 100%)' },
                    ocean: { type: 'gradient', value: 'linear-gradient(135deg, #2b5876 0%, #4e4376 100%)' },
                    purple: { type: 'gradient', value: 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)' },
                    mint: { type: 'gradient', value: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)' },
                    warm: { type: 'gradient', value: 'linear-gradient(135deg, #f6d365 0%, #fda085 100%)' }
                };

                const wallpaperValues = {
                    mountains: { type: 'image', value: 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1600&q=80' },
                    city: { type: 'image', value: 'https://images.unsplash.com/photo-1467269204594-9661b134dd2b?auto=format&fit=crop&w=1600&q=80' },
                    abstract: { type: 'image', value: 'https://images.unsplash.com/photo-1503264116251-35a269479413?auto=format&fit=crop&w=1600&q=80' },
                    workspace: { type: 'image', value: 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80' },
                    studio: { type: 'image', value: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80' },
                    architecture: { type: 'image', value: 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=1600&q=80' },
                    skyline: { type: 'image', value: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1600&q=80' },
                    forest: { type: 'image', value: 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1600&q=80' },
                    coastal: { type: 'image', value: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80' },
                    marble: { type: 'image', value: 'https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?auto=format&fit=crop&w=1600&q=80' },
                    neutral_abstract: { type: 'image', value: 'https://images.unsplash.com/photo-1557683316-973673baf926?auto=format&fit=crop&w=1600&q=80' },
                    executive_office: { type: 'image', value: 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1600&q=80' },
                    conference: { type: 'image', value: 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=1600&q=80' },
                    minimal_desk: { type: 'image', value: 'https://images.unsplash.com/photo-1497215842964-222b430dc094?auto=format&fit=crop&w=1600&q=80' },
                    creative_workspace: { type: 'image', value: 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=1600&q=80' },
                    corporate_lobby: { type: 'image', value: 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1600&q=80' },
                    dark_workspace: { type: 'image', value: 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1600&q=80' },
                    glass_tower: { type: 'image', value: 'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1600&q=80' },
                    soft_gradient_wall: { type: 'image', value: 'https://images.unsplash.com/photo-1557682250-33bd709cbe85?auto=format&fit=crop&w=1600&q=80' },
                    premium_lounge: { type: 'image', value: 'https://images.unsplash.com/photo-1511818966892-d7d671e672a2?auto=format&fit=crop&w=1600&q=80' },
                    calm_abstract: { type: 'image', value: 'https://images.unsplash.com/photo-1557682224-5b8590cd9ec5?auto=format&fit=crop&w=1600&q=80' }
                };

                function setPresetFromValue() {
                    const value = $('#bg-value').val().trim();
                    if ($('#bg-type').val() !== 'gradient') {
                        $('#bg-preset').val('');
                        return;
                    }
                    let found = '';
                    $.each(presetValues, function(key, preset) {
                        if (preset.value === value) {
                            found = key;
                            return false;
                        }
                    });
                    $('#bg-preset').val(found);
                }

                function setWallpaperFromValue() {
                    const value = $('#bg-value').val().trim();
                    if ($('#bg-type').val() !== 'image') {
                        $('#bg-wallpaper').val('');
                        return;
                    }
                    let found = '';
                    $.each(wallpaperValues, function(key, preset) {
                        if (preset.value === value) {
                            found = key;
                            return false;
                        }
                    });
                    $('#bg-wallpaper').val(found);
                }

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
                    setPresetFromValue();
                    setWallpaperFromValue();
                }

                $('#bg-type, #bg-value, #bg-blur, #bg-overlay-opacity').on('input change', function() {
                    updatePreview();
                });

                updatePreview();
                setPresetFromValue();
                setWallpaperFromValue();

                // PRESETS
                $('#bg-preset').on('change', function(){
                    let key = $(this).val();
                    if (!key) return;
                    let preset = presetValues[key];
                    if (!preset) return;
                    $('#bg-type').val(preset.type);
                    $('#bg-value').val(preset.value);
                    $('#bg-wallpaper').val('');
                    updatePreview();
                });

                // WALLPAPERS
                $('#bg-wallpaper').on('change', function(){
                    let key = $(this).val();
                    if (!key) return;
                    let preset = wallpaperValues[key];
                    if (!preset) return;
                    $('#bg-type').val(preset.type);
                    $('#bg-value').val(preset.value);
                    $('#bg-preset').val('');
                    updatePreview();
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
