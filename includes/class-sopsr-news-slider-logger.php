<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SOPSR_News_Slider_Logger {
	private const DIR_NAME  = 'sopsr-news-slider-private';
	private const FILE_NAME = 'sopsr-news-slider.log';

	public static function init(): void {
		register_shutdown_function( array( __CLASS__, 'capture_fatal' ) );
	}

	public static function capture_fatal(): void {
		$error = error_get_last();
		if ( ! is_array( $error ) ) {
			return;
		}

		$fatal_types = array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR );
		if ( ! in_array( (int) ( $error['type'] ?? 0 ), $fatal_types, true ) ) {
			return;
		}

		$file = (string) ( $error['file'] ?? '' );
		if ( '' === $file || 0 !== strpos( wp_normalize_path( $file ), wp_normalize_path( SOPSR_NS_DIR ) ) ) {
			return;
		}

		self::log(
			'critical',
			'Fatal PHP error in slider plugin.',
			array(
				'file'    => $file,
				'line'    => (int) ( $error['line'] ?? 0 ),
				'message' => (string) ( $error['message'] ?? '' ),
			)
		);
	}

	public static function ensure_log_directory(): bool {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$dir = trailingslashit( $upload['basedir'] ) . self::DIR_NAME;

		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$rules = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
			@file_put_contents( $htaccess, $rules ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			@file_put_contents( $index, "<?php\n// Silence is golden.\n" ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		return is_writable( $dir );
	}

	public static function path(): string {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return '';
		}
		return trailingslashit( $upload['basedir'] ) . self::DIR_NAME . '/' . self::FILE_NAME;
	}

	public static function enabled(): bool {
		$settings = SOPSR_News_Slider_Settings::get_all();
		return ! empty( $settings['debug_logging'] );
	}

	public static function log( string $level, string $message, array $context = array() ): void {
		if ( ! self::enabled() && 'error' !== strtolower( $level ) ) {
			return;
		}

		$level   = strtoupper( sanitize_key( $level ) ?: 'INFO' );
		$message = wp_strip_all_tags( $message );
		$context = self::sanitize_context( $context );

		$line = sprintf(
			"[%s] [%s] %s%s\n",
			wp_date( 'Y-m-d H:i:s' ),
			$level,
			$message,
			empty( $context ) ? '' : ' ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		);

		if ( self::ensure_log_directory() ) {
			$path = self::path();
			if ( $path ) {
				$result = @file_put_contents( $path, $line, FILE_APPEND | LOCK_EX ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if ( false !== $result ) {
					return;
				}
			}
		}

		error_log( '[SOPSR News Slider] ' . trim( $line ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	public static function tail( int $lines = 120 ): string {
		$path = self::path();
		if ( ! $path || ! is_readable( $path ) ) {
			return '';
		}

		$content = file( $path, FILE_IGNORE_NEW_LINES );
		if ( ! is_array( $content ) ) {
			return '';
		}

		return implode( "\n", array_slice( $content, -1 * max( 1, $lines ) ) );
	}

	public static function clear(): bool {
		$path = self::path();
		if ( ! $path ) {
			return false;
		}

		if ( ! file_exists( $path ) ) {
			return true;
		}

		return false !== @file_put_contents( $path, '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	private static function sanitize_context( array $context ): array {
		$out = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( (string) $key );
			if ( '' === $key ) {
				continue;
			}

			if ( is_scalar( $value ) || null === $value ) {
				$text = is_bool( $value ) ? ( $value ? 'true' : 'false' ) : (string) $value;
				$out[ $key ] = substr( wp_strip_all_tags( $text ), 0, 500 );
			} elseif ( is_array( $value ) ) {
				$out[ $key ] = array_slice( array_map( 'strval', $value ), 0, 30 );
			}
		}

		return $out;
	}
}
