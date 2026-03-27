<?php
/**
 * Plugin Name: Customizer Backup & Reset
 * Plugin URI: http://wordpress.org/plugins/customizer-reset/
 * Description: Reset theme customizations (theme_mods) made via WordPress Customizer with backup and export features
 * Version: 2.2.0
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * Text Domain: customizer-reset-by-wpzoom
 * License: GPLv3 or later
 *
 * @package WPZOOM_Customizer_Reset
 */

namespace WPZOOM_Customizer_Reset;

add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomain' );
/**
 * Load plugin text domain for translations.
 *
 * @return void
 * @since 1.2.0
 */
function load_textdomain() {
	load_plugin_textdomain( 'customizer-reset-by-wpzoom', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'customize_register', __NAMESPACE__ . '\register_customizer_section' );
/**
 * Register customizer section for reset settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @return void
 * @since 1.2.0
 */
function register_customizer_section( $wp_customize ) {
	// Add the reset settings section.
	$wp_customize->add_section(
		'zoom_reset_section',
		array(
			'title'       => __( 'Customizer Backup & Reset', 'customizer-reset-by-wpzoom' ),
			'description' => get_reset_section_description(),
			'priority'    => 200, // Show at the bottom.
		)
	);

	// Add a dummy setting (required for the section to show).
	$wp_customize->add_setting(
		'zoom_reset_dummy',
		array(
			'default'   => '',
			'type'      => 'option',
			'transport' => 'postMessage',
		)
	);

	// Add a dummy control (required for the section to show).
	$wp_customize->add_control(
		'zoom_reset_dummy',
		array(
			'section' => 'zoom_reset_section',
			'type'    => 'hidden',
		)
	);
}

/**
 * Get the description HTML for the reset section.
 *
 * @return string HTML description.
 * @since 1.2.0
 */
function get_reset_section_description() {
	$backups      = get_backup_list();
	$backup_count = count( $backups );

	ob_start();
	?>
	<div class="zoom-reset-section-content">
		<div class="zoom-reset-actions">
			<h4><?php esc_html_e( 'Reset Customizer', 'customizer-reset-by-wpzoom' ); ?></h4>

			<p><button type="button" class="button button-primary zoom-action-backup-reset" data-action="backup-reset">
				<span class="dashicons dashicons-backup"></span>
				<?php esc_html_e( 'Backup & Reset Customizer', 'customizer-reset-by-wpzoom' ); ?>
			</button></p>

			<button type="button" class="button button-link-delete zoom-action-reset" data-action="reset">
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e( 'Reset Customizer (No Backup)', 'customizer-reset-by-wpzoom' ); ?>
			</button>

			<label class="zoom-reset-css-option">
				<input type="checkbox" id="zoom-reset-css-checkbox" value="1">
				<?php esc_html_e( 'Also remove Additional CSS', 'customizer-reset-by-wpzoom' ); ?>
			</label>

			<?php
			$inactive_mods       = get_inactive_theme_mods();
			$inactive_mods_count = count( $inactive_mods );
			$total_settings      = 0;
			foreach ( $inactive_mods as $entry ) {
				$total_settings += $entry['count'];
			}
			if ( $inactive_mods_count > 0 ) :
				?>
				<div class="zoom-inactive-mods">
					<p class="zoom-inactive-mods-notice">
						<span class="dashicons dashicons-info"></span>
						<?php
						printf(
							/* translators: %d: total number of old settings found */
							esc_html( _n(
								'%d old setting detected from other themes',
								'%d old settings detected from other themes',
								$total_settings,
								'customizer-reset-by-wpzoom'
							) ),
							$total_settings
						);
						?>
					</p>
					<p class="description">
						<?php esc_html_e( 'Old customizations from previously active themes are still stored in the database. These can cause settings like colors and fonts to reappear when switching themes.', 'customizer-reset-by-wpzoom' ); ?>
					</p>
					<details class="zoom-inactive-mods-details">
						<summary><?php esc_html_e( 'View inactive themes', 'customizer-reset-by-wpzoom' ); ?></summary>
						<ul class="zoom-inactive-mods-list">
							<?php foreach ( $inactive_mods as $entry ) : ?>
								<li>
									<?php if ( ! empty( $entry['is_wpzoom'] ) ) : ?>
										<strong><?php esc_html_e( 'WPZOOM customizer options', 'customizer-reset-by-wpzoom' ); ?></strong>
										<span class="description">
											&mdash;
											<?php
											printf(
												/* translators: %d: number of options */
												esc_html( _n( '%d option', '%d options', $entry['count'], 'customizer-reset-by-wpzoom' ) ),
												$entry['count']
											);
											?>
										</span>
									<?php else : ?>
										<strong><?php echo esc_html( $entry['stylesheet'] ); ?></strong>
										<span class="description">
											&mdash;
											<?php
											printf(
												/* translators: %d: number of settings */
												esc_html( _n( '%d setting', '%d settings', $entry['count'], 'customizer-reset-by-wpzoom' ) ),
												$entry['count']
											);
											?>
										</span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</details>
					<button type="button" class="button button-secondary zoom-cleanup-inactive" data-action="cleanup-inactive">
						<span class="dashicons dashicons-trash"></span>
						<?php
						printf(
							/* translators: %d: total number of old settings */
							esc_html__( 'Clean Up %d Old Settings', 'customizer-reset-by-wpzoom' ),
							$total_settings
						);
						?>
					</button>
				</div>
			<?php endif; ?>

			<hr class="zoom-separator">

			<h4><?php esc_html_e( 'Import & Export', 'customizer-reset-by-wpzoom' ); ?></h4>

			<p><button type="button" class="button button-secondary zoom-action-export" data-action="export">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Export Customizer Settings', 'customizer-reset-by-wpzoom' ); ?>
			</button></p>

			<button type="button" class="button button-secondary zoom-action-import" data-action="import">
				<span class="dashicons dashicons-upload"></span>
				<?php esc_html_e( 'Import Customizer Settings', 'customizer-reset-by-wpzoom' ); ?>
			</button>

			<div class="zoom-import-dropzone">
				<span class="dashicons dashicons-upload"></span>
				<p><?php esc_html_e( 'Or drag and drop a file here', 'customizer-reset-by-wpzoom' ); ?></p>
				<span class="description"><?php esc_html_e( '.json or .dat file', 'customizer-reset-by-wpzoom' ); ?></span>
			</div>

			<label class="zoom-import-images-option">
				<input type="checkbox" id="zoom-import-images-checkbox" value="1">
				<?php esc_html_e( 'Download and import image files?', 'customizer-reset-by-wpzoom' ); ?>
			</label>

			<div class="zoom-export-format">
				<label>
					<strong><?php esc_html_e( 'Export Format:', 'customizer-reset-by-wpzoom' ); ?></strong>
				</label>
				<label class="zoom-format-option">
					<input type="radio" name="zoom-export-format" value="json" checked>
					<?php esc_html_e( 'JSON', 'customizer-reset-by-wpzoom' ); ?>
					<span class="description">(<?php esc_html_e( 'recommended', 'customizer-reset-by-wpzoom' ); ?>)</span>
				</label>
				<label class="zoom-format-option">
					<input type="radio" name="zoom-export-format" value="dat">
					<?php esc_html_e( 'DAT', 'customizer-reset-by-wpzoom' ); ?>
				</label>
			</div>
		</div>

		<hr class="zoom-separator">

		<div class="zoom-backup-history">
			<h4>
				<?php esc_html_e( 'Backup History', 'customizer-reset-by-wpzoom' ); ?>
				<span class="zoom-backup-count">(<?php echo esc_html( $backup_count ); ?>)</span>
			</h4>

			<div class="zoom-backup-actions">
				<button type="button" class="button button-small zoom-create-backup">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Create Backup', 'customizer-reset-by-wpzoom' ); ?>
				</button>
				<?php if ( $backup_count > 0 ) : ?>
					<button type="button" class="button button-small button-link-delete zoom-delete-all-backups">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Delete All', 'customizer-reset-by-wpzoom' ); ?>
					</button>
				<?php endif; ?>
			</div>

			<?php if ( $backup_count > 0 ) : ?>
				<ul class="zoom-backup-list">
					<?php foreach ( $backups as $backup ) : ?>
						<li class="zoom-backup-item" data-backup-key="<?php echo esc_attr( $backup['key'] ); ?>">
							<div class="zoom-backup-info">
								<strong><?php echo esc_html( $backup['label'] ); ?></strong>
								<br>
								<small class="zoom-backup-meta">
									<?php
									printf(
										/* translators: %d: number of settings */
										esc_html( _n( '%d setting', '%d settings', $backup['count'], 'customizer-reset-by-wpzoom' ) ),
										(int) $backup['count']
									);
									?>
								</small>
							</div>
							<div class="zoom-backup-buttons">
								<button type="button" class="button button-small zoom-restore-backup" data-backup-key="<?php echo esc_attr( $backup['key'] ); ?>">
									<?php esc_html_e( 'Restore', 'customizer-reset-by-wpzoom' ); ?>
								</button>
								<button type="button" class="button button-small button-link-delete zoom-delete-backup" data-backup-key="<?php echo esc_attr( $backup['key'] ); ?>">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="description">
					<?php esc_html_e( 'No backups found. Use "Backup & Reset" to create a backup before resetting.', 'customizer-reset-by-wpzoom' ); ?>
				</p>
			<?php endif; ?>
		</div>

		<input type="file" id="zoom-reset-import-file" accept=".json" style="display:none;">
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Get list of available backups.
 *
 * @return array List of backups with metadata.
 * @since 1.2.0
 */
function get_backup_list() {
	$backups     = array();
	$stylesheet  = get_stylesheet();
	$backup_keys = array(
		'customizer_reset_backup_' . $stylesheet,
		'customizer_reset_backup_1_' . $stylesheet,
		'customizer_reset_backup_2_' . $stylesheet,
		'customizer_reset_backup_3_' . $stylesheet,
		'customizer_reset_backup_4_' . $stylesheet,
		'customizer_reset_backup_5_' . $stylesheet,
		'customizer_reset_backup_6_' . $stylesheet,
		'customizer_reset_backup_7_' . $stylesheet,
		'customizer_reset_backup_8_' . $stylesheet,
		'customizer_reset_backup_9_' . $stylesheet,
	);

	foreach ( $backup_keys as $key ) {
		$backup = get_transient( $key );
		if ( $backup && is_array( $backup ) && isset( $backup['mods'] ) ) {
			$backups[] = array(
				'key'   => $key,
				'label' => isset( $backup['created'] ) ? wp_date( 'M j, Y g:i A', strtotime( $backup['created'] ) ) : __( 'Unknown date', 'customizer-reset-by-wpzoom' ),
				'count' => count( $backup['mods'] ),
				'data'  => $backup,
			);
		}
	}

	return $backups;
}

add_action( 'customize_controls_print_scripts', __NAMESPACE__ . '\enqueue_scripts' );
/**
 * Enqueue scripts and localizations.
 *
 * @return void
 * @since 1.0.0
 */
function enqueue_scripts() {
	$plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$version     = $plugin_data['Version'];

	// Enqueue CSS.
	wp_enqueue_style(
		'zoom-customizer-reset',
		plugins_url( '/assets/css/customizer-reset.css', __FILE__ ),
		array(),
		$version
	);

	// Enqueue JavaScript.
	wp_enqueue_script(
		'zoom-customizer-reset',
		plugins_url( '/assets/js/customizer-reset.js', __FILE__ ),
		array( 'jquery' ),
		$version,
		false
	);

	wp_localize_script(
		'zoom-customizer-reset',
		'_ZoomCustomizerReset',
		array(
			'reset'       => __( 'Reset', 'customizer-reset-by-wpzoom' ),
			'export'      => __( 'Export Settings', 'customizer-reset-by-wpzoom' ),
			'import'      => __( 'Import Settings', 'customizer-reset-by-wpzoom' ),
			'backup'      => __( 'Backup & Reset', 'customizer-reset-by-wpzoom' ),
			'resetDirect' => __( 'Reset (No Backup)', 'customizer-reset-by-wpzoom' ),
			'confirm'     => __( "Attention!\n\nThis will remove all customizations ever made via customizer to this theme.\n\nThis action is irreversible unless you create a backup first.", 'customizer-reset-by-wpzoom' ),
			'resetting'   => __( 'Resetting...', 'customizer-reset-by-wpzoom' ),
			'exporting'   => __( 'Exporting...', 'customizer-reset-by-wpzoom' ),
			'importing'   => __( 'Importing...', 'customizer-reset-by-wpzoom' ),
			'restoring'   => __( 'Restoring...', 'customizer-reset-by-wpzoom' ),
			'hasBackup'   => get_transient( 'customizer_reset_backup_' . get_stylesheet() ) !== false,
			'nonce'       => array(
				'reset'      => wp_create_nonce( 'customizer-reset-by-wpzoom' ),
				'export'     => wp_create_nonce( 'customizer-export' ),
				'import'     => wp_create_nonce( 'customizer-import' ),
				'backup'     => wp_create_nonce( 'customizer-backup' ),
				'restore'    => wp_create_nonce( 'customizer-restore' ),
				'delete'     => wp_create_nonce( 'customizer-delete-backup' ),
				'deleteAll'        => wp_create_nonce( 'customizer-delete-all-backups' ),
				'cleanupInactive'  => wp_create_nonce( 'customizer-cleanup-inactive' ),
			),
		)
	);
}

/**
 * Collect option-type settings from the Customizer API.
 *
 * Returns each setting keyed by its full Customizer ID (including bracket
 * notation like astra-settings[key]) with the current value. This format
 * is compatible with WP_Customize_Setting::update() for proper restore.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @return array Setting ID => value pairs.
 * @since 2.2.0
 */
function collect_option_settings( $wp_customize ) {
	$options      = array();
	$core_options = array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' );

	foreach ( $wp_customize->settings() as $key => $setting ) {
		if ( 'option' !== $setting->type ) {
			continue;
		}
		if ( 'widget_' === substr( strtolower( $key ), 0, 7 ) ) {
			continue;
		}
		if ( 'sidebars_' === substr( strtolower( $key ), 0, 9 ) ) {
			continue;
		}
		if ( in_array( $key, $core_options, true ) ) {
			continue;
		}

		$options[ $key ] = $setting->value();
	}

	return $options;
}

/**
 * Restore option-type settings using WP_Customize_Setting::update().
 *
 * Handles both bracket-notation keys (e.g. astra-settings[key]) and
 * plain option names correctly via the WordPress Customizer API.
 *
 * WP_Customize_Setting::update() is protected, so we extend the class
 * (same pattern as Customizer Export/Import's CEI_Option). The class is
 * declared inside this function so WP_Customize_Setting is already loaded.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @param array                $options      Setting ID => value pairs.
 * @return int Number of options restored.
 * @since 2.2.0
 */
function restore_option_settings( $wp_customize, $options ) {
	$count = 0;

	foreach ( $options as $option_key => $option_value ) {
		$setting = new class( $wp_customize, $option_key, array(
			'default'    => '',
			'type'       => 'option',
			'capability' => 'edit_theme_options',
		) ) extends \WP_Customize_Setting {
			/** @param mixed $value The option value to persist. */
			public function import( $value ) {
				$this->update( $value );
			}
		};
		$setting->import( $option_value );
		++$count;
	}

	return $count;
}

add_action( 'wp_ajax_customizer_export', __NAMESPACE__ . '\export_theme_modifications' );
/**
 * Export theme modifications in JSON or DAT format.
 *
 * @return void
 * @since 1.2.0
 */
function export_theme_modifications() {
	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-export', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Get format (json or dat).
	$format = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'json';

	if ( 'dat' === $format ) {
		export_as_dat();
	} else {
		export_as_json();
	}
}

/**
 * Export as JSON format.
 *
 * @return void
 * @since 1.2.0
 */
function export_as_json() {
	global $wp_customize;

	$theme_mods = get_theme_mods();
	$theme      = wp_get_theme();

	$export_data = array(
		'theme'      => $theme->get( 'Name' ),
		'version'    => $theme->get( 'Version' ),
		'stylesheet' => get_stylesheet(),
		'template'   => get_template(),
		'exported'   => current_time( 'mysql' ),
		'mods'       => $theme_mods,
		'options'    => $wp_customize ? collect_option_settings( $wp_customize ) : array(),
	);

	if ( function_exists( 'wp_get_custom_css' ) ) {
		$export_data['wp_css'] = wp_get_custom_css();
	}

	// Debug: if $wp_customize is null, log it so we can diagnose.
	if ( ! $wp_customize ) {
		$export_data['_debug'] = 'wp_customize_not_available';
	} elseif ( empty( $export_data['options'] ) ) {
		$export_data['_debug'] = 'settings_count_' . count( $wp_customize->settings() );
	}

	wp_send_json_success( $export_data );
}

/**
 * Export as DAT format (PHP serialize) - Compatible with Customizer Export/Import plugin.
 *
 * @return void
 * @since 1.2.0
 */
function export_as_dat() {
	global $wp_customize;

	$theme      = wp_get_theme();
	$template   = get_template();
	$stylesheet = get_stylesheet();
	$charset    = get_option( 'blog_charset' );
	$mods       = get_theme_mods();

	$data = array(
		'template' => $template,
		'mods'     => $mods ? $mods : array(),
		'options'  => $wp_customize ? collect_option_settings( $wp_customize ) : array(),
	);

	// Allow plugin/theme developers to specify additional option keys to export.
	$custom_option_keys = apply_filters( 'customizer_reset_export_option_keys', array() );
	foreach ( $custom_option_keys as $option_key ) {
		$data['options'][ $option_key ] = get_option( $option_key );
	}

	// Include custom CSS if available.
	if ( function_exists( 'wp_get_custom_css' ) ) {
		$data['wp_css'] = wp_get_custom_css();
	}

	// Serialize the export data.
	$serialized_data = serialize( $data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize

	// Send as JSON response with the serialized data (JavaScript will handle the download).
	wp_send_json_success( $serialized_data );
}

add_action( 'wp_ajax_customizer_backup', __NAMESPACE__ . '\backup_theme_modifications' );
/**
 * Create a backup of theme modifications before reset.
 *
 * @return void
 * @since 1.2.0
 */
function backup_theme_modifications() {
	global $wp_customize;

	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify we're in customizer preview.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-backup', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Get all theme modifications.
	$theme_mods = get_theme_mods();
	$theme      = wp_get_theme();

	// Collect option-type settings registered via the Customizer API.
	// We snapshot the full top-level option (e.g. the entire generate_settings
	// array) so update_option() can restore it in a single write.
	$options      = array();
	$core_options = array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' );

	foreach ( $wp_customize->settings() as $key => $setting ) {
		if ( 'option' !== $setting->type ) {
			continue;
		}
		if ( 'widget_' === substr( strtolower( $key ), 0, 7 ) ) {
			continue;
		}
		if ( 'sidebars_' === substr( strtolower( $key ), 0, 9 ) ) {
			continue;
		}

		$bracket_pos = strpos( $key, '[' );
		$option_name = false !== $bracket_pos ? substr( $key, 0, $bracket_pos ) : $key;

		if ( in_array( $option_name, $core_options, true ) ) {
			continue;
		}
		if ( ! array_key_exists( $option_name, $options ) ) {
			$options[ $option_name ] = get_option( $option_name );
		}
	}

	$backup_data = array(
		'theme'      => $theme->get( 'Name' ),
		'version'    => $theme->get( 'Version' ),
		'stylesheet' => get_stylesheet(),
		'created'    => current_time( 'mysql' ),
		'mods'       => $theme_mods,
		'options'    => $options,
	);

	// Include Additional CSS in backup if available.
	if ( function_exists( 'wp_get_custom_css' ) ) {
		$backup_data['wp_css'] = wp_get_custom_css();
	}

	// Rotate backups (keep last 5).
	rotate_backups();

	// Store backup in transient for 30 days.
	$transient_key = 'customizer_reset_backup_' . get_stylesheet();
	set_transient( $transient_key, $backup_data, 30 * DAY_IN_SECONDS );

	wp_send_json_success(
		array(
			'message' => __( 'Backup created successfully', 'customizer-reset-by-wpzoom' ),
			'count'   => count( $theme_mods ) + count( $options ),
		)
	);
}

/**
 * Rotate backups to keep only the last 5.
 *
 * @return void
 * @since 1.2.0
 */
function rotate_backups() {
	$stylesheet = get_stylesheet();

	// Shift existing backups (now supporting 10 backups).
	for ( $i = 9; $i >= 1; $i-- ) {
		$from_key = $i === 1 ? 'customizer_reset_backup_' . $stylesheet : 'customizer_reset_backup_' . ( $i - 1 ) . '_' . $stylesheet;
		$to_key   = 'customizer_reset_backup_' . $i . '_' . $stylesheet;

		$backup = get_transient( $from_key );
		if ( $backup ) {
			set_transient( $to_key, $backup, 30 * DAY_IN_SECONDS );
		}
	}
}

/**
 * Check if a value is an image URL.
 *
 * @param mixed $value The value to check.
 * @return bool True if the value is an image URL.
 * @since 1.2.0
 */
function is_image_url( $value ) {
	if ( ! is_string( $value ) ) {
		return false;
	}

	// Check if it's a URL and ends with image extension.
	return preg_match( '/\.(jpe?g|png|gif|webp)(\?.*)?$/i', $value );
}

/**
 * Download a remote image and add it to the media library.
 *
 * @param string $url The URL of the image to download.
 * @return int|false The attachment ID on success, false on failure.
 * @since 1.2.0
 */
function sideload_image( $url ) {
	// Validate URL.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return false;
	}

	// Check if already imported.
	global $wpdb;
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid='%s';", $url ) );
	if ( ! empty( $attachment ) ) {
		return $attachment[0];
	}

	// Download file.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) {
		return false;
	}

	// Get file name from URL.
	$file_array = array();
	preg_match( '/[^\?]+\.(jpe?g|png|gif|webp)/i', $url, $matches );
	$file_array['name']     = basename( $matches[0] );
	$file_array['tmp_name'] = $tmp;

	// Upload to media library.
	$id = media_handle_sideload( $file_array, 0 );

	// Clean up temporary file.
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );
		return false;
	}

	return $id;
}

