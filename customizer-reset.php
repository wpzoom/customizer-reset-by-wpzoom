<?php
/**
 * Plugin Name: Customizer Reset
 * Plugin URI: http://wordpress.org/plugins/customizer-reset/
 * Description: Reset theme customizations (theme_mods) made via WordPress Customizer
 * Version: 1.1.0
 * Author: WPZOOM
 * Author URI: http://wpzoom.com/
 * Text Domain: customizer-reset
 * License: GPLv2 or later
 *
 * @package ZOOM_Customizer_Reset
 */

namespace ZOOM_Customizer_Reset;

add_action( 'customize_controls_print_scripts', __NAMESPACE__ . '\enqueue_scripts' );
/**
 * Enqueue scripts and localizations.
 *
 * @return void
 * @since 1.0.0
 */
function enqueue_scripts() {
	wp_enqueue_script(
		'zoom-customizer-reset',
		plugins_url( '/assets/js/customizer-reset.js', __FILE__ ),
		array( 'jquery' ),
		'20150120',
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

	// Bail early if we are in preview mode.
	if ( ! $wp_customize->is_preview() ) {
		wp_send_json_error( 'not_preview' );
	}

	// Bail early if nonce is invalid.
	if ( ! check_ajax_referer( 'customizer-reset', 'nonce', false ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}

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
	$theme               = wp_get_theme(); // gets the current theme.
	$themename           = strtolower( $theme->name );
	$customizer_settings = get_option( "theme_mods_{$themename}" );
	$theme_options       = get_option( "et_{$themename}" );

	if ( 'divi' === $themename ) {
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
