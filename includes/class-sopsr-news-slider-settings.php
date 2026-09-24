<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SOPSR_News_Slider_Settings {
	public const OPTION_NAME = 'sopsr_news_slider_settings';

	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function register(): void {
		register_setting(
			'sopsr_news_slider_group',
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function defaults(): array {
		$defaults = array(
			'post_count'              => 5,
			'include_categories'      => array(),
			'exclude_categories'      => array(),
			'pinned_post_ids'         => array(),
			'missing_image_mode'      => 'skip',
			'fallback_image_id'       => 0,
			'image_size'              => 'full',
			'image_alt_mode'          => 'media',
			'show_date'               => 0,
			'show_excerpt'            => 0,
			'excerpt_words'           => 24,
			'title_link'              => 0,
			'heading_level'           => 'h2',
			'cta_enabled'             => 1,
			'cta_title_attribute'     => 1,

			'full_bleed'               => 1,
			'content_halign'           => 'center',
			'content_valign'           => 'center',
			'content_max_width'        => 1100,
			'content_max_width_unit'   => 'px',
			'content_padding_x'        => 32,
			'content_padding_x_unit'   => 'px',
			'content_padding_y'        => 32,
			'content_padding_y_unit'   => 'px',

			'tablet_breakpoint'        => 1024,
			'mobile_breakpoint'        => 767,

			'responsive'               => array(
				'desktop' => array(
					'width_value'  => 100,
					'width_unit'   => '%',
					'height_mode'  => 'fixed',
					'height_value' => 620,
					'height_unit'  => 'px',
					'aspect_w'     => 12,
					'aspect_h'     => 5,
					'clamp_min'    => 420,
					'clamp_fluid'  => 36,
					'clamp_max'    => 760,
					'fit'          => 'cover',
					'position_x'   => 50,
					'position_y'   => 50,
					'content_halign'         => 'center',
					'content_valign'         => 'center',
					'content_max_width'      => 1100,
					'content_max_width_unit' => 'px',
					'content_padding_x'      => 32,
					'content_padding_x_unit' => 'px',
					'content_padding_y'      => 32,
					'content_padding_y_unit' => 'px',
					'title_weight'           => 700,
					'title_line_height'      => 1.15,
					'title_max_width'        => 900,
					'title_max_width_unit'   => 'px',
					'title_padding'          => 0,
					'title_padding_unit'     => 'px',
					'title_margin'           => 0,
					'title_margin_unit'      => 'px',
					'title_text_shadow'      => 1,
					'cta_border_width'       => 1,
					'cta_border_width_unit'  => 'px',
					'cta_border_radius'      => 4,
					'cta_border_radius_unit' => 'px',
					'cta_font_size'          => 16,
					'cta_font_size_unit'     => 'px',
					'cta_font_weight'        => 600,
					'cta_padding_y'          => 12,
					'cta_padding_y_unit'     => 'px',
					'cta_padding_x'          => 22,
					'cta_padding_x_unit'     => 'px',
					'controls_size'          => 46,
					'controls_size_unit'     => 'px',
				),
				'tablet' => array(
					'width_value'  => 100,
					'width_unit'   => '%',
					'height_mode'  => 'fixed',
					'height_value' => 480,
					'height_unit'  => 'px',
					'aspect_w'     => 12,
					'aspect_h'     => 5,
					'clamp_min'    => 360,
					'clamp_fluid'  => 50,
					'clamp_max'    => 560,
					'fit'          => 'cover',
					'position_x'   => 50,
					'position_y'   => 50,
					'content_halign'         => 'center',
					'content_valign'         => 'center',
					'content_max_width'      => 1100,
					'content_max_width_unit' => 'px',
					'content_padding_x'      => 32,
					'content_padding_x_unit' => 'px',
					'content_padding_y'      => 32,
					'content_padding_y_unit' => 'px',
					'title_weight'           => 700,
					'title_line_height'      => 1.15,
					'title_max_width'        => 900,
					'title_max_width_unit'   => 'px',
					'title_padding'          => 0,
					'title_padding_unit'     => 'px',
					'title_margin'           => 0,
					'title_margin_unit'      => 'px',
					'title_text_shadow'      => 1,
					'cta_border_width'       => 1,
					'cta_border_width_unit'  => 'px',
					'cta_border_radius'      => 4,
					'cta_border_radius_unit' => 'px',
					'cta_font_size'          => 16,
					'cta_font_size_unit'     => 'px',
					'cta_font_weight'        => 600,
					'cta_padding_y'          => 12,
					'cta_padding_y_unit'     => 'px',
					'cta_padding_x'          => 22,
					'cta_padding_x_unit'     => 'px',
					'controls_size'          => 46,
					'controls_size_unit'     => 'px',
				),
				'mobile' => array(
					'width_value'  => 100,
					'width_unit'   => '%',
					'height_mode'  => 'fixed',
					'height_value' => 340,
					'height_unit'  => 'px',
					'aspect_w'     => 4,
					'aspect_h'     => 5,
					'clamp_min'    => 300,
					'clamp_fluid'  => 90,
					'clamp_max'    => 430,
					'fit'          => 'cover',
					'position_x'   => 50,
					'position_y'   => 50,
					'content_halign'         => 'center',
					'content_valign'         => 'center',
					'content_max_width'      => 1100,
					'content_max_width_unit' => 'px',
					'content_padding_x'      => 32,
					'content_padding_x_unit' => 'px',
					'content_padding_y'      => 32,
					'content_padding_y_unit' => 'px',
					'title_weight'           => 700,
					'title_line_height'      => 1.15,
					'title_max_width'        => 900,
					'title_max_width_unit'   => 'px',
					'title_padding'          => 0,
					'title_padding_unit'     => 'px',
					'title_margin'           => 0,
					'title_margin_unit'      => 'px',
					'title_text_shadow'      => 1,
					'cta_border_width'       => 1,
					'cta_border_width_unit'  => 'px',
					'cta_border_radius'      => 4,
					'cta_border_radius_unit' => 'px',
					'cta_font_size'          => 16,
					'cta_font_size_unit'     => 'px',
					'cta_font_weight'        => 600,
					'cta_padding_y'          => 12,
					'cta_padding_y_unit'     => 'px',
					'cta_padding_x'          => 22,
					'cta_padding_x_unit'     => 'px',
					'controls_size'          => 46,
					'controls_size_unit'     => 'px',
				),
			),

			'image_background_color' => '#111111',
			'lazy_mode'               => 'native',

			'overlay_type'            => 'solid',
			'overlay_color'           => '#000000',
			'overlay_opacity'         => 42,

			'title_color'             => '#ffffff',
			'title_font_mode'         => 'clamp',
			'title_clamp_min'         => 26,
			'title_clamp_min_unit'    => 'px',
			'title_clamp_fluid'       => 3.1,
			'title_clamp_max'         => 52,
			'title_clamp_max_unit'    => 'px',
			'title_desktop'           => 48,
			'title_desktop_unit'      => 'px',
			'title_tablet'            => 40,
			'title_tablet_unit'       => 'px',
			'title_mobile'            => 30,
			'title_mobile_unit'       => 'px',
			'title_weight'            => 700,
			'title_line_height'       => 1.15,
			'title_max_width'         => 900,
			'title_max_width_unit'    => 'px',
			'title_padding'           => 0,
			'title_padding_unit'      => 'px',
			'title_margin'            => 0,
			'title_margin_unit'       => 'px',
			'title_text_shadow'       => 1,

			'cta_text_color'          => '#ffffff',
			'cta_bg_color'            => '#2f6f3e',
			'cta_hover_text_color'    => '#ffffff',
			'cta_hover_bg_color'      => '#245831',
			'cta_focus_text_color'    => '#ffffff',
			'cta_focus_bg_color'      => '#2f6f3e',
			'cta_border_color'        => '#2f6f3e',
			'cta_border_width'        => 1,
			'cta_border_width_unit'   => 'px',
			'cta_border_radius'       => 4,
			'cta_border_radius_unit'  => 'px',
			'cta_font_size'           => 16,
			'cta_font_size_unit'      => 'px',
			'cta_font_weight'         => 600,
			'cta_padding_y'           => 12,
			'cta_padding_y_unit'      => 'px',
			'cta_padding_x'           => 22,
			'cta_padding_x_unit'      => 'px',

			'transition'              => 'fade',
			'loop'                    => 1,
			'rewind'                  => 1,
			'rewind_by_drag'          => 1,
			'speed'                   => 700,
			'rewind_speed'            => 700,
			'autoplay'                => 1,
			'interval'                => 6000,
			'reset_progress'          => 1,
			'progress_bar'            => 0,
			'arrows'                  => 1,
			'pagination'              => 1,
			'drag'                    => 1,
			'keyboard'                => 'focused',
			'wait_for_transition'     => 0,
			'easing'                  => 'cubic-bezier(0.25, 1, 0.5, 1)',
			'start'                   => 0,
			'per_page'                => 1,
			'per_move'                => 1,
			'gap'                     => 0,
			'gap_unit'                => 'px',
			'padding_left'            => 0,
			'padding_right'           => 0,
			'padding_unit'            => 'px',
			'wheel'                   => 0,
			'wheel_sleep'             => 0,
			'release_wheel'           => 1,
			'flick_power'             => 600,
			'flick_max_pages'         => 1,
			'omit_end'                => 0,

			'controls_color'          => '#ffffff',
			'controls_bg_color'       => '#000000',
			'controls_bg_opacity'     => 35,
			'controls_size'           => 46,
			'controls_size_unit'      => 'px',
			'pagination_active_color' => '#ffffff',
			'pagination_color'        => '#ffffff',
			'focus_color'             => '#ffcc00',

			'custom_css'              => '',
			'debug_logging'           => 1,
			'debug_console'           => 1,

			'translations'            => self::translation_defaults(),
		);
		foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$key = $property . '_' . $side;
				$defaults[ $key ] = 0;
				$defaults[ $key . '_unit' ] = 'px';
				foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
					$defaults['responsive'][ $device ][ $key ] = 0;
					$defaults['responsive'][ $device ][ $key . '_unit' ] = 'px';
				}
			}
		}
		return $defaults;
	}

	public static function translation_defaults(): array {
		return array(
			'sk' => array(
				'cta_text'      => 'Viac informácií',
				'cta_prefix'    => 'Viac informácií o aktualite:',
				'slider_label'  => 'Najnovšie aktuality',
				'prev'          => 'Predchádzajúca aktualita',
				'next'          => 'Nasledujúca aktualita',
				'first'         => 'Prejsť na prvú aktualitu',
				'last'          => 'Prejsť na poslednú aktualitu',
				'slideX'        => 'Prejsť na aktualitu %s',
				'pageX'         => 'Prejsť na stránku %s',
				'slideLabel'    => '%s z %s',
				'play'          => 'Spustiť automatické prehrávanie',
				'pause'         => 'Pozastaviť automatické prehrávanie',
				'select'        => 'Vyberte aktualitu',
				'carousel'      => 'karusel',
				'slide'         => 'aktualita',
				'empty_text'    => 'Momentálne nie sú k dispozícii žiadne aktuality.',
			),
			'en' => array(
				'cta_text'      => 'Read more',
				'cta_prefix'    => 'Read more about:',
				'slider_label'  => 'Latest news',
				'prev'          => 'Previous news item',
				'next'          => 'Next news item',
				'first'         => 'Go to the first news item',
				'last'          => 'Go to the last news item',
				'slideX'        => 'Go to news item %s',
				'pageX'         => 'Go to page %s',
				'slideLabel'    => '%s of %s',
				'play'          => 'Start autoplay',
				'pause'         => 'Pause autoplay',
				'select'        => 'Select a news item',
				'carousel'      => 'carousel',
				'slide'         => 'news item',
				'empty_text'    => 'No news items are currently available.',
			),
			'de' => array(
				'cta_text'      => 'Mehr erfahren',
				'cta_prefix'    => 'Mehr Informationen zu:',
				'slider_label'  => 'Aktuelle Nachrichten',
				'prev'          => 'Vorherige Nachricht',
				'next'          => 'Nächste Nachricht',
				'first'         => 'Zur ersten Nachricht',
				'last'          => 'Zur letzten Nachricht',
				'slideX'        => 'Zur Nachricht %s',
				'pageX'         => 'Zur Seite %s',
				'slideLabel'    => '%s von %s',
				'play'          => 'Automatische Wiedergabe starten',
				'pause'         => 'Automatische Wiedergabe pausieren',
				'select'        => 'Nachricht auswählen',
				'carousel'      => 'Karussell',
				'slide'         => 'Nachricht',
				'empty_text'    => 'Derzeit sind keine Nachrichten verfügbar.',
			),
			'pl' => array(
				'cta_text'      => 'Więcej informacji',
				'cta_prefix'    => 'Więcej informacji o aktualności:',
				'slider_label'  => 'Najnowsze aktualności',
				'prev'          => 'Poprzednia aktualność',
				'next'          => 'Następna aktualność',
				'first'         => 'Przejdź do pierwszej aktualności',
				'last'          => 'Przejdź do ostatniej aktualności',
				'slideX'        => 'Przejdź do aktualności %s',
				'pageX'         => 'Przejdź do strony %s',
				'slideLabel'    => '%s z %s',
				'play'          => 'Uruchom automatyczne odtwarzanie',
				'pause'         => 'Wstrzymaj automatyczne odtwarzanie',
				'select'        => 'Wybierz aktualność',
				'carousel'      => 'karuzela',
				'slide'         => 'aktualność',
				'empty_text'    => 'Obecnie nie ma dostępnych aktualności.',
			),
			'hu' => array(
				'cta_text'      => 'További információ',
				'cta_prefix'    => 'További információ erről:',
				'slider_label'  => 'Legfrissebb hírek',
				'prev'          => 'Előző hír',
				'next'          => 'Következő hír',
				'first'         => 'Ugrás az első hírre',
				'last'          => 'Ugrás az utolsó hírre',
				'slideX'        => 'Ugrás a(z) %s. hírre',
				'pageX'         => 'Ugrás a(z) %s. oldalra',
				'slideLabel'    => '%s / %s',
				'play'          => 'Automatikus lejátszás indítása',
				'pause'         => 'Automatikus lejátszás szüneteltetése',
				'select'        => 'Hír kiválasztása',
				'carousel'      => 'lapozó',
				'slide'         => 'hír',
				'empty_text'    => 'Jelenleg nincs elérhető hír.',
			),
		);
	}

	public static function get_all(): array {
		$saved = get_option( self::OPTION_NAME, array() );
		$saved = is_array( $saved ) ? $saved : array();
		$settings = self::merge_recursive( self::defaults(), $saved );
		// Preserve each installation's normal CTA palette until focus colors are saved.
		foreach ( array( 'text', 'bg' ) as $part ) {
			$key = 'cta_focus_' . $part . '_color';
			if ( ! isset( $saved[ $key ] ) ) {
				$settings[ $key ] = $settings[ 'cta_' . $part . '_color' ];
			}
		}
		$responsive_fallbacks = self::responsive_style_fallbacks();
		foreach ( array( 'tablet', 'mobile' ) as $device ) {
			$saved_device = isset( $saved['responsive'][ $device ] ) && is_array( $saved['responsive'][ $device ] ) ? $saved['responsive'][ $device ] : array();
			foreach ( $responsive_fallbacks as $responsive_key => $global_key ) {
				if ( ! array_key_exists( $responsive_key, $saved_device ) ) {
					$settings['responsive'][ $device ][ $responsive_key ] = $settings[ $global_key ];
				}
			}
		}
		foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$key = $property . '_' . $side;
				if ( ! array_key_exists( $key, $saved ) ) {
					$settings[ $key ] = $settings[ $property ];
				}
				if ( ! array_key_exists( $key . '_unit', $saved ) ) {
					$settings[ $key . '_unit' ] = $settings[ $property . '_unit' ];
				}
				foreach ( array( 'tablet', 'mobile' ) as $device ) {
					$saved_device = isset( $saved['responsive'][ $device ] ) && is_array( $saved['responsive'][ $device ] ) ? $saved['responsive'][ $device ] : array();
					if ( ! array_key_exists( $key, $saved_device ) ) {
						$settings['responsive'][ $device ][ $key ] = $saved_device[ $property ] ?? $settings[ $key ];
					}
					if ( ! array_key_exists( $key . '_unit', $saved_device ) ) {
						$settings['responsive'][ $device ][ $key . '_unit' ] = $saved_device[ $property . '_unit' ] ?? $settings[ $key . '_unit' ];
					}
				}
			}
		}
		return $settings;
	}

	public static function get( string $key, $default = null ) {
		$settings = self::get_all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
	}

	public static function current_language(): string {
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'slug' );
			if ( is_string( $lang ) && '' !== $lang ) {
				return sanitize_key( $lang );
			}
		}

		$locale = determine_locale();
		return sanitize_key( strtolower( substr( (string) $locale, 0, 2 ) ) ?: 'sk' );
	}

	public static function languages(): array {
		$languages = array();

		if ( function_exists( 'pll_languages_list' ) ) {
			$slugs = pll_languages_list( array( 'fields' => 'slug' ) );
			$names = pll_languages_list( array( 'fields' => 'name' ) );

			if ( is_array( $slugs ) ) {
				foreach ( array_values( $slugs ) as $index => $slug ) {
					$slug = sanitize_key( (string) $slug );
					if ( '' === $slug ) {
						continue;
					}
					$name = isset( $names[ $index ] ) ? sanitize_text_field( (string) $names[ $index ] ) : strtoupper( $slug );
					$languages[ $slug ] = $name;
				}
			}
		}

		if ( empty( $languages ) ) {
			$slug               = self::current_language();
			$languages[ $slug ] = strtoupper( $slug );
		}

		return $languages;
	}

	public static function text( string $key, ?string $lang = null ): string {
		$settings = self::get_all();
		$lang     = $lang ?: self::current_language();

		if ( isset( $settings['translations'][ $lang ][ $key ] ) && '' !== $settings['translations'][ $lang ][ $key ] ) {
			return (string) $settings['translations'][ $lang ][ $key ];
		}

		$defaults = self::translation_defaults();
		if ( isset( $defaults[ $lang ][ $key ] ) ) {
			return (string) $defaults[ $lang ][ $key ];
		}

		if ( isset( $settings['translations']['sk'][ $key ] ) && '' !== $settings['translations']['sk'][ $key ] ) {
			return (string) $settings['translations']['sk'][ $key ];
		}

		return isset( $defaults['en'][ $key ] ) ? (string) $defaults['en'][ $key ] : '';
	}

	public static function sanitize( $input ): array {
		$defaults = self::defaults();
		$input    = is_array( $input ) ? $input : array();
		$out      = $defaults;

		$out['post_count']          = self::int_range( $input, 'post_count', 1, 30, $defaults['post_count'] );
		$out['excerpt_words']       = self::int_range( $input, 'excerpt_words', 5, 100, $defaults['excerpt_words'] );
		$out['fallback_image_id']   = absint( $input['fallback_image_id'] ?? 0 );
		$out['image_size']          = sanitize_key( $input['image_size'] ?? $defaults['image_size'] );
		$out['missing_image_mode']  = self::enum( $input, 'missing_image_mode', array( 'skip', 'fallback', 'background' ), $defaults['missing_image_mode'] );
		$out['image_alt_mode']      = self::enum( $input, 'image_alt_mode', array( 'media', 'decorative' ), $defaults['image_alt_mode'] );
		$out['lazy_mode']           = self::enum( $input, 'lazy_mode', array( 'native', 'off', 'splide-nearby', 'splide-sequential' ), $defaults['lazy_mode'] );
		$out['heading_level']       = self::enum( $input, 'heading_level', array( 'h2', 'h3', 'h4' ), $defaults['heading_level'] );

		$out['include_categories']  = self::sanitize_id_array( $input['include_categories'] ?? array() );
		$out['exclude_categories']  = self::sanitize_id_array( $input['exclude_categories'] ?? array() );
		$out['pinned_post_ids']     = self::sanitize_id_array( $input['pinned_post_ids'] ?? array() );

		foreach ( array( 'show_date', 'show_excerpt', 'title_link', 'cta_enabled', 'cta_title_attribute', 'full_bleed', 'title_text_shadow', 'loop', 'rewind', 'rewind_by_drag', 'autoplay', 'reset_progress', 'progress_bar', 'arrows', 'pagination', 'drag', 'wait_for_transition', 'wheel', 'release_wheel', 'omit_end', 'debug_logging', 'debug_console' ) as $key ) {
			$out[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
		}

		$out['content_halign'] = self::enum( $input, 'content_halign', array( 'left', 'center', 'right' ), $defaults['content_halign'] );
		$out['content_valign'] = self::enum( $input, 'content_valign', array( 'top', 'center', 'bottom' ), $defaults['content_valign'] );
		foreach ( array(
			'content_max_width'  => array( 0.01, 2400 ),
			'content_padding_x'  => array( 0, 200 ),
			'content_padding_y'  => array( 0, 200 ),
			'title_max_width'    => array( 0.01, 2000 ),
			'title_padding'      => array( 0, 500 ),
			'title_margin'       => array( 0, 500 ),
			'cta_border_width'   => array( 0, 100 ),
			'cta_border_radius'  => array( 0, 200 ),
			'cta_padding_y'      => array( 0, 200 ),
			'cta_padding_x'      => array( 0, 300 ),
			'controls_size'      => array( 0.01, 200 ),
		) as $key => $range ) {
			$out[ $key ] = self::float_range_input( $input, $key, $range[0], $range[1], $defaults[ $key ] );
			$out[ $key . '_unit' ] = self::enum( $input, $key . '_unit', array( 'px', 'rem', 'em' ), 'px' );
		}
		foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$key = $property . '_' . $side;
				$out[ $key ] = self::float_range_value( $input[ $key ] ?? $input[ $property ] ?? 0, 0, 500, 0 );
				$out[ $key . '_unit' ] = self::enum_value( $input[ $key . '_unit' ] ?? $input[ $property . '_unit' ] ?? 'px', array( 'px', 'rem', 'em' ), 'px' );
			}
		}

		$out['tablet_breakpoint'] = self::int_range( $input, 'tablet_breakpoint', 600, 1600, $defaults['tablet_breakpoint'] );
		$out['mobile_breakpoint'] = self::int_range( $input, 'mobile_breakpoint', 320, 1000, $defaults['mobile_breakpoint'] );
		if ( $out['mobile_breakpoint'] >= $out['tablet_breakpoint'] ) {
			$out['mobile_breakpoint'] = max( 320, $out['tablet_breakpoint'] - 1 );
		}

		$out['responsive'] = array();
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$source = isset( $input['responsive'][ $device ] ) && is_array( $input['responsive'][ $device ] ) ? $input['responsive'][ $device ] : array();
			$def    = $defaults['responsive'][ $device ];

			$out['responsive'][ $device ] = array(
				'width_value'  => self::float_range_value( $source['width_value'] ?? $def['width_value'], 1, 3000, $def['width_value'] ),
				'width_unit'   => self::enum_value( $source['width_unit'] ?? $def['width_unit'], array( '%', 'px', 'vw' ), $def['width_unit'] ),
				'height_mode'  => self::enum_value( $source['height_mode'] ?? $def['height_mode'], array( 'fixed', 'auto', 'aspect', 'clamp', 'viewport' ), $def['height_mode'] ),
				'height_value' => self::float_range_value( $source['height_value'] ?? $def['height_value'], 1, 3000, $def['height_value'] ),
				'height_unit'  => self::enum_value( $source['height_unit'] ?? $def['height_unit'], array( 'px', 'vh', 'vw' ), $def['height_unit'] ),
				'aspect_w'     => self::float_range_value( $source['aspect_w'] ?? $def['aspect_w'], 0.1, 100, $def['aspect_w'] ),
				'aspect_h'     => self::float_range_value( $source['aspect_h'] ?? $def['aspect_h'], 0.1, 100, $def['aspect_h'] ),
				'clamp_min'    => self::float_range_value( $source['clamp_min'] ?? $def['clamp_min'], 1, 3000, $def['clamp_min'] ),
				'clamp_fluid'  => self::float_range_value( $source['clamp_fluid'] ?? $def['clamp_fluid'], 1, 200, $def['clamp_fluid'] ),
				'clamp_max'    => self::float_range_value( $source['clamp_max'] ?? $def['clamp_max'], 1, 4000, $def['clamp_max'] ),
				'fit'          => self::enum_value( $source['fit'] ?? $def['fit'], array( 'cover', 'contain', 'fill', 'none', 'scale-down' ), $def['fit'] ),
				'position_x'   => self::float_range_value( $source['position_x'] ?? $def['position_x'], 0, 100, $def['position_x'] ),
				'position_y'   => self::float_range_value( $source['position_y'] ?? $def['position_y'], 0, 100, $def['position_y'] ),
			);

			$out['responsive'][ $device ] += array(
				'content_halign'         => self::enum_value( $source['content_halign'] ?? $out['content_halign'], array( 'left', 'center', 'right' ), $out['content_halign'] ),
				'content_valign'         => self::enum_value( $source['content_valign'] ?? $out['content_valign'], array( 'top', 'center', 'bottom' ), $out['content_valign'] ),
				'title_weight'           => self::enum_value( (string) ( $source['title_weight'] ?? $input['title_weight'] ?? $defaults['title_weight'] ), array( '300', '400', '500', '600', '700', '800', '900' ), (string) $defaults['title_weight'] ),
				'title_line_height'      => self::float_range_value( $source['title_line_height'] ?? $input['title_line_height'] ?? $defaults['title_line_height'], 0.8, 2.5, $defaults['title_line_height'] ),
				'title_text_shadow'      => ! empty( $source['title_text_shadow'] ?? $input['title_text_shadow'] ?? $defaults['title_text_shadow'] ) ? 1 : 0,
				'cta_font_weight'        => self::enum_value( (string) ( $source['cta_font_weight'] ?? $input['cta_font_weight'] ?? $defaults['cta_font_weight'] ), array( '300', '400', '500', '600', '700', '800', '900' ), (string) $defaults['cta_font_weight'] ),
			);
			foreach ( array(
				'content_max_width' => array( 0.01, 2400 ),
				'content_padding_x' => array( 0, 200 ),
				'content_padding_y' => array( 0, 200 ),
				'title_max_width'   => array( 0.01, 2000 ),
				'title_padding'     => array( 0, 500 ),
				'title_margin'      => array( 0, 500 ),
				'cta_border_width'  => array( 0, 100 ),
				'cta_border_radius' => array( 0, 200 ),
				'cta_font_size'     => array( 0.01, 140 ),
				'cta_padding_y'     => array( 0, 200 ),
				'cta_padding_x'     => array( 0, 300 ),
				'controls_size'     => array( 0.01, 200 ),
			) as $key => $range ) {
				$global_default = array_key_exists( $key, $out )
					? $out[ $key ]
					: self::float_range_value( $input[ $key ] ?? $defaults[ $key ], $range[0], $range[1], $defaults[ $key ] );
				$global_unit = array_key_exists( $key . '_unit', $out )
					? $out[ $key . '_unit' ]
					: self::enum_value( $input[ $key . '_unit' ] ?? $defaults[ $key . '_unit' ] ?? 'px', array( 'px', 'rem', 'em' ), 'px' );
				$out['responsive'][ $device ][ $key ] = self::float_range_value( $source[ $key ] ?? $global_default, $range[0], $range[1], $global_default );
				$out['responsive'][ $device ][ $key . '_unit' ] = self::enum_value( $source[ $key . '_unit' ] ?? $global_unit, array( 'px', 'rem', 'em' ), 'px' );
			}
			foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
				foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
					$key = $property . '_' . $side;
					$out['responsive'][ $device ][ $key ] = self::float_range_value( $source[ $key ] ?? $source[ $property ] ?? $out[ $key ], 0, 500, $out[ $key ] );
					$out['responsive'][ $device ][ $key . '_unit' ] = self::enum_value( $source[ $key . '_unit' ] ?? $source[ $property . '_unit' ] ?? $out[ $key . '_unit' ], array( 'px', 'rem', 'em' ), 'px' );
				}
			}
		}

		foreach ( array(
			'image_background_color',
			'overlay_color',
			'title_color',
			'cta_text_color',
			'cta_bg_color',
			'cta_hover_text_color',
			'cta_hover_bg_color',
			'cta_focus_text_color',
			'cta_focus_bg_color',
			'cta_border_color',
			'controls_color',
			'controls_bg_color',
			'pagination_active_color',
			'pagination_color',
			'focus_color',
		) as $key ) {
			$out[ $key ] = sanitize_hex_color( $input[ $key ] ?? $defaults[ $key ] ) ?: $defaults[ $key ];
		}

		$out['overlay_type']        = self::enum( $input, 'overlay_type', array( 'none', 'solid', 'gradient-top-bottom', 'gradient-bottom-top' ), $defaults['overlay_type'] );
		$out['overlay_opacity']     = self::int_range( $input, 'overlay_opacity', 0, 100, $defaults['overlay_opacity'] );
		$out['controls_bg_opacity'] = self::int_range( $input, 'controls_bg_opacity', 0, 100, $defaults['controls_bg_opacity'] );

		foreach ( array( 'title_clamp_min', 'title_clamp_max', 'title_desktop', 'title_tablet', 'title_mobile', 'cta_font_size' ) as $key ) {
			$out[ $key . '_unit' ] = self::enum( $input, $key . '_unit', array( 'px', 'rem', 'em' ), 'px' );
			$out[ $key ] = self::float_range_input( $input, $key, 0.01, 140, $defaults[ $key ] );
		}
		foreach ( array( 'text', 'bg' ) as $part ) {
			$key = 'cta_focus_' . $part . '_color';
			if ( ! isset( $input[ $key ] ) ) {
				$out[ $key ] = $out[ 'cta_' . $part . '_color' ];
			}
		}

		$out['title_font_mode']   = self::enum( $input, 'title_font_mode', array( 'clamp', 'responsive' ), $defaults['title_font_mode'] );
		$out['title_clamp_fluid'] = self::float_range_input( $input, 'title_clamp_fluid', 0.1, 20, $defaults['title_clamp_fluid'] );
		$out['title_weight']      = self::enum( $input, 'title_weight', array( '300', '400', '500', '600', '700', '800', '900' ), (string) $defaults['title_weight'] );
		$out['title_line_height'] = self::float_range_input( $input, 'title_line_height', 0.8, 2.5, $defaults['title_line_height'] );

		foreach ( array(
			'speed'             => array( 0, 10000 ),
			'rewind_speed'      => array( 0, 10000 ),
			'interval'          => array( 1000, 60000 ),
			'start'             => array( 0, 100 ),
			'per_page'          => array( 1, 10 ),
			'per_move'          => array( 1, 10 ),
			'wheel_sleep'       => array( 0, 5000 ),
			'flick_power'       => array( 0, 2000 ),
			'flick_max_pages'   => array( 1, 10 ),
		) as $key => $range ) {
			$out[ $key ] = self::int_range( $input, $key, $range[0], $range[1], $defaults[ $key ] );
		}

		$out['cta_font_weight'] = self::enum( $input, 'cta_font_weight', array( '300', '400', '500', '600', '700', '800', '900' ), (string) $defaults['cta_font_weight'] );
		$out['transition']      = self::enum( $input, 'transition', array( 'slide', 'fade' ), $defaults['transition'] );
		$out['keyboard']        = self::enum( $input, 'keyboard', array( 'off', 'focused', 'global' ), $defaults['keyboard'] );
		$out['easing']          = sanitize_text_field( $input['easing'] ?? $defaults['easing'] );
		$out['gap']             = self::float_range_input( $input, 'gap', 0, 500, $defaults['gap'] );
		$out['gap_unit']        = self::enum( $input, 'gap_unit', array( 'px', 'rem', 'vw' ), $defaults['gap_unit'] );
		$out['padding_left']    = self::float_range_input( $input, 'padding_left', 0, 1000, $defaults['padding_left'] );
		$out['padding_right']   = self::float_range_input( $input, 'padding_right', 0, 1000, $defaults['padding_right'] );
		$out['padding_unit']    = self::enum( $input, 'padding_unit', array( 'px', 'rem', 'vw' ), $defaults['padding_unit'] );

		$css = isset( $input['custom_css'] ) ? wp_unslash( (string) $input['custom_css'] ) : '';
		$css = preg_replace( '/<\s*\/?\s*(?:style|script)\b[^>]*>/i', '', $css );
		$css = is_string( $css ) ? $css : '';
		$out['custom_css'] = trim( $css );

		$out['translations'] = $defaults['translations'];
		if ( isset( $input['translations'] ) && is_array( $input['translations'] ) ) {
			foreach ( $input['translations'] as $lang => $strings ) {
				$lang = sanitize_key( (string) $lang );
				if ( '' === $lang || ! is_array( $strings ) ) {
					continue;
				}
				foreach ( array( 'cta_text', 'cta_prefix', 'slider_label', 'prev', 'next', 'first', 'last', 'slideX', 'pageX', 'slideLabel', 'play', 'pause', 'select', 'carousel', 'slide', 'empty_text' ) as $key ) {
					if ( array_key_exists( $key, $strings ) ) {
						$out['translations'][ $lang ][ $key ] = sanitize_text_field( (string) $strings[ $key ] );
					}
				}
			}
		}

		return $out;
	}

	private static function merge_recursive( array $defaults, array $saved ): array {
		foreach ( $saved as $key => $value ) {
			if ( isset( $defaults[ $key ] ) && is_array( $defaults[ $key ] ) && is_array( $value ) ) {
				$defaults[ $key ] = self::merge_recursive( $defaults[ $key ], $value );
			} else {
				$defaults[ $key ] = $value;
			}
		}
		return $defaults;
	}

	private static function responsive_style_fallbacks(): array {
		return array(
			'content_halign'         => 'content_halign',
			'content_valign'         => 'content_valign',
			'content_max_width'      => 'content_max_width',
			'content_max_width_unit' => 'content_max_width_unit',
			'content_padding_x'      => 'content_padding_x',
			'content_padding_x_unit' => 'content_padding_x_unit',
			'content_padding_y'      => 'content_padding_y',
			'content_padding_y_unit' => 'content_padding_y_unit',
			'title_weight'           => 'title_weight',
			'title_line_height'      => 'title_line_height',
			'title_max_width'        => 'title_max_width',
			'title_max_width_unit'   => 'title_max_width_unit',
			'title_padding'          => 'title_padding',
			'title_padding_unit'     => 'title_padding_unit',
			'title_margin'           => 'title_margin',
			'title_margin_unit'      => 'title_margin_unit',
			'title_text_shadow'      => 'title_text_shadow',
			'cta_border_width'       => 'cta_border_width',
			'cta_border_width_unit'  => 'cta_border_width_unit',
			'cta_border_radius'      => 'cta_border_radius',
			'cta_border_radius_unit' => 'cta_border_radius_unit',
			'cta_font_size'          => 'cta_font_size',
			'cta_font_size_unit'     => 'cta_font_size_unit',
			'cta_font_weight'        => 'cta_font_weight',
			'cta_padding_y'          => 'cta_padding_y',
			'cta_padding_y_unit'     => 'cta_padding_y_unit',
			'cta_padding_x'          => 'cta_padding_x',
			'cta_padding_x_unit'     => 'cta_padding_x_unit',
			'controls_size'          => 'controls_size',
			'controls_size_unit'     => 'controls_size_unit',
		);
	}

	private static function sanitize_id_array( $value ): array {
		if ( is_string( $value ) ) {
			$value = preg_split( '/[\s,]+/', $value );
		}
		$value = is_array( $value ) ? $value : array();
		$value = array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );
		return $value;
	}

	private static function int_range( array $input, string $key, int $min, int $max, int $default ): int {
		$value = isset( $input[ $key ] ) ? (int) $input[ $key ] : $default;
		return max( $min, min( $max, $value ) );
	}

	private static function float_range_input( array $input, string $key, float $min, float $max, float $default ): float {
		return self::float_range_value( $input[ $key ] ?? $default, $min, $max, $default );
	}

	private static function float_range_value( $value, float $min, float $max, float $default ): float {
		if ( ! is_numeric( $value ) ) {
			return $default;
		}
		$value = (float) $value;
		return max( $min, min( $max, $value ) );
	}

	private static function enum( array $input, string $key, array $allowed, $default ) {
		return self::enum_value( $input[ $key ] ?? $default, $allowed, $default );
	}

	private static function enum_value( $value, array $allowed, $default ) {
		return in_array( $value, $allowed, true ) ? $value : $default;
	}
}