/**
 * Process and import images from theme mods.
 *
 * @param array $mods Theme modifications array.
 * @return array Modified theme mods with local image URLs.
 * @since 1.2.0
 */
function import_images( $mods ) {
	if ( ! is_array( $mods ) ) {
		return $mods;
	}

	foreach ( $mods as $key => $value ) {
		// Handle simple image URLs.
		if ( is_image_url( $value ) ) {
			$attachment_id = sideload_image( $value );
			if ( $attachment_id ) {
				$mods[ $key ] = wp_get_attachment_url( $attachment_id );

				// Handle header image data.
				if ( strpos( $key, 'header_image' ) !== false ) {
					$header_data_key = $key . '_data';
					$image_data      = wp_get_attachment_metadata( $attachment_id );
					if ( $image_data ) {
						$mods[ $header_data_key ] = (object) array(
							'attachment_id' => $attachment_id,
							'url'           => wp_get_attachment_url( $attachment_id ),
							'thumbnail_url' => wp_get_attachment_thumb_url( $attachment_id ),
							'height'        => $image_data['height'],
							'width'         => $image_data['width'],
						);
					}
				}
			}
		}

		// Handle arrays recursively.
		if ( is_array( $value ) ) {
			$mods[ $key ] = import_images( $value );
		}
	}

	return $mods;
}

