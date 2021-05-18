<?php
/**
 * Plugin Name: Customizer Reset
 * Plugin URI: http://wordpress.org/plugins/customizer-reset/
 * Description: Reset theme customizations (theme_mods) made via WordPress Customizer
 * Version: 1.1.1
 * Author: WPZOOM
 * Author URI: http://wpzoom.com/
 * Text Domain: customizer-reset
 * License: GPLv2 or later
 *
 * @package WPZOOM_Customizer_Reset
 */

namespace WPZOOM_Customizer_Reset;

add_action( 'customize_controls_print_scripts', __NAMESPACE__ . '\enqueue_scripts' );
/**
 * Enqueue scripts and localizations.
 *
 * @return void
 * @since 1.0.0
 */
function enqueue_scripts() {
	$file_mod_time = strval( filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/customizer-reset.js' ) );

	wp_enqueue_script(
		'zoom-customizer-reset',
		plugins_url( '/assets/js/customizer-reset.js', __FILE__ ),
		array( 'jquery' ),
		$file_mod_time,
		false
	);
	wp_localize_script(
		'zoom-customizer-reset',
		'_ZoomCustomizerReset',
		array(
			'reset'   => __( 'Reset', 'customizer-reset' ),
			'confirm' => __( "Attention!\n\nThis will remove all customizations ever made via customizer to this theme.\n\nThis action is irreversible.", 'customizer-reset' ),
			'nonce'   => array(
				'reset' => wp_create_nonce( 'customizer-reset' ),
			),
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
	global $wp_customize, $options;

	// Bail early if we are not in preview mode.
	if ( ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Bail early if nonce is invalid.
	if ( ! check_ajax_referer( 'customizer-reset', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

	// Gets the current theme.
	$theme     = wp_get_theme();
	$themename = strtolower( $theme->name );

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

		if ( $options ) {
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
			$theme_options = get_option( ASTRA_THEME_SETTINGS );
			$auto_version  = false;

			if ( isset( $theme_options['theme-auto-version'] ) ) {
				$auto_version = $theme_options['theme-auto-version'];
			}
			if ( isset( $theme_options['astra-addon-auto-version'] ) ) {
				$auto_version = $theme_options['astra-addon-auto-version'];
			}

			if ( false !== $auto_version ) {
				update_option( ASTRA_THEME_SETTINGS, $auto_version );
			} else {
				delete_option( ASTRA_THEME_SETTINGS );
			}
		}
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

	wp_send_json_success();
}
