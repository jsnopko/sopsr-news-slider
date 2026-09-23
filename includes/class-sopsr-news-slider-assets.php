<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SOPSR_News_Slider_Assets {
	private const CACHE_DIR = 'sopsr-news-slider-assets';

	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_frontend' ) );
	}

	public static function maybe_enqueue_frontend(): void {
		if ( is_admin() ) {
			return;
		}

		$should_enqueue = is_front_page();

		if ( ! $should_enqueue && is_singular() ) {
			$post = get_post();
			if ( $post && has_shortcode( (string) $post->post_content, 'sopsr_news_slider' ) ) {
				$should_enqueue = true;
			}
		}

		if ( $should_enqueue ) {
			self::enqueue_frontend();
		}
	}

	public static function enqueue_frontend(): void {
		$urls = self::splide_urls();

		wp_enqueue_style(
			'sopsr-splide-core',
			$urls['css'],
			array(),
			SOPSR_NS_SPLIDE_VERSION
		);

		wp_enqueue_style(
			'sopsr-news-slider',
			SOPSR_NS_URL . 'assets/css/frontend.css',
			array( 'sopsr-splide-core' ),
			SOPSR_NS_VERSION
		);

		wp_enqueue_script(
			'sopsr-splide',
			$urls['js'],
			array(),
			SOPSR_NS_SPLIDE_VERSION,
			true
		);

		wp_enqueue_script(
			'sopsr-news-slider',
			SOPSR_NS_URL . 'assets/js/frontend.js',
			array( 'sopsr-splide' ),
			SOPSR_NS_VERSION,
			true
		);
	}

	public static function splide_urls(): array {
		$bundled_js  = SOPSR_NS_DIR . 'assets/vendor/splide/splide.min.js';
		$bundled_css = SOPSR_NS_DIR . 'assets/vendor/splide/splide-core.min.css';

		if ( self::valid_vendor_file( $bundled_js, 10000 ) && self::valid_vendor_file( $bundled_css, 1000 ) ) {
			return array(
				'js'     => SOPSR_NS_URL . 'assets/vendor/splide/splide.min.js',
				'css'    => SOPSR_NS_URL . 'assets/vendor/splide/splide-core.min.css',
				'source' => 'bundled',
			);
		}

		$cache = self::cache_paths();
		if ( self::valid_vendor_file( $cache['js_path'], 10000 ) && self::valid_vendor_file( $cache['css_path'], 1000 ) ) {
			return array(
				'js'     => $cache['js_url'],
				'css'    => $cache['css_url'],
				'source' => 'local-cache',
			);
		}

		return array(
			'js'     => 'https://cdn.jsdelivr.net/npm/@splidejs/splide@' . rawurlencode( SOPSR_NS_SPLIDE_VERSION ) . '/dist/js/splide.min.js',
			'css'    => 'https://cdn.jsdelivr.net/npm/@splidejs/splide@' . rawurlencode( SOPSR_NS_SPLIDE_VERSION ) . '/dist/css/splide-core.min.css',
			'source' => 'cdn',
		);
	}

	public static function download_splide_locally() {
		$cache = self::cache_paths();
		if ( ! wp_mkdir_p( $cache['dir'] ) ) {
			return new WP_Error( 'mkdir_failed', 'Nepodarilo sa vytvoriť adresár pre lokálnu kópiu Splide.' );
		}

		$js_url  = 'https://cdn.jsdelivr.net/npm/@splidejs/splide@' . rawurlencode( SOPSR_NS_SPLIDE_VERSION ) . '/dist/js/splide.min.js';
		$css_url = 'https://cdn.jsdelivr.net/npm/@splidejs/splide@' . rawurlencode( SOPSR_NS_SPLIDE_VERSION ) . '/dist/css/splide-core.min.css';

		$js = wp_safe_remote_get(
			$js_url,
			array(
				'timeout'     => 25,
				'redirection' => 3,
			)
		);

		if ( is_wp_error( $js ) ) {
			return $js;
		}

		$css = wp_safe_remote_get(
			$css_url,
			array(
				'timeout'     => 25,
				'redirection' => 3,
			)
		);

		if ( is_wp_error( $css ) ) {
			return $css;
		}

		$js_body  = wp_remote_retrieve_body( $js );
		$css_body = wp_remote_retrieve_body( $css );

		// Source maps are not needed in production and would create avoidable 404 warnings
		// when only the minified vendor files are cached locally.
		$js_body  = preg_replace( '/^\s*\/\/[#@]\s*sourceMappingURL=.*$/mi', '', $js_body );
		$css_body = preg_replace( '/\/\*[#@]\s*sourceMappingURL=.*?\*\//is', '', $css_body );
		$js_body  = is_string( $js_body ) ? $js_body : '';
		$css_body = is_string( $css_body ) ? $css_body : '';

		if ( 200 !== (int) wp_remote_retrieve_response_code( $js ) || strlen( $js_body ) < 10000 || false === strpos( $js_body, 'Splide.js' ) ) {
			return new WP_Error( 'invalid_js', 'Stiahnutý Splide JavaScript nevyzerá ako platný súbor.' );
		}

		if ( 200 !== (int) wp_remote_retrieve_response_code( $css ) || strlen( $css_body ) < 1000 || false === strpos( $css_body, 'splide__' ) ) {
			return new WP_Error( 'invalid_css', 'Stiahnutý Splide CSS nevyzerá ako platný súbor.' );
		}

		if ( false === file_put_contents( $cache['js_path'], $js_body, LOCK_EX ) ) {
			return new WP_Error( 'write_js_failed', 'Nepodarilo sa uložiť Splide JavaScript.' );
		}

		if ( false === file_put_contents( $cache['css_path'], $css_body, LOCK_EX ) ) {
			return new WP_Error( 'write_css_failed', 'Nepodarilo sa uložiť Splide CSS.' );
		}

		file_put_contents(
			$cache['dir'] . '/VERSION.txt',
			"Splide " . SOPSR_NS_SPLIDE_VERSION . "\nDownloaded from jsDelivr.\n",
			LOCK_EX
		);

		SOPSR_News_Slider_Logger::log( 'info', 'Splide downloaded to local uploads cache.', array( 'version' => SOPSR_NS_SPLIDE_VERSION ) );

		return true;
	}

	public static function cache_paths(): array {
		$upload = wp_upload_dir();
		$dir    = trailingslashit( $upload['basedir'] ) . self::CACHE_DIR . '/splide-' . SOPSR_NS_SPLIDE_VERSION;
		$url    = trailingslashit( $upload['baseurl'] ) . self::CACHE_DIR . '/splide-' . SOPSR_NS_SPLIDE_VERSION;

		return array(
			'dir'      => $dir,
			'js_path'  => $dir . '/splide.min.js',
			'css_path' => $dir . '/splide-core.min.css',
			'js_url'   => $url . '/splide.min.js',
			'css_url'  => $url . '/splide-core.min.css',
		);
	}

	private static function valid_vendor_file( string $path, int $min_bytes ): bool {
		return is_readable( $path ) && filesize( $path ) >= $min_bytes;
	}
}