add_action( 'wp_ajax_customizer_import', __NAMESPACE__ . '\import_theme_modifications' );
/**
 * Import theme modifications from JSON or DAT format.
 *
 * @return void
 * @since 1.2.0
 */
function import_theme_modifications() {
	global $wp_customize;

	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify we're in customizer preview.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-import', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Validate import data exists.
	if ( ! isset( $_POST['import_data'] ) ) {
		wp_send_json_error( 'no_import_data' );
	}

	$raw_data = wp_unslash( $_POST['import_data'] );

	// Get format hint if provided.
	$format = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : '';

	// Auto-detect format: Try JSON first, then serialize.
	$import_data = null;

	if ( 'dat' === $format || empty( $format ) ) {
		// Try unserialize (DAT format).
		$import_data = @unserialize( $raw_data, array( 'allowed_classes' => false ) );
	}

	// If unserialize failed or format is json, try JSON.
	if ( null === $import_data || ! is_array( $import_data ) ) {
		$import_data = json_decode( $raw_data, true );
	}

	// Still no valid data? Try unserialize as last resort.
	if ( ( null === $import_data || ! is_array( $import_data ) ) && 'json' !== $format ) {
		$import_data = @unserialize( $raw_data, array( 'allowed_classes' => false ) );
	}

	if ( null === $import_data || ! is_array( $import_data ) ) {
		wp_send_json_error( 'invalid_data_format' );
	}

	// Validate data structure.
	if ( ! isset( $import_data['mods'] ) || ! is_array( $import_data['mods'] ) ) {
		wp_send_json_error( 'invalid_data_structure' );
	}

	// Check template/theme compatibility.
	$current_template = get_template();
	if ( isset( $import_data['template'] ) && $import_data['template'] !== $current_template ) {
		// Allow but it's from different theme - handled in JS.
	}

	// Check if user wants to import images.
	$import_images_flag = isset( $_POST['import_images'] ) && '1' === $_POST['import_images'];

	// Process images if requested.
	if ( $import_images_flag ) {
		$import_data['mods'] = import_images( $import_data['mods'] );
	}

	// Call WordPress customize_save action before import (for theme compatibility).
	do_action( 'customize_save', $wp_customize );

	// Import the theme mods.
	$imported_count = 0;
	foreach ( $import_data['mods'] as $key => $value ) {
		// Sanitize the key.
		$key = sanitize_key( $key );

		// Call customize_save_{$key} action for each setting (for theme compatibility).
		do_action( "customize_save_{$key}", $wp_customize );

		set_theme_mod( $key, $value );
		$imported_count++;
	}

	// Import option-type settings via WP_Customize_Setting::update() which
	// properly handles bracket-notation keys (e.g. astra-settings[key]).
	if ( isset( $import_data['options'] ) && is_array( $import_data['options'] ) ) {
		$core_options = array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' );
		$filtered     = array_diff_key( $import_data['options'], array_flip( $core_options ) );
		$imported_count += restore_option_settings( $wp_customize, $filtered );
	}

	// Import custom CSS if available (DAT format).
	if ( isset( $import_data['wp_css'] ) && function_exists( 'wp_update_custom_css_post' ) ) {
		wp_update_custom_css_post( $import_data['wp_css'] );
	}

	// Call WordPress customize_save_after action (for theme compatibility).
	do_action( 'customize_save_after', $wp_customize );

	wp_send_json_success(
		array(
			'message' => __( 'Settings imported successfully', 'customizer-reset-by-wpzoom' ),
			'count'   => $imported_count,
		)
	);
}

