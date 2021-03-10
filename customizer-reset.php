<?php
/**
 * Plugin Name: Customizer Reset
 * Plugin URI: http://wordpress.org/plugins/customizer-reset/
 * Description: Reset theme customizations (theme_mods) made via WordPress Customizer
 * Version: 1.0.1
 * Author: WPZOOM
 * Author URI: http://wpzoom.com/
 * Text Domain: customizer-reset
 * License: GPLv2 or later
 *
 * @package ZOOM_Customizer_Reset
 */

/**
 * Customizer Reset class
 *
 * @since 1.0.0
 */
final class ZOOM_Customizer_Reset {
	/**
	 * Instance of plugin
	 *
	 * @var ZOOM_Customizer_Reset
	 * @access private
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Customizer object
	 *
	 * @var WP_Customize_Manager
	 * @access private
	 * @since 1.0.0
	 */
	private $wp_customize;

	/**
	 * Invoke Singleton instance of plugin.
	 *
	 * @return object
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize actions and filters.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'customize_controls_print_scripts', array( $this, 'customize_controls_print_scripts' ) );
		add_action( 'wp_ajax_customizer_reset', array( $this, 'ajax_customizer_reset' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );
	}

	/**
	 * Enqueue scripts and localizations.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function customize_controls_print_scripts() {
		wp_enqueue_script( 'zoom-customizer-reset', plugins_url( '/js/customizer-reset.js', __FILE__ ), array( 'jquery' ), '20150120', true );
		wp_localize_script(
			'zoom-customizer-reset',
			'_ZoomCustomizerReset',
			array(
				'reset'   => __( 'Reset', 'customizer-reset' ),
				'confirm' => __( "Attention! This will remove all customizations ever made via customizer to this theme!\n\nThis action is irreversible!", 'customizer-reset' ),
				'nonce'   => array(
					'reset' => wp_create_nonce( 'customizer-reset' ),
				),
			)
		);
	}

	/**
	 * Store a reference to `WP_Customize_Manager` instance
	 *
	 * @param object $wp_customize Customizer object.
	 * @return void
	 * @since 1.0.0
	 */
	public function customize_register( $wp_customize ) {
		$this->wp_customize = $wp_customize;
	}

	/**
	 * Run methods if nonce and not in preview mode
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function ajax_customizer_reset() {
		if ( ! $this->wp_customize->is_preview() ) {
			wp_send_json_error( 'not_preview' );
		}

		if ( ! check_ajax_referer( 'customizer-reset', 'nonce', false ) ) {
			wp_send_json_error( 'invalid_nonce' );
		}

		$this->reset_customizer();

		wp_send_json_success();
	}

	/**
	 * Loop through settings found in the Customizer and remove them from database.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function reset_customizer() {
		/**
		 * Filter the settings that will be removed.
		 *
		 * @param array $settings Theme modifications.
		 * @return array
		 * @since 1.1.0
		 */
		$settings = apply_filters( 'customizer_reset_settings', $this->wp_customize->settings() );

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $setting ) {
				if ( 'theme_mod' === $setting->type ) {
					remove_theme_mod( $setting->id );
				}
			}
		}
	}
}

ZOOM_Customizer_Reset::get_instance();
