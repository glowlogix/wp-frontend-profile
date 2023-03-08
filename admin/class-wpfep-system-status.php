<?php
/**
 * @package wp-front-end-profile
 * System status.
 */

defined('ABSPATH') || exit;

if (! class_exists('Wpfep_System_Status')) {
    /**
     * Wpfep status functions.
     *
     * All Wpfep status related functions can be found here.
     *
     * @since      1.0.0
     */
    class Wpfep_System_Status
    {

        /**
         * Display Error message.
         *
         * @param string $message get message.
         * @param string $class   get class.
         *
         * @since 1.0.0
         */
        public function wpfep_status_wrap_error_message($message, $class)
        {
            ob_start(); ?>
			<div class="notice inline notice-<?php esc_attr_e('$class', 'wpfep'); ?> notice-alt">
				<p><?php echo esc_html($message); ?></p>
			</div>
			<?php
            $html = ob_get_clean();

            return $html;
        }

        /**
         * Display system status report.
         *
         * @since 1.0.0
         */
        public static function status_report()
        {
            global $wpdb;

            $environment    = self::wpfep_get_environment_info();
            $database       = self::wpfep_get_database_info();
            $active_plugins = self::wpfep_get_active_plugins();
            $theme          = self::wpfep_get_theme_info();
            $security       = self::wpfep_get_security_info(); ?>
			<style type="text/css"></style>
			<div class="wrap">
				<h2><?php esc_attr_e('System Status', 'wpfep'); ?></h2>
				<div class="notice notice_system_status_wpfep">
					<p><?php esc_attr_e('Please copy and paste this information in your ticket when contacting support:', 'wpfep'); ?> </p>
					<p class="submit"><a href="javascript:void" class="button-primary debug-report"><?php esc_attr_e('Get system report', 'wpfep'); ?></a></p>
					<div id="debug-report">
						<textarea readonly="readonly"></textarea>
						<p class="submit"><button id="copy-for-system-support" class="button-primary" href="javascript:void"><?php esc_attr_e('Copy for Support', 'wpfep'); ?></button></p>
						<p class="copy-error hidden"><?php esc_attr_e('Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'wpfep'); ?></p>
					</div>
				</div>
				<table class="wpfep-status-table widefat">
					<thead>
					<tr>
						<th colspan="3" data-export-label="WordPress Environment"><h2><?php esc_attr_e('WordPress Environment', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('Home URL', 'wpfep'); ?>"><?php esc_attr_e('Home URL', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['home_url']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Site URL', 'wpfep'); ?>"><?php esc_attr_e('Site URL', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['site_url']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('WP Frontend Profile Version', 'wpfep'); ?>"><?php esc_attr_e('WP Frontend Profile Version', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['version']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('WP version', 'wpfep'); ?>"><?php esc_attr_e('WP version', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['wp_version']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('WP Multisite', 'wpfep'); ?>"><?php esc_attr_e('WP multisite', 'wpfep'); ?>:</td>
						<td><?php echo ($environment['wp_multisite']) ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('WP Memory Limit', 'wpfep'); ?>"><?php esc_attr_e('WP memory limit', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['wp_memory_limit'] < 67108864) {
                            /* translators: %1s: WordPress environment */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html__('%1$s - We recommend setting memory to at least 64MB. See: %2$s', 'wpfep'), esc_html(size_format($environment['wp_memory_limit'])), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . esc_html__('Increasing memory allocated to PHP', 'wpfep') . '</a>') . '</mark>';
                        } else {
                            echo '<mark class="yes">' . esc_html(size_format($environment['wp_memory_limit'])) . '</mark>';
                        } ?>
							</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('WP Debug Mode', 'wpfep'); ?>"><?php esc_attr_e('WP debug mode', 'wp-front-end-profilep'); ?>:</td>
						<td>
							<?php if ($environment['wp_debug_mode']) { ?>
								<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
							<?php } else { ?>
								<mark class="no">&ndash;</mark>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Wp Cron', 'wpfep'); ?>"><?php esc_attr_e('WP cron', 'wpfep'); ?>:</td>
						<td>
							<?php if ($environment['wp_cron']) { ?>
								<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
							<?php } else { ?>
								<mark class="no">&ndash;</mark>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Language', 'wpfep'); ?>"><?php esc_attr_e('Language', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['language']); ?></td>
					</tr>
					</tbody>
				</table>

				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="Server Environment"><h2><?php esc_attr_e('Server Environment', 'wp-front-end-profilep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('Server Info', 'wpfep'); ?>"><?php esc_attr_e('Server info', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['server_info']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('PHP Version', 'wpfep'); ?>"><?php esc_attr_e('PHP version', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if (version_compare($environment['php_version'], '5.6', '<')) {
                            /* translators: %1$s: php version */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('%1$s - We recommend a minimum PHP version of 5.6.', 'wpfep'), esc_html($environment['php_version'])) . '</mark>';
                        } else {
                            echo '<mark class="yes">' . esc_html($environment['php_version']) . '</mark>';
                        } ?>
							</td>
					</tr>
					<?php if (function_exists('ini_get')) { ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('PHP Post Max Size', 'wpfep'); ?>"><?php esc_attr_e('PHP post max size', 'wpfep'); ?>:</td>
							<td><?php echo esc_html(size_format($environment['php_post_max_size'])); ?></td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('PHP Time Limit', 'wpfep'); ?>"><?php esc_attr_e('PHP time limit', 'wpfep'); ?>:</td>
							<td><?php echo esc_html($environment['php_max_execution_time']); ?></td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('PHP Max Input Vars', 'wpfep'); ?>"><?php esc_attr_e('PHP max input vars', 'wpfep'); ?>:</td>
							<td><?php echo esc_html($environment['php_max_input_vars']); ?></td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('cURL Version', 'wpfep'); ?>"><?php esc_attr_e('cURL version', 'wpfep'); ?>:</td>
							<td><?php echo esc_html($environment['curl_version']); ?></td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('SUHOSIN Installed', 'wpfep'); ?>"><?php esc_attr_e('SUHOSIN installed', 'wpfep'); ?>:</td>
							<td><?php echo $environment['suhosin_installed'] ? '<span class="dashicons dashicons-yes"></span>' : '&ndash;'; ?></td>
						</tr>
						<?php
                    }
            global $wpdb;
            if ($wpdb->use_mysqli) {
                $ver = $wpdb->db_version();
            } else {
                $ver = $wpdb->db_version();
            }
            if (! empty($wpdb->is_mysql) && ! stristr($ver, 'MariaDB')) {
                ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('MySQL Version', 'wpfep'); ?>"><?php esc_attr_e('MySQL version', 'wpfep'); ?>:</td>
							<td>
								<?php
                                if (version_compare($environment['mysql_version'], '5.6', '<')) {
                                    echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('%1$s - We recommend a minimum MySQL version of 5.6. See: %2$s', 'wpfep'), esc_html($environment['mysql_version']), '<a href="https://wordpress.org/about/requirements/" target="_blank">' . esc_html('WordPress requirements', 'wpfep') . '</a>') . '</mark>';
                                } else {
                                    echo '<mark class="yes">' . esc_html($environment['mysql_version']) . '</mark>';
                                } ?>
							</td>
						</tr>
							<?php
            } ?>
					<tr>
						<td data-export-label="<?php esc_attr_e('Max Upload Size', 'wpfep'); ?>"><?php esc_attr_e('Max upload size', 'wpfep'); ?>:</td>
						<td><?php echo esc_html(size_format($environment['max_upload_size'])); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Default Timezone is UTC', 'wpfep'); ?>"><?php esc_attr_e('Default timezone is UTC', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ('UTC' !== $environment['default_timezone']) {
                            /* translators: %s: default timezone */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_attr_e('Default timezone is %s - it should be UTC', 'wpfep'), esc_html($environment['default_timezone'])) . '</mark>';
                        } else {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('fsockopen/cURL', 'wp-front-end-profilep'); ?>"><?php esc_attr_e('fsockopen/cURL', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['fsockopen_or_curl_enabled']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . esc_html_e('Your server does not have fsockopen or cURL enabled - PayPal IPN and other scripts which communicate with other servers will not work. Contact your hosting provider.', 'wpfep') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('SoapClient', 'wpfep'); ?>"><?php esc_attr_e('SoapClient', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['soapclient_enabled']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_attr_e('Your server does not have the %s class enabled - some gateway plugins which use SOAP may not work as expected.', 'wpfep'), '<a href="https://php.net/manual/en/class.soapclient.php">SoapClient</a>') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('DOMDocument', 'wpfep'); ?>"><?php esc_attr_e('DOMDocument', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['domdocument_enabled']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('Your server does not have the %s class enabled - HTML/Multipart emails, and also some extensions, will not work without DOMDocument.', 'wpfep'), '<a href="https://php.net/manual/en/class.domdocument.php">DOMDocument</a>') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('GZip', 'wpfep'); ?>"><?php esc_attr_e('GZip', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['gzip_enabled']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('Your server does not support the %s function - this is required to use the GeoIP database from MaxMind.', 'wpfep'), '<a href="https://php.net/manual/en/zlib.installation.php">gzopen</a>') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('GD Library', 'wpfep'); ?>"><?php esc_attr_e('GD Library', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['gd_library']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('Your server does not have enabled %s - this is required for image processing.', 'wpfep'), '<a href="https://secure.php.net/manual/en/image.installation.php">GD Library</a>') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Multibyte String', 'wp-front-end-profilep'); ?>"><?php esc_attr_e('Multibyte string', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['mbstring_enabled']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('Your server does not support the %s functions - this is required for better character encoding. Some fallbacks will be used instead for it.', 'wpfep'), '<a href="https://php.net/manual/en/mbstring.installation.php">mbstring</a>') . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Remote POST', 'wpfep'); ?>"><?php esc_attr_e('Remote POST', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['remote_post_successful']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html('%s failed. Contact your hosting provider.', 'wpfep'), 'wp_remote_post()') . ' ' . esc_html($environment['remote_post_response']) . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Remote GET', 'wpfep'); ?>"><?php esc_attr_e('Remote GET', 'wpfep'); ?>:</td>
						<td>
						<?php
                        if ($environment['remote_get_successful']) {
                            echo '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>';
                        } else {
                            /* translators: %s: search term */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html__('%s failed. Contact your hosting provider.', 'wpfep'), 'wp_remote_get()') . ' ' . esc_html($environment['remote_get_response']) . '</mark>';
                        } ?>
						</td>
					</tr>
					<?php
                    $rows = apply_filters('wpfep_system_status_environment_rows', array());
            if (count($rows) > 0) {
                foreach ($rows as $row) {
                    if (! empty($row['success'])) {
                        $css_class = 'yes';
                        $icon      = '<span class="dashicons dashicons-yes"></span>';
                    } else {
                        $css_class = 'error';
                        $icon      = '<span class="dashicons dashicons-no-alt"></span>';
                    } ?>
							<tr>
							<td data-export-label="<?php echo esc_attr($row['name']); ?>"><?php echo esc_html($row['name']); ?>
								:
							</td>
							<td>
								<mark class="<?php echo esc_attr($css_class); ?>">
									<?php
                                    echo wp_kses(
                        $icon,
                        array(
                                            'span' => array(

                                                'class' => array(),
                                            ),
                                        )
                    ); ?>
								<?php echo ! empty($row['note']) ? wp_kses_data($row['note']) : ''; ?>
								</mark>
							</td>
							</tr>
							<?php
                }
            } ?>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="<?php esc_attr_e('User Platform', 'wpfep'); ?>"><h2><?php esc_attr_e('User Platform', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('Platform', 'wpfep'); ?>"><?php esc_attr_e('Platform', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['platform']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Browser name', 'wpfep'); ?>"><?php esc_attr_e('Browser name', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['browser_name']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Browser version', 'wpfep'); ?>"><?php esc_attr_e('Browser version', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['browser_version']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('User agent', 'wpfep'); ?>"><?php esc_attr_e('User agent', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($environment['user_agent']); ?></td>
					</tr>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="<?php esc_attr_e('Database', 'wpfep'); ?>"><h2><?php esc_attr_e('Database', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('WPFEP Database Version', 'wpfep'); ?>"><?php esc_attr_e('WPFEP database version', 'wpfep'); ?>:</td>
						<td><?php echo esc_html(WPFEP_VERSION); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Database Prefix', 'wpfep'); ?>"><?php esc_attr_e('Database Prefix', 'wpfep'); ?></td>
						<td>
						<?php
                        if (strlen($database['database_prefix']) > 20) {
                            /* translators: %1s: database prefix */
                            echo '<mark class="error"><span class="dashicons dashicons-warning"></span> ' . sprintf(esc_html__('%1$s - We recommend using a prefix with less than 20 characters.', 'wpfep'), esc_html($database['database_prefix'])) . '</mark>';
                        } else {
                            echo '<mark class="yes">' . esc_html($database['database_prefix']) . '</mark>';
                        } ?>
						</td>
					</tr>
					<tr>
						<td><?php esc_attr_e('Total Database Size', 'wpfep'); ?></td>
						<td><?php printf('%.2fMB', esc_html($database['database_size']['data']) + esc_html($database['database_size']['index'])); ?></td>
					</tr>
					<tr>
						<td><?php esc_attr_e('Database Data Size', 'wpfep'); ?></td>
						<td><?php printf('%.2fMB', esc_html($database['database_size']['data'])); ?></td>
					</tr>
					<tr>
						<td><?php esc_attr_e('Database Index Size', 'wpfep'); ?></td>
						<td><?php printf('%.2fMB', esc_html($database['database_size']['index'])); ?></td>
					</tr>
					<?php foreach ($database['database_tables']['other'] as $table => $table_data) { ?>
						<tr>
							<td><?php echo esc_html($table); ?></td>
							<td>
								<?php
                                /* translators: %s: search term */
                                printf(esc_html('Data: %1$.2fMB + Index: %2$.2fMB', 'wpfep'), esc_html(wpfep_format_decimal($table_data['data'], 2)), esc_html(wpfep_format_decimal($table_data['index'], 2)));
                                ?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="Security"><h2><?php esc_attr_e('Security', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('Secure Connection (HTTPS)', 'wpfep'); ?>"><?php esc_attr_e('Secure connection (HTTPS)', 'wpfep'); ?>:</td>
						<td>
							<?php if ($security['secure_connection']) { ?>
								<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
							<?php } else { ?>
								<mark class="error"><span class="dashicons dashicons-warning"></span><?php echo esc_attr_e('Your site is not using HTTPS.', 'wpfep'); ?></mark>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Hide errors from visitors', 'wpfep'); ?>"><?php esc_attr_e('Hide errors from visitors', 'wpfep'); ?></td>
						<td>
							<?php if ($security['hide_errors']) { ?>
								<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>
							<?php } else { ?>
								<mark class="error"><span class="dashicons dashicons-warning"></span><?php esc_attr_e('Error messages should not be shown to visitors.', 'wpfep'); ?></mark>
							<?php } ?>
						</td>
					</tr>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="Active Plugins (<?php echo count($active_plugins); ?>)"><h2><?php esc_attr_e('Active Plugins', 'wpfep'); ?> (<?php echo count($active_plugins); ?>)</h2></th>
					</tr>
					</thead>
					<tbody>
					<?php
                    foreach ($active_plugins as $plugin) {
                        if (! empty($plugin['name'])) {
                            $dirname = dirname($plugin['plugin']);

                            // Link the plugin name to the plugin url if available.
                            $plugin_name = esc_html($plugin['name'], 'wpfep');
                            if (! empty($plugin['url'])) {
                                $plugin_name = '<a href="' . esc_url($plugin['url']) . '" aria-label="' . esc_attr__('Visit plugin homepage', 'wpfep') . '" target="_blank">' . esc_attr($plugin_name) . '</a>';
                            }

                            $version_string = '';
                            $network_string = '';
                            if (! empty($plugin['latest_verison']) && version_compare($plugin['latest_verison'], $plugin['version'], '>')) {
                                /* translators: %s: plugin latest version */
                                $version_string = ' &ndash; <strong style="color:red;">' . sprintf(esc_html__('%s is available', 'wpfep'), $plugin['latest_verison']) . '</strong>';
                            }

                            if (false !== $plugin['network_activated']) {
                                $network_string = ' &ndash; <strong style="color:black;">' . esc_attr__('Network enabled', 'wpfep') . '</strong>';
                            } ?>
							<tr>
								<td>
								<?php
                                echo wp_kses(
                                $plugin_name,
                                array(
                                        'a' => array(
                                            'href'  => array(),
                                            'id'    => array(),
                                            'class' => array(),
                                        ),
                                    )
                            ); ?>
			</td>
								<td>
								<?php
                                    /* translators: %s: plugin author */
                                    printf(esc_attr__('by %s', 'wpfep'), esc_html($plugin['author_name']));
                            echo ' &ndash; ' . esc_html($plugin['version']) . wp_kses(
                                $version_string,
                                array(
                                        'strong' => array(
                                            'style' => array(),

                                        ),
                                    )
                            ) . wp_kses(
                                $network_string,
                                array(
                                    'strong' => array(
                                        'style' => array(),

                                    ),
                                )
                            )
                                ?>
									</td>
							</tr>
							<?php
                        }
                    } ?>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="<?php esc_attr_e('Theme', 'wpfep'); ?>"><h2><?php esc_attr_e('Theme', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td data-export-label="<?php esc_attr_e('Name', 'wpfep'); ?>"><?php esc_attr_e('Name', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($theme['name']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Version', 'wpfep'); ?>"><?php esc_attr_e('Version', 'wpfep'); ?>:</td>
						<td>
						<?php
                            echo esc_html($theme['version']);
            if (version_compare($theme['version'], $theme['latest_verison'], '<')) {
                /* translators: %s: theme latest version */
                echo ' &ndash; <strong style="color:red;">' . sprintf(esc_attr__('%s is available', 'wpfep'), esc_html($theme['latest_verison'])) . '</strong>';
            } ?>
							</td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Author URL', 'wpfep'); ?>"><?php esc_attr_e('Author URL', 'wpfep'); ?>:</td>
						<td><?php echo esc_html($theme['author_url']); ?></td>
					</tr>
					<tr>
						<td data-export-label="<?php esc_attr_e('Child theme', 'wpfep'); ?>"><?php esc_attr_e('Child theme', 'wpfep'); ?>:</td>
						<td>
						<?php
                        /* translators: %s: child theme */
                            $theme_name = $theme['is_child_theme'] ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<span class="dashicons dashicons-no-alt"></span> &ndash; ' . sprintf(__('If you are modifying wpfep on a parent theme that you did not build personally we recommend using a child theme. See: <a href="%s" target="_blank">How to create a child theme</a>', 'wpfep'), 'https://codex.wordpress.org/Child_Themes');
            echo wp_kses(
                $theme_name,
                array(
                                'span' => array(

                                    'class' => array(),
                                ),
                                'a'    => array(
                                    'href'   => array(),
                                    'target' => array(),
                                ),
                            )
            ); ?>
							</td>
					</tr>
					<?php
                    if ($theme['is_child_theme']) {
                        ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('Parent Theme Name', 'wpfep'); ?>"><?php esc_attr_e('Parent theme name', 'wpfep'); ?>:</td>
							<td><?php echo esc_html($theme['parent_name']); ?></td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('Parent Theme Version', 'wpfep'); ?>"><?php esc_attr_e('Parent theme version', 'wpfep'); ?>:</td>
							<td>
							<?php
                                echo esc_html($theme['parent_version']);
                        if (version_compare($theme['parent_version'], $theme['parent_latest_verison'], '<')) {
                            /* translators: %s: parent theme latest version */
                            echo ' &ndash; <strong style="color:red;">' . sprintf(esc_attr__('%s is available', 'wpfep'), esc_html($theme['parent_latest_verison'])) . '</strong>';
                        } ?>
								</td>
						</tr>
						<tr>
							<td data-export-label="<?php esc_attr_e('Parent Theme Author URL', 'wpfep'); ?>"><?php esc_attr_e('Parent theme author URL', 'wpfep'); ?>:</td>
							<td><?php echo esc_html($theme['parent_author_url']); ?></td>
						</tr>
						<?php
                    } ?>
					</tbody>
				</table>
				<table class="wpfep-status-table widefat" cellspacing="0">
					<thead>
					<tr>
						<th colspan="3" data-export-label="<?php esc_attr_e('Templates', 'wpfep'); ?>"><h2><?php esc_attr_e('Templates', 'wpfep'); ?></h2></th>
					</tr>
					</thead>
					<tbody>
					<?php
                    if (! empty($theme['overrides'])) {
                        ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('Overrides', 'wpfep'); ?>"><?php esc_attr_e('Overrides', 'wpfep'); ?></td>
							<td>
								<?php
                                $total_overrides = count($theme['overrides']);
                        for ($i = 0; $i < $total_overrides; $i++) {
                            $override = $theme['overrides'][ $i ];
                            if (isset($override['core_version']) && (empty($override['version']) || version_compare($override['version'], $override['core_version'], '<'))) {
                                $current_version = $override['version'] ? $override['version'] : '-';
                                printf(
                                            /*
                                             * Translators: %1s: current version
                                             * Translators: %2s: your version
                                             * Translators: %3s: core version
                                             */
                                            esc_html__('%1$s version %2$s is out of date. The core version is %3$s', 'wpfep'),
                                    '<code>' . esc_html($override['file']) . '</code>',
                                    '<strong style="color:red">' . esc_html($current_version) . '</strong>',
                                    esc_html($override['core_version'])
                                );
                            } else {
                                echo esc_html($override['file']);
                            }
                            if ((count($theme['overrides']) - 1) !== $i) {
                                echo ', ';
                            }
                            echo '<br />';
                        } ?>
							</td>
						</tr>
						<?php
                    } else {
                        ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('Overrides', 'wpfep'); ?>"><?php esc_attr_e('Overrides', 'wpfep'); ?>:</td>
							<td>&ndash;</td>
						</tr>
						<?php
                    }

            if (true === $theme['has_outdated_templates']) {
                ?>
						<tr>
							<td data-export-label="<?php esc_attr_e('Outdated Templates', 'wpfep'); ?>"><?php esc_attr_e('Outdated templates', 'wpfep'); ?>:</td>
							<td><mark class="error"><span class="dashicons dashicons-warning"></span></mark></td>
						</tr>
						<?php
            } ?>
					</tbody>
				</table>
			</div>
			<?php
        }

        /**
         * Get array of environment information. Includes thing like software
         * versions, and various server settings.
         *
         * @since 1.0.0
         *
         * @return array
         */
        public static function wpfep_get_environment_info()
        {
            global $wpdb;

            // Figure out cURL version, if installed.
            $curl_version = '';
            if (function_exists('curl_version')) {
                $curl_version = curl_version();
                $curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
            }

            // WP memory limit.
            $wp_memory_limit = wpfep_let_to_num(WP_MEMORY_LIMIT);
            if (function_exists('memory_get_usage')) {
                $wp_memory_limit = max($wp_memory_limit, wpfep_let_to_num(@ini_get('memory_limit')));
            }

            // User agent.
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
            // Test POST requests.
            $post_response            = wp_safe_remote_post(
                'http://api.wordpress.org/core/browse-happy/1.1/',
                array(
                    'timeout'     => 10,
                    'user-agent'  => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
                    'httpversion' => '1.1',
                    'body'        => array(
                        'useragent' => $user_agent,
                    ),
                )
            );
            $post_response_body       = null;
            $post_response_successful = false;
            if (! is_wp_error($post_response) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300) {
                $post_response_successful = true;
                $post_response_body       = json_decode(wp_remote_retrieve_body($post_response), true);
            }

            // Test GET requests.
            $get_response            = wp_safe_remote_get(
                'https://plugins.svn.wordpress.org/wpptm/trunk/readme.txt',
                array(
                    'timeout'     => 10,
                    'user-agent'  => 'wpfep/' . WPFEP_VERSION,
                    'httpversion' => '1.1',
                )
            );
            $get_response_successful = false;
            if (! is_wp_error($post_response) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300) {
                $get_response_successful = true;
            }

            // Return all environment info. Described by JSON Schema.
            return array(
                'home_url'                  => get_option('home'),
                'site_url'                  => get_option('siteurl'),
                'version'                   => WPFEP_VERSION,
                'wp_version'                => get_bloginfo('version'),
                'wp_multisite'              => is_multisite(),
                'wp_memory_limit'           => $wp_memory_limit,
                'wp_debug_mode'             => (defined('WP_DEBUG') && WP_DEBUG),
                'wp_cron'                   => ! (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON),
                'language'                  => get_locale(),
                'server_info'               => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
                'php_version'               => phpversion(),
                'php_post_max_size'         => wpfep_let_to_num(ini_get('post_max_size')),
                'php_max_execution_time'    => ini_get('max_execution_time'),
                'php_max_input_vars'        => ini_get('max_input_vars'),
                'curl_version'              => $curl_version,
                'suhosin_installed'         => extension_loaded('suhosin'),
                'max_upload_size'           => wp_max_upload_size(),
                'mysql_version'             => (! empty($wpdb->is_mysql) ? $wpdb->db_version() : ''),
                'default_timezone'          => date_default_timezone_get(),
                'fsockopen_or_curl_enabled' => (function_exists('fsockopen') || function_exists('curl_init')),
                'soapclient_enabled'        => class_exists('SoapClient'),
                'domdocument_enabled'       => class_exists('DOMDocument'),
                'gzip_enabled'              => is_callable('gzopen'),
                'gd_library'                => extension_loaded('gd'),
                'mbstring_enabled'          => extension_loaded('mbstring'),
                'remote_post_successful'    => $post_response_successful,
                'remote_post_response'      => (is_wp_error($post_response) ? $post_response->get_error_message() : $post_response['response']['code']),
                'remote_get_successful'     => $get_response_successful,
                'remote_get_response'       => (is_wp_error($get_response) ? $get_response->get_error_message() : $get_response['response']['code']),
                'platform'                  => ! empty($post_response_body['platform']) ? $post_response_body['platform'] : '-',
                'browser_name'              => ! empty($post_response_body['name']) ? $post_response_body['name'] : '-',
                'browser_version'           => ! empty($post_response_body['version']) ? $post_response_body['version'] : '-',
                'user_agent'                => $user_agent,
            );
        }

        /**
         * Get all activated plugins.
         *
         * @since 1.0.0
         *
         * @return active plugins.
         */
        public static function wpfep_get_active_plugins()
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/update.php';

            if (! function_exists('get_plugin_updates')) {
                return array();
            }

            // Get both site plugins and network plugins.
            $active_plugins = (array) get_option('active_plugins', array());
            if (is_multisite()) {
                $network_activated_plugins = array_keys(get_site_option('active_sitewide_plugins', array()));
                $active_plugins            = array_merge($active_plugins, $network_activated_plugins);
            }

            $active_plugins_data = array();
            $available_updates   = get_plugin_updates();

            foreach ($active_plugins as $plugin) {
                $data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);

                // Convert plugin data to json response format.
                $active_plugins_data[] = array(
                    'plugin'            => $plugin,
                    'name'              => $data['Name'],
                    'version'           => $data['Version'],
                    'url'               => $data['PluginURI'],
                    'author_name'       => $data['AuthorName'],
                    'author_url'        => esc_url_raw($data['AuthorURI']),
                    'network_activated' => $data['Network'],
                    'latest_verison'    => (array_key_exists($plugin, $available_updates)) ? $available_updates[ $plugin ]->update->new_version : $data['Version'],
                );
            }

            return $active_plugins_data;
        }

        /**
         * Get all database info.
         *
         * @since 1.0.0
         *
         * @return database info.
         */
        public static function wpfep_get_database_info()
        {
            global $wpdb;

            $database_table_sizes = $wpdb->get_results(
                $wpdb->prepare(
                    "
                SELECT
                    table_name AS 'name',
                    round( ( data_length / 1024 / 1024 ), 2 ) 'data',
                    round( ( index_length / 1024 / 1024 ), 2 ) 'index'
                FROM information_schema.TABLES
                WHERE table_schema = %s
                ORDER BY name ASC;
            ",
                    DB_NAME
                )
            );

            /**
             * Organize WPFEP and non-WPFEP tables separately for display purposes later.
             *
             * To ensure we include all WPFEP tables, even if they do not exist, pre-populate the WPFEP array with all the tables.
             */
            $tables = array(
                'other' => array(),
            );

            $database_size = array(
                'data'  => 0,
                'index' => 0,
            );

            foreach ($database_table_sizes as $table) {
                $table_type = 'other';

                $tables[ $table_type ][ $table->name ] = array(
                    'data'  => $table->data,
                    'index' => $table->index,
                );

                $database_size['data']  += $table->data;
                $database_size['index'] += $table->index;
            }

            // Return all database info. Described by JSON Schema.
            return array(
                'wpfep_db_version' => get_option('wpfep_db_version'),
                'database_prefix'  => $wpdb->prefix,
                'database_tables'  => $tables,
                'database_size'    => $database_size,
            );
        }

        /**
         * Get all security info.
         *
         * @since 1.0.0
         *
         * @return security info.
         */
        public static function wpfep_get_security_info()
        {
            $check_page = get_home_url();

            return array(
                'secure_connection' => 'https' === substr($check_page, 0, 5),
                'hide_errors'       => ! (defined('WP_DEBUG') && defined('WP_DEBUG_DISPLAY') && WP_DEBUG && WP_DEBUG_DISPLAY) || 0 === intval(ini_get('display_errors')),
            );
        }

        /**
         * Get latest version of a theme by slug.
         *
         * @since 1.0.0
         *
         * @param object $theme WP_Theme object.
         *
         * @return string Version number if found.
         */
        public static function get_latest_theme_version($theme)
        {
            include_once ABSPATH . 'wp-admin/includes/theme.php';

            $api = themes_api(
                'theme_information',
                array(
                    'slug'   => $theme->get_stylesheet(),
                    'fields' => array(
                        'sections' => false,
                        'tags'     => false,
                    ),
                )
            );

            $update_theme_version = 0;

            // Check .org for updates.
            if (is_object($api) && ! is_wp_error($api)) {
                $update_theme_version = $api->version;
            }

            return $update_theme_version;
        }

        /**
         * Scan the template files.
         *
         * @param string $template_path Path to the template directory.
         *
         * @return array
         */
        public static function scan_template_files($template_path)
        {
            $files = @scandir($template_path); // @codingStandardsIgnoreLine.
            $result = array();

            if (! empty($files)) {
                foreach ($files as $key => $value) {
                    if (! in_array($value, array( '.', '..' ), true)) {
                        if (is_dir($template_path . DIRECTORY_SEPARATOR . $value)) {
                            $sub_files = self::scan_template_files($template_path . DIRECTORY_SEPARATOR . $value);
                            foreach ($sub_files as $sub_file) {
                                $result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
                            }
                        } else {
                            $result[] = $value;
                        }
                    }
                }
            }

            return $result;
        }

        /**
         * Get parent theme info if this theme is a child theme, otherwise pass empty info in the response.
         *
         * @return theme info
         */
        public static function wpfep_get_theme_info()
        {
            $active_theme = wp_get_theme();

            // Get parent theme info if this theme is a child theme, otherwise
            // pass empty info in the response.
            if (is_child_theme()) {
                $parent_theme      = wp_get_theme($active_theme->template);
                $parent_theme_info = array(
                    'parent_name'           => $parent_theme->name,
                    'parent_version'        => $parent_theme->version,
                    'parent_latest_verison' => self::get_latest_theme_version($parent_theme),
                    'parent_author_url'     => $parent_theme->{'Author URI'},
                );
            } else {
                $parent_theme_info = array(
                    'parent_name'           => '',
                    'parent_version'        => '',
                    'parent_latest_verison' => '',
                    'parent_author_url'     => '',
                );
            }

            /**
             * Scan the theme directory for all WPFEP templates to see if our theme
             * overrides any of them.
             */
            $override_files     = array();
            $outdated_templates = false;
            $scan_files         = self::scan_template_files(WPFEP_PATH . 'views/');

            foreach ($scan_files as $file) {
                if (file_exists(get_stylesheet_directory() . '/' . $file)) {
                    $theme_file = get_stylesheet_directory() . '/' . $file;
                } elseif (file_exists(get_stylesheet_directory() . '/wpfep/' . $file)) {
                    $theme_file = get_stylesheet_directory() . '/wpfep/' . $file;
                } elseif (file_exists(get_template_directory() . '/' . $file)) {
                    $theme_file = get_template_directory() . '/' . $file;
                } elseif (file_exists(get_template_directory() . '/wpfep/' . $file)) {
                    $theme_file = get_template_directory() . '/wpfep/' . $file;
                } else {
                    $theme_file = false;
                }

                if (! empty($theme_file)) {
                    $core_version  = self::get_file_version(WPFEP_PATH . '/views/' . $file);
                    $theme_version = self::get_file_version($theme_file);
                    if ($core_version && (empty($theme_version) || version_compare($theme_version, $core_version, '<'))) {
                        if (! $outdated_templates) {
                            $outdated_templates = true;
                        }
                    }
                    $override_files[] = array(
                        'file'         => str_replace(WP_CONTENT_DIR . '/themes/', '', $theme_file),
                        'version'      => $theme_version,
                        'core_version' => $core_version,
                    );
                }
            }

            $active_theme_info = array(
                'name'                   => $active_theme->name,
                'version'                => $active_theme->version,
                'latest_verison'         => self::get_latest_theme_version($active_theme),
                'author_url'             => esc_url_raw($active_theme->{'Author URI'}),
                'is_child_theme'         => is_child_theme(),
                'has_outdated_templates' => $outdated_templates,
                'overrides'              => $override_files,
            );

            return array_merge($active_theme_info, $parent_theme_info);
        }

        /**
         * Retrieve metadata from a file. Based on WP Core's get_file_data function.
         *
         * @since  1.0.0
         *
         * @param string $file Path to the file.
         *
         * @return string
         */
        public static function get_file_version($file)
        {
            // Avoid notices if file does not exist.
            if (! file_exists($file)) {
                return '';
            }

            // We don't need to write to the file, so just open for reading.
            $fp = fopen($file, 'r'); // @codingStandardsIgnoreLine.

            // Pull only the first 8kiB of the file in.
            $file_data = fread($fp, 8192); // @codingStandardsIgnoreLine.

            // PHP will close file handle, but we are good citizens.
            fclose($fp); // @codingStandardsIgnoreLine.

            // Make sure we catch CR-only line endings.
            $file_data = str_replace("\r", "\n", $file_data);
            $version   = '';

            if (preg_match('/^[ \t\/*#@]*' . preg_quote('@version', '/') . '(.*)$/mi', $file_data, $match) && $match[1]) {
                $version = _cleanup_header_comment($match[1]);
            }

            return $version;
        }
    }
}