add_action( 'wp_ajax_customizer_restore_backup', __NAMESPACE__ . '\restore_backup' );
/**
 * Restore settings from a backup.
 *
 * @return void
 * @since 1.2.0
 */
function restore_backup() {
	global $wp_customize;

	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify we're in customizer preview.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-restore', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Validate backup key.
	if ( ! isset( $_POST['backup_key'] ) ) {
		wp_send_json_error( 'no_backup_key' );
	}

	$backup_key = sanitize_text_field( wp_unslash( $_POST['backup_key'] ) );

	// Get the backup.
	$backup = get_transient( $backup_key );

	if ( ! $backup || ! is_array( $backup ) || ! isset( $backup['mods'] ) ) {
		wp_send_json_error( 'backup_not_found' );
	}

	// Restore the theme mods.
	$restored_count = 0;
	foreach ( $backup['mods'] as $key => $value ) {
		set_theme_mod( $key, $value );
		$restored_count++;
	}

	// Restore option-type settings. Each entry is a top-level option name
	// (e.g. generate_settings, astra-settings) mapped to its full value.
	if ( isset( $backup['options'] ) && is_array( $backup['options'] ) ) {
		foreach ( $backup['options'] as $option_name => $option_value ) {
			update_option( $option_name, $option_value );
			++$restored_count;
		}
	}

	// Restore Additional CSS if available in backup.
	if ( isset( $backup['wp_css'] ) && function_exists( 'wp_update_custom_css_post' ) ) {
		wp_update_custom_css_post( $backup['wp_css'] );
	}

	wp_send_json_success(
		array(
			'message' => __( 'Backup restored successfully', 'customizer-reset-by-wpzoom' ),
			'count'   => $restored_count,
		)
	);
}

