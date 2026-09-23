<?php
/**
 * Plugin Name: ŠOP SR News Slider
 * Description: Accessible Splide-based news/hero slider for ŠOP SR with Polylang support, responsive design settings, live admin preview and debug logging.
 * Version: 0.1.1
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: ŠOP SR
 * License: GPL-2.0-or-later
 * Text Domain: sopsr-news-slider
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SOPSR_NS_VERSION', '0.1.1' );
define( 'SOPSR_NS_SPLIDE_VERSION', '4.1.4' );
define( 'SOPSR_NS_FILE', __FILE__ );
define( 'SOPSR_NS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SOPSR_NS_URL', plugin_dir_url( __FILE__ ) );

require_once SOPSR_NS_DIR . 'includes/class-sopsr-news-slider-settings.php';
require_once SOPSR_NS_DIR . 'includes/class-sopsr-news-slider-logger.php';
require_once SOPSR_NS_DIR . 'includes/class-sopsr-news-slider-assets.php';
require_once SOPSR_NS_DIR . 'includes/class-sopsr-news-slider-render.php';
require_once SOPSR_NS_DIR . 'includes/class-sopsr-news-slider-admin.php';

final class SOPSR_News_Slider {
	public static function init(): void {
		SOPSR_News_Slider_Logger::init();
		SOPSR_News_Slider_Settings::init();
		SOPSR_News_Slider_Assets::init();
		SOPSR_News_Slider_Render::init();

		if ( is_admin() ) {
			SOPSR_News_Slider_Admin::init();
		}
	}

	public static function activate(): void {
		if ( false === get_option( SOPSR_News_Slider_Settings::OPTION_NAME, false ) ) {
			add_option( SOPSR_News_Slider_Settings::OPTION_NAME, SOPSR_News_Slider_Settings::defaults(), '', false );
		}

		SOPSR_News_Slider_Logger::ensure_log_directory();
		SOPSR_News_Slider_Logger::log( 'info', 'Plugin activated.', array( 'version' => SOPSR_NS_VERSION ) );
	}
}

register_activation_hook( __FILE__, array( 'SOPSR_News_Slider', 'activate' ) );
add_action( 'plugins_loaded', array( 'SOPSR_News_Slider', 'init' ) );
