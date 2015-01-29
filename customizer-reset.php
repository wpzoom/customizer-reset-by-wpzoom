<?php

/*
Plugin Name: Customizer Reset
Plugin URI: http://wordpress.org/plugins/customizer-reset/
Description: Reset theme customizations (theme_mods) made via WordPress Customizer
Version: 1.0.1
Author: WPZOOM
Author URI: http://wpzoom.com/
License: GPLv2 or later
*/


if ( ! class_exists( 'ZOOM_Customizer_Reset' ) ) {
	final class ZOOM_Customizer_Reset {
		/**
		 * @var ZOOM_Customizer_Reset
		 */
		private static $instance = null;

		/**
		 * @var WP_Customize_Manager
		 */
		private $wp_customize;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'customize_controls_print_scripts', array( $this, 'customize_controls_print_scripts' ) );
			add_action( 'wp_ajax_customizer_reset', array( $this, 'ajax_customizer_reset' ) );
			add_action( 'customize_register', array( $this, 'customize_register' ) );
		}

		public function customize_controls_print_scripts() {
			wp_enqueue_script( 'zoom-customizer-reset', plugins_url( '/js/customizer-reset.js', __FILE__ ), array( 'jquery' ), '20150120' );
			wp_localize_script( 'zoom-customizer-reset', '_ZoomCustomizerReset', array(
				'reset'   => __( 'Reset', 'customizer-reset' ),
				'confirm' => __( "Attention! This will remove all customizations ever made via customizer to this theme!\n\nThis action is irreversible!", 'customizer-reset' ),
				'nonce'   => array(
					'reset' => wp_create_nonce( 'customizer-reset' ),
				)
			) );
		}

		/**
		 * Store a reference to `WP_Customize_Manager` instance
		 *
		 * @param $wp_customize
		 */
		public function customize_register( $wp_customize ) {
			$this->wp_customize = $wp_customize;
		}

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

		public function reset_customizer() {
			$settings = $this->wp_customize->settings();

			// remove theme_mod settings registered in customizer
			foreach ( $settings as $setting ) {
				if ( 'theme_mod' == $setting->type ) {
					remove_theme_mod( $setting->id );
				}
			}
		}
	}
}

ZOOM_Customizer_Reset::get_instance();