add_action( 'wp_ajax_customizer_delete_backup', __NAMESPACE__ . '\delete_backup' );
/**
 * Delete a single backup.
 *
 * @return void
 * @since 1.2.0
 */
function delete_backup() {
	global $wp_customize;

	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify we're in customizer preview.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-delete-backup', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Validate backup key.
	if ( ! isset( $_POST['backup_key'] ) ) {
		wp_send_json_error( 'no_backup_key' );
	}

	$backup_key = sanitize_text_field( wp_unslash( $_POST['backup_key'] ) );

	// Delete the transient.
	$deleted = delete_transient( $backup_key );

	if ( $deleted ) {
		wp_send_json_success(
			array(
				'message' => __( 'Backup deleted successfully', 'customizer-reset-by-wpzoom' ),
			)
		);
	} else {
		wp_send_json_error( 'backup_not_found_or_already_deleted' );
	}
}

add_action( 'wp_ajax_customizer_delete_all_backups', __NAMESPACE__ . '\delete_all_backups' );
/**
 * Delete all backups.
 *
 * @return void
 * @since 1.2.0
 */
function delete_all_backups() {
	global $wp_customize;

	// Check permissions.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Verify we're in customizer preview.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-delete-all-backups', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	$stylesheet  = get_stylesheet();
	$backup_keys = array(
		'customizer_reset_backup_' . $stylesheet,
		'customizer_reset_backup_1_' . $stylesheet,
		'customizer_reset_backup_2_' . $stylesheet,
		'customizer_reset_backup_3_' . $stylesheet,
		'customizer_reset_backup_4_' . $stylesheet,
		'customizer_reset_backup_5_' . $stylesheet,
		'customizer_reset_backup_6_' . $stylesheet,
		'customizer_reset_backup_7_' . $stylesheet,
		'customizer_reset_backup_8_' . $stylesheet,
		'customizer_reset_backup_9_' . $stylesheet,
	);

	$deleted_count = 0;
	foreach ( $backup_keys as $key ) {
		if ( delete_transient( $key ) ) {
			$deleted_count++;
		}
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %d: number of backups deleted */
				__( '%d backups deleted successfully', 'customizer-reset-by-wpzoom' ),
				$deleted_count
			),
			'count'   => $deleted_count,
		)
	);
}

