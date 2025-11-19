<?php
/**
 * Plugin Name: Customizer Backup & Reset
 * Plugin URI: http://wordpress.org/plugins/customizer-reset/
 * Description: Reset theme customizations (theme_mods) made via WordPress Customizer with backup and export features
 * Version: 2.0.0
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * Text Domain: customizer-reset-by-wpzoom
 * License: GPLv3 or later
 *
 * @package WPZOOM_Customizer_Reset
 */

namespace WPZOOM_Customizer_Reset;

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

			<?php if ( $backup_count > 0 ) : ?>
				<div class="zoom-backup-actions">
					<button type="button" class="button button-small button-link-delete zoom-delete-all-backups">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Delete All Backups', 'customizer-reset-by-wpzoom' ); ?>
					</button>
				</div>
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
	$js_file  = plugin_dir_path( __FILE__ ) . 'assets/js/customizer-reset.js';
	$css_file = plugin_dir_path( __FILE__ ) . 'assets/css/customizer-reset.css';

	// Enqueue CSS.
	wp_enqueue_style(
		'zoom-customizer-reset',
		plugins_url( '/assets/css/customizer-reset.css', __FILE__ ),
		array(),
		file_exists( $css_file ) ? strval( filemtime( $css_file ) ) : '1.2.0'
	);

	// Enqueue JavaScript.
	wp_enqueue_script(
		'zoom-customizer-reset',
		plugins_url( '/assets/js/customizer-reset.js', __FILE__ ),
		array( 'jquery' ),
		file_exists( $js_file ) ? strval( filemtime( $js_file ) ) : '1.2.0',
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
				'deleteAll'  => wp_create_nonce( 'customizer-delete-all-backups' ),
			),
		)
	);
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
	// Get all theme modifications.
	$theme_mods = get_theme_mods();
	$theme      = wp_get_theme();

	$export_data = array(
		'theme'      => $theme->get( 'Name' ),
		'version'    => $theme->get( 'Version' ),
		'stylesheet' => get_stylesheet(),
		'template'   => get_template(),
		'exported'   => current_time( 'mysql' ),
		'mods'       => $theme_mods,
	);

	// Include Additional CSS in JSON export if available.
	if ( function_exists( 'wp_get_custom_css' ) ) {
		$export_data['wp_css'] = wp_get_custom_css();
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

	// Build data structure matching Customizer Export/Import plugin.
	$data = array(
		'template' => $template,
		'mods'     => $mods ? $mods : array(),
		'options'  => array(),
	);

	// Get options from the Customizer API (matching their approach).
	if ( $wp_customize ) {
		$settings = $wp_customize->settings();

		foreach ( $settings as $key => $setting ) {
			if ( 'option' === $setting->type ) {
				// Don't save widget data.
				if ( 'widget_' === substr( strtolower( $key ), 0, 7 ) ) {
					continue;
				}

				// Don't save sidebar data.
				if ( 'sidebars_' === substr( strtolower( $key ), 0, 9 ) ) {
					continue;
				}

				// Don't save core options.
				$core_options = array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' );
				if ( in_array( $key, $core_options, true ) ) {
					continue;
				}

				$data['options'][ $key ] = $setting->value();
			}
		}
	}

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
	if ( ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Verify nonce.
	if ( ! check_ajax_referer( 'customizer-backup', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Get all theme modifications.
	$theme_mods = get_theme_mods();
	$theme      = wp_get_theme();

	$backup_data = array(
		'theme'      => $theme->get( 'Name' ),
		'version'    => $theme->get( 'Version' ),
		'stylesheet' => get_stylesheet(),
		'created'    => current_time( 'mysql' ),
		'mods'       => $theme_mods,
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
			'count'   => count( $theme_mods ),
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
	if ( ! $wp_customize->is_preview() ) {
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

	// Import custom options if available (DAT format).
	if ( isset( $import_data['options'] ) && is_array( $import_data['options'] ) ) {
		foreach ( $import_data['options'] as $option_key => $option_value ) {
			// Skip if it's a core WordPress option we shouldn't import.
			$core_options = array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' );
			if ( in_array( $option_key, $core_options, true ) ) {
				continue;
			}
			// Update the option.
			update_option( sanitize_key( $option_key ), $option_value );
		}
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
	if ( ! $wp_customize->is_preview() ) {
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
	if ( ! $wp_customize->is_preview() ) {
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
	if ( ! $wp_customize->is_preview() ) {
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
	if ( ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Bail early if nonce is invalid.
	if ( ! check_ajax_referer( 'customizer-reset', 'nonce', false ) ) {
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