/**
 * Extract customizer option keys from a wpzoom_customizer_data array.
 *
 * @param array $customizer_data Customizer data array from a WPZOOM theme.
 * @return array List of option IDs (without the wpzoom_ prefix).
 * @since 2.1.0
 */
function extract_wpzoom_customizer_keys( $customizer_data ) {
	$keys = array();

	foreach ( $customizer_data as $section ) {
		if ( ! isset( $section['options'] ) || ! is_array( $section['options'] ) ) {
			continue;
		}
		foreach ( $section['options'] as $id => $option ) {
			$keys[] = $id;
		}
	}

	return $keys;
}

/**
 * Get customizer option keys from the active WPZOOM theme.
 *
 * @return array List of option IDs (without the wpzoom_ prefix).
 * @since 2.1.0
 */
function get_wpzoom_customizer_keys() {
	return extract_wpzoom_customizer_keys( apply_filters( 'wpzoom_customizer_data', array() ) );
}

/**
 * Get customizer option keys from ALL installed WPZOOM themes.
 *
 * Scans all installed themes for customizer-data.php files and extracts
 * all customizer option IDs by parsing the file contents. This avoids
 * executing theme files which may depend on theme-specific constants.
 *
 * This is needed for cleanup because different themes have different
 * customizer keys, and options from a previously active theme may not
 * be known to the currently active theme.
 *
 * @return array List of unique option IDs (without the wpzoom_ prefix).
 * @since 2.1.0
 */
function get_all_wpzoom_customizer_keys() {
	// Start with the active theme's keys (from the live filter).
	$all_keys = get_wpzoom_customizer_keys();

	// Scan all installed themes for customizer-data.php files.
	$themes_dir = get_theme_root();

	if ( ! is_dir( $themes_dir ) ) {
		return array_unique( $all_keys );
	}

	$themes = array_diff( scandir( $themes_dir ), array( '.', '..' ) );

	foreach ( $themes as $theme_slug ) {
		$data_file = $themes_dir . '/' . $theme_slug . '/functions/customizer/customizer-data.php';
		if ( ! file_exists( $data_file ) ) {
			continue;
		}

		$contents = file_get_contents( $data_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( false === $contents ) {
			continue;
		}

		// Verify this is a WPZOOM customizer data file.
		if ( false === strpos( $contents, 'wpzoom_customizer_data' ) ) {
			continue;
		}

		// Parse option IDs from the file contents.
		// WPZOOM customizer-data.php files follow a consistent pattern where
		// option IDs are string keys inside 'options' => array( ... ) blocks.
		// We extract all quoted string keys followed by => array(.
		$keys = parse_wpzoom_customizer_keys_from_source( $contents );

		$all_keys = array_merge( $all_keys, $keys );
	}

	return array_unique( $all_keys );
}

/**
 * Parse customizer option IDs from a WPZOOM customizer-data.php file's source code.
 *
 * WPZOOM themes define customizer data in a consistent structure:
 *   'section-name' => array(
 *       'options' => array(
 *           'option-id' => array( ... ),
 *       ),
 *   ),
 *
 * This function extracts the option IDs without executing the PHP file.
 *
 * @param string $source PHP source code of a customizer-data.php file.
 * @return array List of option IDs.
 * @since 2.1.0
 */
function parse_wpzoom_customizer_keys_from_source( $source ) {
	$keys = array();

	// Find all 'options' => array( ... ) blocks and extract their keys.
	// Pattern: after 'options' => array(, capture quoted keys before => array(.
	$in_options = false;
	$depth      = 0;

	// Split into tokens for reliable parsing.
	$tokens = token_get_all( $source );
	$count  = count( $tokens );

	for ( $i = 0; $i < $count; $i++ ) {
		$token = $tokens[ $i ];

		// Track when we enter an 'options' => array( block.
		if ( is_array( $token ) && T_CONSTANT_ENCAPSED_STRING === $token[0] ) {
			$value = trim( $token[1], "\"'" );

			if ( 'options' === $value ) {
				// Look ahead for => array(.
				$j = $i + 1;
				while ( $j < $count && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
					++$j;
				}
				if ( $j < $count && is_array( $tokens[ $j ] ) && T_DOUBLE_ARROW === $tokens[ $j ][0] ) {
					++$j;
					while ( $j < $count && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
						++$j;
					}
					if ( $j < $count && is_array( $tokens[ $j ] ) && T_ARRAY === $tokens[ $j ][0] ) {
						$in_options = true;
						$depth      = 0;
						$i          = $j;
						continue;
					}
				}
			}

			// If we're at depth 1 inside an options block, this is an option ID.
			if ( $in_options && 1 === $depth ) {
				// Verify it's followed by => array(.
				$j = $i + 1;
				while ( $j < $count && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
					++$j;
				}
				if ( $j < $count && is_array( $tokens[ $j ] ) && T_DOUBLE_ARROW === $tokens[ $j ][0] ) {
					$keys[] = $value;
				}
			}
		}

		// Track parentheses depth inside options blocks.
		if ( $in_options ) {
			if ( '(' === $token ) {
				++$depth;
			} elseif ( ')' === $token ) {
				--$depth;
				if ( $depth <= 0 ) {
					$in_options = false;
				}
			}
		}
	}

	return $keys;
}

/**
 * Get the wpzoom_* customizer options stored in the database.
 *
 * When $all_themes is true, checks keys from all installed WPZOOM themes.
 * When false, only checks keys from the active theme's customizer data.
 *
 * @param bool $all_themes Whether to include keys from all installed WPZOOM themes.
 * @return array List of option names (with wpzoom_ prefix).
 * @since 2.1.0
 */
function get_wpzoom_customizer_options( $all_themes = false ) {
	$keys = $all_themes ? get_all_wpzoom_customizer_keys() : get_wpzoom_customizer_keys();
	if ( empty( $keys ) ) {
		return array();
	}

	$option_names = array();
	foreach ( $keys as $key ) {
		$option_name = 'wpzoom_' . $key;
		if ( false !== get_option( $option_name ) ) {
			$option_names[] = $option_name;
		}
	}

	return array_unique( $option_names );
}

/**
 * Delete wpzoom_* options that belong to the customizer.
 *
 * When $all_themes is true, deletes options matching any installed WPZOOM theme's
 * customizer keys. When false, only deletes options matching the active theme.
 *
 * @param bool $all_themes Whether to include keys from all installed WPZOOM themes.
 * @return int Number of options deleted.
 * @since 2.1.0
 */
function delete_wpzoom_customizer_options( $all_themes = false ) {
	$option_names = get_wpzoom_customizer_options( $all_themes );
	$deleted      = 0;

	foreach ( $option_names as $option_name ) {
		if ( delete_option( $option_name ) ) {
			++$deleted;
		}
	}

	return $deleted;
}

/**
 * Get list of inactive theme mods stored in the database.
 *
 * WordPress stores theme_mods in separate options per theme (theme_mods_{stylesheet}).
 * When switching themes, old options remain in the database and can cause settings
 * to reappear when switching to another theme that uses the same option keys.
 *
 * Also detects wpzoom_* global options that persist across WPZOOM theme switches.
 *
 * @return array List of inactive theme mod entries with stylesheet, count, and option name.
 * @since 2.1.0
 */
function get_inactive_theme_mods() {
	global $wpdb;

	$current_stylesheet = get_stylesheet();

	// Find all theme_mods_* options in the database, excluding the active theme.
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s AND option_name != %s",
			$wpdb->esc_like( 'theme_mods_' ) . '%',
			'theme_mods_' . $current_stylesheet
		)
	);

	$inactive = array();
	foreach ( $results as $row ) {
		$stylesheet = substr( $row->option_name, strlen( 'theme_mods_' ) );
		$mods       = maybe_unserialize( $row->option_value );
		$mod_count  = 0;

		if ( is_array( $mods ) ) {
			// Count actual customizer mods, excluding internal WordPress keys.
			foreach ( $mods as $key => $value ) {
				if ( 0 !== strpos( $key, 'nav_menu_locations' ) && ! in_array( $key, array( 'sidebars_widgets', 'custom_css_post_id' ), true ) ) {
					++$mod_count;
				}
			}
		}

		if ( $mod_count > 0 ) {
			$inactive[] = array(
				'stylesheet'  => $stylesheet,
				'option_name' => $row->option_name,
				'count'       => $mod_count,
			);
		}
	}

	// Also detect wpzoom_* customizer options that persist across theme switches.
	// Use all_themes=true to catch options from any WPZOOM theme, not just the active one.
	$wpzoom_options = get_wpzoom_customizer_options( true );
	$wpzoom_count   = count( $wpzoom_options );
	if ( $wpzoom_count > 0 ) {
		$inactive[] = array(
			'stylesheet'  => 'wpzoom_*',
			'option_name' => 'wpzoom_*',
			'count'       => $wpzoom_count,
			'is_wpzoom'   => true,
		);
	}

	return $inactive;
}

add_action( 'wp_ajax_customizer_cleanup_inactive', __NAMESPACE__ . '\cleanup_inactive_theme_mods' );
/**
 * Remove theme_mods for all inactive themes.
 *
 * @return void
 * @since 2.1.0
 */
function cleanup_inactive_theme_mods() {
	global $wp_customize;

	// Bail early if user doesn't have permission.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Bail early if we are not in preview mode.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Bail early if nonce is invalid.
	if ( ! check_ajax_referer( 'customizer-cleanup-inactive', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Validate request data.
	if ( ! isset( $_POST['wp_customize'] ) || 'on' !== $_POST['wp_customize'] ) {
		wp_send_json_error( 'invalid_request' );
	}

	$inactive      = get_inactive_theme_mods();
	$deleted_count = 0;

	foreach ( $inactive as $entry ) {
		if ( ! empty( $entry['is_wpzoom'] ) ) {
			// Delete wpzoom_* customizer options from all installed WPZOOM themes.
			$deleted_count += delete_wpzoom_customizer_options( true );
		} elseif ( delete_option( $entry['option_name'] ) ) {
			++$deleted_count;
		}
	}

	wp_send_json_success(
		array(
			'message' => sprintf(
				/* translators: %d: number of inactive theme entries deleted */
				_n(
					'%d inactive theme entry removed',
					'%d inactive theme entries removed',
					$deleted_count,
					'customizer-reset-by-wpzoom'
				),
				$deleted_count
			),
			'count'   => $deleted_count,
		)
	);
}

add_action( 'wp_ajax_customizer_reset', __NAMESPACE__ . '\remove_theme_modifications' );
/**
 * Run methods if nonce and not in preview mode
 *
 * @return void
 * @since 1.0.0
 */
function remove_theme_modifications() {
	global $wp_customize;

	// Bail early if user doesn't have permission.
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( 'insufficient_permissions' );
	}

	// Bail early if we are not in preview mode.
	if ( ! $wp_customize || ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Bail early if nonce is invalid.
	if ( ! check_ajax_referer( 'customizer-reset-by-wpzoom', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Validate request data.
	if ( ! isset( $_POST['wp_customize'] ) || 'on' !== $_POST['wp_customize'] ) {
		wp_send_json_error( 'invalid_request' );
	}

	// Gets the current theme.
	$theme     = wp_get_theme();
	$themename = sanitize_key( strtolower( $theme->name ) );

	/**
	 * Make compatible with Divi customizer settings.
	 *
	 * The Theme Options are stored in wp_options  table, the option name is et_divi.
	 *
	 * The Options of the Theme Customizer are stored in the wp_options table, the option name is theme_mods_*themename* .
	 * For example if you are using Divi theme the option name will be theme_mods_divi.
	 *
	 * @since 1.1.0
	 */
	if ( 'divi' === $themename ) {
		$customizer_settings = get_option( "theme_mods_{$themename}" );
		$theme_options       = get_option( "et_{$themename}" );

		// Access Divi's global options if available.
		global $options;
		if ( isset( $options ) && is_array( $options ) && ! empty( $theme_options ) ) {
			$et_divi = array();
			foreach ( $options as $option ) {
				// Skip option without id.
				if ( ! isset( $option['id'] ) ) {
					continue;
				}

				// Leave only Theme Options and remove customizer settings from array.
				if ( isset( $theme_options[ $option['id'] ] ) ) {
					$et_divi[ $option['id'] ] = $theme_options[ $option['id'] ];
				}
			}

			if ( ! empty( $et_divi ) ) {
				update_option( "et_{$themename}", $et_divi );
			}
		}

		if ( $customizer_settings ) {
			delete_option( "theme_mods_{$themename}" );
		}
	}

	/**
	 * Make compatible with Astra theme.
	 * All customizer settings are stored to option 'astra-settings'.
	 *
	 * @since 1.1.1
	 */
	if ( 'astra' === $themename ) {
		if ( defined( 'ASTRA_THEME_SETTINGS' ) ) {
			$theme_options = get_option( \ASTRA_THEME_SETTINGS );
			$auto_version  = $theme_options['theme-auto-version'] ?? $theme_options['astra-addon-auto-version'] ?? false;

			if ( false !== $auto_version ) {
				update_option( \ASTRA_THEME_SETTINGS, array( 'theme-auto-version' => $auto_version ) );
			} else {
				delete_option( \ASTRA_THEME_SETTINGS );
			}
		}
	}

	/**
	 * Make compatible with GeneratePress theme.
	 * GeneratePress stores ALL customizer settings in the 'generate_settings' option (both free and premium).
	 *
	 * @since 1.2.0
	 */
	if ( 'generatepress' === $themename ) {
		// Delete the main settings option (used by both free and premium versions).
		delete_option( 'generate_settings' );

		// Delete other GeneratePress options that store customizer settings.
		$gp_options = array(
			'generate_blog_settings',
			'generate_spacing_settings',
			'generate_menu_plus_settings',
			'generate_backgrounds_settings',
			'generate_colors_settings',
			'generate_copyright',
			'generate_disable_elements',
			'generate_secondary_nav_settings',
		);

		foreach ( $gp_options as $gp_option ) {
			delete_option( $gp_option );
		}

		// Clear GeneratePress dynamic CSS cache (important for frontend changes to appear).
		delete_option( 'generate_dynamic_css_output' );
		delete_option( 'generate_dynamic_css_cached_version' );

		// Also reset the standard theme_mods.
		remove_theme_mods();
	}

	/**
	 * Make compatible with WPZOOM themes.
	 *
	 * WPZOOM themes store customizer settings as individual wpzoom_* options in wp_options
	 * (e.g., wpzoom_color-background, wpzoom_font-family-body). These are global and shared
	 * across all WPZOOM themes, causing old settings to bleed into other themes when switching.
	 *
	 * Only deletes wpzoom_* options that match the current theme's customizer data,
	 * leaving theme panel options (Theme Options page) untouched.
	 *
	 * @since 2.1.0
	 */
	if ( class_exists( 'option' ) && property_exists( 'option', '$prefix' ) && 'wpzoom_' === \option::$prefix ) {
		delete_wpzoom_customizer_options();
	}

	/**
	 * Filter the settings that will be removed.
	 *
	 * @param array $settings Theme modifications.
	 * @return array
	 * @since 1.1.0
	 */
	$settings = apply_filters( 'customizer_reset_settings', $wp_customize->settings() );

	if ( ! empty( $settings ) ) {
		foreach ( $settings as $setting ) {
			if ( 'theme_mod' === $setting->type ) {
				remove_theme_mod( $setting->id );
			}
		}
	}

	/**
	 * Remove remaining theme_mods that weren't registered in the Customizer.
	 *
	 * The settings loop above only removes mods registered via $wp_customize->add_setting().
	 * But theme_mods can contain stale keys from demo imports, previously active themes,
	 * or settings the current theme no longer registers. This removes those leftovers
	 * while preserving WordPress internal keys (menus, sidebars, etc.).
	 *
	 * @since 2.1.0
	 */
	$all_mods = get_theme_mods();
	if ( is_array( $all_mods ) ) {
		$preserved_keys = array(
			'nav_menu_locations',
			'sidebars_widgets',
			'custom_css_post_id',
			'custom_logo',
			'wp_classic_sidebars',
		);

		foreach ( $all_mods as $key => $value ) {
			if ( ! in_array( $key, $preserved_keys, true ) ) {
				remove_theme_mod( $key );
			}
		}
	}

	// Reset Additional CSS if checkbox was checked.
	$reset_css = isset( $_POST['reset_css'] ) && '1' === $_POST['reset_css'];
	if ( $reset_css && function_exists( 'wp_get_custom_css_post' ) ) {
		$custom_css_post = wp_get_custom_css_post();
		if ( $custom_css_post ) {
			wp_delete_post( $custom_css_post->ID, true ); // true = force delete, bypass trash.
		}
	}

	wp_send_json_success();
}
