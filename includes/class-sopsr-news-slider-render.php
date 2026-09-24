<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SOPSR_News_Slider_Render {
	private static int $instance = 0;

	public static function init(): void {
		add_shortcode( 'sopsr_news_slider', array( __CLASS__, 'shortcode' ) );
	}

	public static function shortcode( $atts = array() ): string {
		self::$instance++;
		SOPSR_News_Slider_Assets::enqueue_frontend();

		$settings = SOPSR_News_Slider_Settings::get_all();
		$lang     = SOPSR_News_Slider_Settings::current_language();

		$atts = shortcode_atts(
			array(
				'id'    => '',
				'class' => '',
			),
			(array) $atts,
			'sopsr_news_slider'
		);

		$root_id = sanitize_html_class( (string) $atts['id'] );
		if ( '' === $root_id ) {
			$root_id = 1 === self::$instance ? 'sopsr-news-slider' : 'sopsr-news-slider-' . self::$instance;
		}

		$posts = self::query_posts( $settings, $lang );

		SOPSR_News_Slider_Logger::log(
			'info',
			'Slider render.',
			array(
				'language' => $lang,
				'requested' => (int) $settings['post_count'],
				'returned' => count( $posts ),
				'root_id'   => $root_id,
			)
		);

		if ( empty( $posts ) ) {
			return sprintf(
				'<div class="sopsr-news-slider__empty">%s</div>',
				esc_html( SOPSR_News_Slider_Settings::text( 'empty_text', $lang ) )
			);
		}

		$count      = count( $posts );
		$config     = self::splide_config( $settings, $lang, $count );
		$dynamic_css = self::dynamic_css( $root_id, $settings );

		$classes = array( 'splide', 'sopsr-news-slider' );
		if ( ! empty( $settings['full_bleed'] ) ) {
			$classes[] = 'sopsr-news-slider--full-bleed';
		}
		if ( ! empty( $atts['class'] ) ) {
			foreach ( preg_split( '/\s+/', (string) $atts['class'] ) as $class ) {
				$class = sanitize_html_class( $class );
				if ( $class ) {
					$classes[] = $class;
				}
			}
		}

		ob_start();
		?>
		<style id="<?php echo esc_attr( $root_id ); ?>-dynamic-css"><?php echo $dynamic_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></style>
		<section
			id="<?php echo esc_attr( $root_id ); ?>"
			class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>"
			aria-label="<?php echo esc_attr( SOPSR_News_Slider_Settings::text( 'slider_label', $lang ) ); ?>"
		>
			<?php /* Keep controls before the track in DOM order for predictable keyboard navigation. */ ?>
			<?php if ( $count > 1 && ! empty( $settings['autoplay'] ) ) : ?>
				<div class="sopsr-news-slider__autoplay">
					<button class="splide__toggle sopsr-news-slider__toggle" type="button">
						<span class="splide__toggle__play" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M8 5v14l11-7z"/></svg>
						</span>
						<span class="splide__toggle__pause" aria-hidden="true">
							<svg viewBox="0 0 24 24" focusable="false"><path d="M7 5h4v14H7zm6 0h4v14h-4z"/></svg>
						</span>
					</button>
				</div>
			<?php endif; ?>

			<?php if ( $count > 1 && ! empty( $settings['arrows'] ) ) : ?>
				<div class="splide__arrows sopsr-news-slider__arrows">
					<button class="splide__arrow splide__arrow--prev" type="button">
						<span class="sopsr-news-slider__sr-only"><?php echo esc_html( SOPSR_News_Slider_Settings::text( 'prev', $lang ) ); ?></span>
						<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="M15.5 4.5 8 12l7.5 7.5-1.4 1.4L5.2 12l8.9-8.9z"/></svg>
					</button>
					<button class="splide__arrow splide__arrow--next" type="button">
						<span class="sopsr-news-slider__sr-only"><?php echo esc_html( SOPSR_News_Slider_Settings::text( 'next', $lang ) ); ?></span>
						<svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><path d="m8.5 19.5 7.5-7.5-7.5-7.5 1.4-1.4 8.9 8.9-8.9 8.9z"/></svg>
					</button>
				</div>
			<?php endif; ?>

			<div class="splide__track">
				<ul class="splide__list">
					<?php foreach ( $posts as $index => $post ) : ?>
						<?php echo self::render_slide( $post, $index, $settings, $lang ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</ul>
			</div>

			<?php if ( $count > 1 && ! empty( $settings['autoplay'] ) && ! empty( $settings['progress_bar'] ) ) : ?>
				<div class="splide__progress sopsr-news-slider__progress" aria-hidden="true">
					<div class="splide__progress__bar"></div>
				</div>
			<?php endif; ?>

			<script type="application/json" class="sopsr-news-slider__config"><?php echo wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
		</section>
		<?php

		return (string) ob_get_clean();
	}

	public static function query_posts( array $settings, string $lang ): array {
		$target    = max( 1, (int) $settings['post_count'] );
		$include   = self::map_categories( (array) $settings['include_categories'], $lang );
		$exclude   = self::map_categories( (array) $settings['exclude_categories'], $lang );
		$pinned    = self::map_posts( (array) $settings['pinned_post_ids'], $lang );
		$collected = array();
		$seen      = array();

		$base = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'suppress_filters'    => false,
		);

		if ( function_exists( 'pll_current_language' ) && '' !== $lang ) {
			$base['lang'] = $lang;
		}
		if ( ! empty( $include ) ) {
			$base['category__in'] = $include;
		}
		if ( ! empty( $exclude ) ) {
			$base['category__not_in'] = $exclude;
		}

		if ( ! empty( $pinned ) ) {
			$args = array_merge(
				$base,
				array(
					'post__in'       => $pinned,
					'posts_per_page' => min( count( $pinned ), $target ),
					'orderby'        => 'post__in',
				)
			);
			$query = new WP_Query( $args );
			foreach ( $query->posts as $post ) {
				if ( self::post_is_usable( $post, $settings ) ) {
					$collected[] = $post;
					$seen[]      = (int) $post->ID;
					if ( count( $collected ) >= $target ) {
						break;
					}
				}
			}
		}

		if ( count( $collected ) < $target ) {
			$candidate_count = min( 100, max( 20, $target * 5 ) );
			$args = array_merge(
				$base,
				array(
					'posts_per_page' => $candidate_count,
					'post__not_in'   => $seen,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			$query = new WP_Query( $args );
			foreach ( $query->posts as $post ) {
				if ( in_array( (int) $post->ID, $seen, true ) ) {
					continue;
				}
				if ( self::post_is_usable( $post, $settings ) ) {
					$collected[] = $post;
					$seen[]      = (int) $post->ID;
					if ( count( $collected ) >= $target ) {
						break;
					}
				}
			}
		}

		if ( count( $collected ) < $target ) {
			SOPSR_News_Slider_Logger::log(
				'warning',
				'Slider contains fewer posts than requested.',
				array(
					'language'  => $lang,
					'requested' => $target,
					'returned'  => count( $collected ),
				)
			);
		}

		return array_slice( $collected, 0, $target );
	}

	private static function post_is_usable( WP_Post $post, array $settings ): bool {
		if ( has_post_thumbnail( $post ) ) {
			return true;
		}

		if ( 'skip' === $settings['missing_image_mode'] ) {
			SOPSR_News_Slider_Logger::log( 'debug', 'Post skipped because featured image is missing.', array( 'post_id' => $post->ID ) );
			return false;
		}

		if ( 'fallback' === $settings['missing_image_mode'] && ! wp_attachment_is_image( (int) $settings['fallback_image_id'] ) ) {
			SOPSR_News_Slider_Logger::log( 'warning', 'Fallback image is not configured; using background-only slide.', array( 'post_id' => $post->ID ) );
		}

		return true;
	}

	private static function map_posts( array $ids, string $lang ): array {
		$mapped = array();

		foreach ( array_filter( array_map( 'absint', $ids ) ) as $post_id ) {
			$current = $post_id;
			if ( function_exists( 'pll_get_post' ) && '' !== $lang ) {
				$translated = pll_get_post( $post_id, $lang );
				if ( $translated ) {
					$current = absint( $translated );
				}
			}
			$mapped[] = $current;
		}

		return array_values( array_unique( array_filter( $mapped ) ) );
	}

	private static function map_categories( array $ids, string $lang ): array {
		$mapped = array();

		foreach ( array_filter( array_map( 'absint', $ids ) ) as $term_id ) {
			$current = $term_id;
			if ( function_exists( 'pll_get_term' ) && '' !== $lang ) {
				$translated = pll_get_term( $term_id, $lang );
				if ( $translated ) {
					$current = absint( $translated );
				}
			}
			$mapped[] = $current;
		}

		return array_values( array_unique( array_filter( $mapped ) ) );
	}

	private static function render_slide( WP_Post $post, int $index, array $settings, string $lang ): string {
		$post_id      = (int) $post->ID;
		$title        = get_the_title( $post );
		$url          = get_permalink( $post );
		$attachment   = get_post_thumbnail_id( $post );
		$has_fallback = false;

		if ( ! $attachment && 'fallback' === $settings['missing_image_mode'] && wp_attachment_is_image( (int) $settings['fallback_image_id'] ) ) {
			$attachment   = (int) $settings['fallback_image_id'];
			$has_fallback = true;
		}

		$image_html = '';
		if ( $attachment ) {
			$image_html = self::image_html( $attachment, 0 === $index, $settings, $title, $has_fallback );
		}

		$aria_label = trim( SOPSR_News_Slider_Settings::text( 'cta_prefix', $lang ) . ' ' . wp_strip_all_tags( $title ) );

		ob_start();
		?>
		<li class="splide__slide sopsr-news-slider__slide">
			<article class="sopsr-news-slider__slide-inner">
				<div class="sopsr-news-slider__media"<?php echo $image_html ? '' : ' aria-hidden="true"'; ?>>
					<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<div class="sopsr-news-slider__overlay" aria-hidden="true"></div>
				<div class="sopsr-news-slider__content">
					<div class="sopsr-news-slider__content-inner">
						<?php if ( ! empty( $settings['show_date'] ) ) : ?>
							<div class="sopsr-news-slider__date"><?php echo esc_html( get_the_date( '', $post ) ); ?></div>
						<?php endif; ?>

						<?php $heading_tag = in_array( $settings['heading_level'], array( 'h2', 'h3', 'h4' ), true ) ? $settings['heading_level'] : 'h2'; ?>
						<<?php echo esc_attr( $heading_tag ); ?> class="sopsr-news-slider__title">
							<?php if ( ! empty( $settings['title_link'] ) ) : ?>
								<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $title ); ?>
							<?php endif; ?>
						</<?php echo esc_attr( $heading_tag ); ?>>

						<?php if ( ! empty( $settings['show_excerpt'] ) ) : ?>
							<div class="sopsr-news-slider__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), (int) $settings['excerpt_words'] ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $settings['cta_enabled'] ) ) : ?>
							<a
								class="sopsr-news-slider__cta"
								href="<?php echo esc_url( $url ); ?>"
								aria-label="<?php echo esc_attr( $aria_label ); ?>"
								<?php if ( ! empty( $settings['cta_title_attribute'] ) ) : ?>title="<?php echo esc_attr( $aria_label ); ?>"<?php endif; ?>
							><?php echo esc_html( SOPSR_News_Slider_Settings::text( 'cta_text', $lang ) ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</article>
		</li>
		<?php
		return (string) ob_get_clean();
	}

	private static function image_html( int $attachment_id, bool $first, array $settings, string $title, bool $fallback ): string {
		$size = $settings['image_size'] ?: 'full';
		$src  = wp_get_attachment_image_src( $attachment_id, $size );
		if ( ! $src ) {
			return '';
		}

		$alt = '';
		if ( 'media' === $settings['image_alt_mode'] ) {
			$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
		}

		$attrs = array(
			'class' => 'sopsr-news-slider__image',
			'alt'   => $alt,
		);

		if ( $first ) {
			$attrs['loading']       = 'eager';
			$attrs['fetchpriority'] = 'high';
			return wp_get_attachment_image( $attachment_id, $size, false, $attrs );
		}

		if ( 'native' === $settings['lazy_mode'] ) {
			$attrs['loading'] = 'lazy';
			return wp_get_attachment_image( $attachment_id, $size, false, $attrs );
		}

		if ( 'off' === $settings['lazy_mode'] ) {
			$attrs['loading'] = 'eager';
			return wp_get_attachment_image( $attachment_id, $size, false, $attrs );
		}

		$srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
		$sizes  = wp_get_attachment_image_sizes( $attachment_id, $size );

		$html  = '<img class="sopsr-news-slider__image"';
		$html .= ' width="' . esc_attr( (string) $src[1] ) . '" height="' . esc_attr( (string) $src[2] ) . '"';
		$html .= ' data-splide-lazy="' . esc_url( $src[0] ) . '"';
		if ( $srcset ) {
			$html .= ' data-splide-lazy-srcset="' . esc_attr( $srcset ) . '"';
		}
		if ( $sizes ) {
			$html .= ' sizes="' . esc_attr( $sizes ) . '"';
		}
		$html .= ' alt="' . esc_attr( $alt ) . '">';

		return $html;
	}

	private static function splide_config( array $settings, string $lang, int $count ): array {
		$is_single = $count <= 1;

		if ( 'fade' === $settings['transition'] ) {
			$type   = 'fade';
			$rewind = ! $is_single && ( ! empty( $settings['loop'] ) || ! empty( $settings['rewind'] ) );
		} else {
			$type   = ! $is_single && ! empty( $settings['loop'] ) ? 'loop' : 'slide';
			$rewind = ! $is_single && empty( $settings['loop'] ) && ! empty( $settings['rewind'] );
		}

		$lazy_load = false;
		if ( 'splide-nearby' === $settings['lazy_mode'] ) {
			$lazy_load = 'nearby';
		} elseif ( 'splide-sequential' === $settings['lazy_mode'] ) {
			$lazy_load = 'sequential';
		}

		$keyboard = false;
		if ( 'focused' === $settings['keyboard'] ) {
			$keyboard = 'focused';
		} elseif ( 'global' === $settings['keyboard'] ) {
			$keyboard = 'global';
		}

		$config = array(
			'type'              => $type,
			'rewind'            => $rewind,
			'rewindByDrag'      => ! empty( $settings['rewind_by_drag'] ),
			'speed'             => (int) $settings['speed'],
			'rewindSpeed'       => (int) $settings['rewind_speed'],
			'autoplay'          => ! $is_single && ! empty( $settings['autoplay'] ),
			'interval'          => (int) $settings['interval'],
			'pauseOnHover'      => true,
			'pauseOnFocus'      => true,
			'resetProgress'     => ! empty( $settings['reset_progress'] ),
			'arrows'            => ! $is_single && ! empty( $settings['arrows'] ),
			'pagination'        => ! $is_single && ! empty( $settings['pagination'] ),
			'paginationKeyboard'=> true,
			'drag'              => ! $is_single && ! empty( $settings['drag'] ),
			'keyboard'          => $keyboard,
			'waitForTransition' => ! empty( $settings['wait_for_transition'] ),
			'easing'            => (string) $settings['easing'],
			'start'             => min( (int) $settings['start'], max( 0, $count - 1 ) ),
			'perPage'           => 'fade' === $settings['transition'] ? 1 : max( 1, (int) $settings['per_page'] ),
			'perMove'           => max( 1, (int) $settings['per_move'] ),
			'gap'               => self::css_number( $settings['gap'], $settings['gap_unit'] ),
			'padding'           => array(
				'left'  => self::css_number( $settings['padding_left'], $settings['padding_unit'] ),
				'right' => self::css_number( $settings['padding_right'], $settings['padding_unit'] ),
			),
			'wheel'             => ! empty( $settings['wheel'] ),
			'wheelSleep'        => (int) $settings['wheel_sleep'],
			'releaseWheel'      => ! empty( $settings['release_wheel'] ),
			'flickPower'        => (int) $settings['flick_power'],
			'flickMaxPages'     => (int) $settings['flick_max_pages'],
			'omitEnd'           => ! empty( $settings['omit_end'] ),
			'lazyLoad'          => $lazy_load,
			'preloadPages'      => 1,
			'live'              => true,
			'label'             => SOPSR_News_Slider_Settings::text( 'slider_label', $lang ),
			'i18n'              => self::i18n( $lang ),
			'__sopsrDebug'      => ! empty( $settings['debug_console'] ),
		);

		return $config;
	}

	private static function i18n( string $lang ): array {
		return array(
			'prev'       => SOPSR_News_Slider_Settings::text( 'prev', $lang ),
			'next'       => SOPSR_News_Slider_Settings::text( 'next', $lang ),
			'first'      => SOPSR_News_Slider_Settings::text( 'first', $lang ),
			'last'       => SOPSR_News_Slider_Settings::text( 'last', $lang ),
			'slideX'     => SOPSR_News_Slider_Settings::text( 'slideX', $lang ),
			'pageX'      => SOPSR_News_Slider_Settings::text( 'pageX', $lang ),
			'play'       => SOPSR_News_Slider_Settings::text( 'play', $lang ),
			'pause'      => SOPSR_News_Slider_Settings::text( 'pause', $lang ),
			'carousel'   => SOPSR_News_Slider_Settings::text( 'carousel', $lang ),
			'select'     => SOPSR_News_Slider_Settings::text( 'select', $lang ),
			'slide'      => SOPSR_News_Slider_Settings::text( 'slide', $lang ),
			'slideLabel' => SOPSR_News_Slider_Settings::text( 'slideLabel', $lang ),
		);
	}

	private static function dynamic_css( string $id, array $settings ): string {
		$selector = '#' . $id;
		$css      = array();

		$overlay = self::overlay_css( $settings );
		$control_bg = self::rgba( $settings['controls_bg_color'], (int) $settings['controls_bg_opacity'] / 100 );

		$css[] = sprintf(
			'%1$s{--sopsr-image-bg:%2$s;--sopsr-overlay:%3$s;--sopsr-title:%4$s;--sopsr-content-max:%5$s;--sopsr-pad-x:%6$s;--sopsr-pad-y:%7$s;--sopsr-cta-text:%8$s;--sopsr-cta-bg:%9$s;--sopsr-cta-hover-text:%10$s;--sopsr-cta-hover-bg:%11$s;--sopsr-cta-border:%12$s;--sopsr-cta-border-width:%13$s;--sopsr-cta-radius:%14$s;--sopsr-cta-font:%15$s;--sopsr-cta-weight:%16$s;--sopsr-cta-py:%17$s;--sopsr-cta-px:%18$s;--sopsr-control-color:%19$s;--sopsr-control-bg:%20$s;--sopsr-control-size:%21$s;--sopsr-page-active:%22$s;--sopsr-page:%23$s;--sopsr-focus:%24$s;}',
			$selector,
			$settings['image_background_color'],
			$overlay,
			$settings['title_color'],
			self::dimension( $settings, 'content_max_width' ),
			self::dimension( $settings, 'content_padding_x' ),
			self::dimension( $settings, 'content_padding_y' ),
			$settings['cta_text_color'],
			$settings['cta_bg_color'],
			$settings['cta_hover_text_color'],
			$settings['cta_hover_bg_color'],
			$settings['cta_border_color'],
			self::dimension( $settings, 'cta_border_width' ),
			self::dimension( $settings, 'cta_border_radius' ),
			self::font_size( $settings, 'cta_font_size' ),
			$settings['cta_font_weight'],
			self::dimension( $settings, 'cta_padding_y' ),
			self::dimension( $settings, 'cta_padding_x' ),
			$settings['controls_color'],
			$control_bg,
			self::dimension( $settings, 'controls_size' ),
			$settings['pagination_active_color'],
			$settings['pagination_color'],
			$settings['focus_color']
		);

		$css[] = sprintf(
			'%1$s .sopsr-news-slider__content{align-items:%2$s;justify-items:%3$s;text-align:%4$s;}%1$s .sopsr-news-slider__excerpt{margin-left:%5$s;margin-right:%6$s;}',
			$selector,
			self::align_to_grid( $settings['content_valign'] ),
			self::align_to_grid( $settings['content_halign'] ),
			$settings['content_halign'],
			'right' === $settings['content_halign'] ? 'auto' : ( 'center' === $settings['content_halign'] ? 'auto' : '0' ),
			'left' === $settings['content_halign'] ? 'auto' : ( 'center' === $settings['content_halign'] ? 'auto' : '0' )
		);

		$css[] = $selector . '{--sopsr-cta-focus-text:' . ( $settings['cta_focus_text_color'] ?? $settings['cta_text_color'] ) . ';--sopsr-cta-focus-bg:' . ( $settings['cta_focus_bg_color'] ?? $settings['cta_bg_color'] ) . ';}';
		$title_size = 'clamp(' . self::font_size( $settings, 'title_clamp_min' ) . ',' . self::css_number( $settings['title_clamp_fluid'], 'vw' ) . ',' . self::font_size( $settings, 'title_clamp_max' ) . ')';
		if ( 'responsive' === $settings['title_font_mode'] ) {
			$title_size = self::font_size( $settings, 'title_desktop' );
		}

		$css[] = sprintf(
			'%1$s .sopsr-news-slider__title{font-size:%2$s;font-weight:%3$s;line-height:%4$s;max-width:%5$s;padding:%6$s;margin:%7$s;%8$s}',
			$selector,
			$title_size,
			$settings['title_weight'],
			(float) $settings['title_line_height'],
			self::dimension( $settings, 'title_max_width' ),
			self::title_spacing( $settings, 'title_padding' ),
			self::title_spacing( $settings, 'title_margin' ),
			! empty( $settings['title_text_shadow'] ) ? 'text-shadow:0 2px 8px rgba(0,0,0,.45);' : 'text-shadow:none;'
		);

		$css = array_merge( $css, self::device_css( $selector, 'desktop', $settings['responsive']['desktop'], ! empty( $settings['full_bleed'] ), '' ) );

		$tablet_media = '@media (max-width:' . (int) $settings['tablet_breakpoint'] . 'px){';
		$tablet_rules = self::device_css( $selector, 'tablet', $settings['responsive']['tablet'], ! empty( $settings['full_bleed'] ), $tablet_media );
		$css           = array_merge( $css, $tablet_rules );
		$css[]         = self::responsive_style_css( $selector, $settings, 'tablet', $tablet_media );

		$mobile_media = '@media (max-width:' . (int) $settings['mobile_breakpoint'] . 'px){';
		$mobile_rules = self::device_css( $selector, 'mobile', $settings['responsive']['mobile'], ! empty( $settings['full_bleed'] ), $mobile_media );
		$css           = array_merge( $css, $mobile_rules );
		$css[]         = self::responsive_style_css( $selector, $settings, 'mobile', $mobile_media );

		if ( 'responsive' === $settings['title_font_mode'] ) {
			$css[] = '@media (max-width:' . (int) $settings['tablet_breakpoint'] . 'px){' . $selector . ' .sopsr-news-slider__title{font-size:' . self::font_size( $settings, 'title_tablet' ) . ';}}';
			$css[] = '@media (max-width:' . (int) $settings['mobile_breakpoint'] . 'px){' . $selector . ' .sopsr-news-slider__title{font-size:' . self::font_size( $settings, 'title_mobile' ) . ';}}';
		}

		if ( ! empty( $settings['custom_css'] ) ) {
			$custom = str_replace( array( '{{slider}}', '{{id}}' ), $selector, (string) $settings['custom_css'] );
			$css[]  = "\n/* Custom CSS */\n" . $custom;
		}

		return implode( "\n", $css );
	}

	private static function device_css( string $selector, string $device, array $device_settings, bool $full_bleed, string $media_start ): array {
		$width_value = self::clean_number( $device_settings['width_value'] );
		$width_unit  = $device_settings['width_unit'];
		if ( $full_bleed && '%' === $width_unit ) {
			$width_unit = 'vw';
		}

		$root_rule = $selector . '{width:' . $width_value . $width_unit . ';max-width:100vw;';
		if ( $full_bleed ) {
			$root_rule .= 'position:relative;left:50%;transform:translateX(-50%);';
		} else {
			$root_rule .= 'margin-left:auto;margin-right:auto;';
		}
		$root_rule .= '}';

		$height_rule = $selector . ' .sopsr-news-slider__slide-inner{';
		$image_rule  = $selector . ' .sopsr-news-slider__image{';

		switch ( $device_settings['height_mode'] ) {
			case 'auto':
				$height_rule .= 'height:auto;aspect-ratio:auto;';
				$image_rule  .= 'height:auto;';
				break;
			case 'aspect':
				$height_rule .= 'height:auto;aspect-ratio:' . self::clean_number( $device_settings['aspect_w'] ) . '/' . self::clean_number( $device_settings['aspect_h'] ) . ';';
				$image_rule  .= 'height:100%;';
				break;
			case 'clamp':
				$height_rule .= 'height:clamp(' . self::clean_number( $device_settings['clamp_min'] ) . 'px,' . self::clean_number( $device_settings['clamp_fluid'] ) . 'vw,' . self::clean_number( $device_settings['clamp_max'] ) . 'px);aspect-ratio:auto;';
				$image_rule  .= 'height:100%;';
				break;
			case 'viewport':
				$height = min( 100, max( 1, (float) $device_settings['height_value'] ) );
				$height_rule .= 'height:' . self::clean_number( $height ) . 'vh;aspect-ratio:auto;';
				$image_rule  .= 'height:100%;';
				break;
			case 'fixed':
			default:
				$height_rule .= 'height:' . self::clean_number( $device_settings['height_value'] ) . $device_settings['height_unit'] . ';aspect-ratio:auto;';
				$image_rule  .= 'height:100%;';
				break;
		}

		$height_rule .= '}';
		$image_rule  .= 'object-fit:' . $device_settings['fit'] . ';object-position:' . self::clean_number( $device_settings['position_x'] ) . '% ' . self::clean_number( $device_settings['position_y'] ) . '%;}';

		if ( '' === $media_start ) {
			return array( $root_rule, $height_rule, $image_rule );
		}

		return array( $media_start . $root_rule . $height_rule . $image_rule . '}' );
	}

	private static function overlay_css( array $settings ): string {
		$opacity = (int) $settings['overlay_opacity'] / 100;
		$solid   = self::rgba( $settings['overlay_color'], $opacity );
		$light   = self::rgba( $settings['overlay_color'], max( 0, $opacity * 0.2 ) );

		switch ( $settings['overlay_type'] ) {
			case 'none':
				return 'transparent';
			case 'gradient-top-bottom':
				return 'linear-gradient(to bottom,' . $light . ',' . $solid . ')';
			case 'gradient-bottom-top':
				return 'linear-gradient(to top,' . $light . ',' . $solid . ')';
			case 'solid':
			default:
				return $solid;
		}
	}

	private static function rgba( string $hex, float $alpha ): string {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			$hex = '000000';
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );
		return sprintf( 'rgba(%d,%d,%d,%.3F)', $r, $g, $b, max( 0, min( 1, $alpha ) ) );
	}

	private static function align_to_grid( string $value ): string {
		if ( 'left' === $value || 'top' === $value ) {
			return 'start';
		}
		if ( 'right' === $value || 'bottom' === $value ) {
			return 'end';
		}
		return 'center';
	}

	private static function responsive_style_css( string $selector, array $settings, string $device, string $media_start ): string {
		$halign = (string) self::responsive_value( $settings, $device, 'content_halign' );
		$valign = (string) self::responsive_value( $settings, $device, 'content_valign' );
		$root_rule = $selector . '{'
			. '--sopsr-content-max:' . self::responsive_dimension( $settings, $device, 'content_max_width' ) . ';'
			. '--sopsr-pad-x:' . self::responsive_dimension( $settings, $device, 'content_padding_x' ) . ';'
			. '--sopsr-pad-y:' . self::responsive_dimension( $settings, $device, 'content_padding_y' ) . ';'
			. '--sopsr-cta-border-width:' . self::responsive_dimension( $settings, $device, 'cta_border_width' ) . ';'
			. '--sopsr-cta-radius:' . self::responsive_dimension( $settings, $device, 'cta_border_radius' ) . ';'
			. '--sopsr-cta-font:' . self::responsive_dimension( $settings, $device, 'cta_font_size' ) . ';'
			. '--sopsr-cta-weight:' . self::responsive_value( $settings, $device, 'cta_font_weight' ) . ';'
			. '--sopsr-cta-py:' . self::responsive_dimension( $settings, $device, 'cta_padding_y' ) . ';'
			. '--sopsr-cta-px:' . self::responsive_dimension( $settings, $device, 'cta_padding_x' ) . ';'
			. '--sopsr-control-size:' . self::responsive_dimension( $settings, $device, 'controls_size' ) . ';}';
		$content_rule = $selector . ' .sopsr-news-slider__content{align-items:' . self::align_to_grid( $valign )
			. ';justify-items:' . self::align_to_grid( $halign ) . ';text-align:' . $halign . ';}';
		$excerpt_rule = $selector . ' .sopsr-news-slider__excerpt{margin-left:'
			. ( 'right' === $halign ? 'auto' : ( 'center' === $halign ? 'auto' : '0' ) )
			. ';margin-right:' . ( 'left' === $halign ? 'auto' : ( 'center' === $halign ? 'auto' : '0' ) ) . ';}';
		$title_rule = $selector . ' .sopsr-news-slider__title{font-weight:' . self::responsive_value( $settings, $device, 'title_weight' )
			. ';line-height:' . self::clean_number( self::responsive_value( $settings, $device, 'title_line_height' ) )
			. ';max-width:' . self::responsive_dimension( $settings, $device, 'title_max_width' ) . ';'
			. 'padding:' . self::title_spacing( $settings, 'title_padding', $device ) . ';'
			. 'margin:' . self::title_spacing( $settings, 'title_margin', $device ) . ';'
			. ( ! empty( self::responsive_value( $settings, $device, 'title_text_shadow' ) ) ? 'text-shadow:0 2px 8px rgba(0,0,0,.45);' : 'text-shadow:none;' ) . '}';

		return $media_start . $root_rule . $content_rule . $excerpt_rule . $title_rule . '}';
	}

	private static function responsive_value( array $settings, string $device, string $key ) {
		return $settings['responsive'][ $device ][ $key ] ?? $settings[ $key ];
	}

	private static function responsive_dimension( array $settings, string $device, string $key ): string {
		$value = self::responsive_value( $settings, $device, $key );
		$unit  = $settings['responsive'][ $device ][ $key . '_unit' ] ?? $settings[ $key . '_unit' ] ?? 'px';
		$unit  = in_array( $unit, array( 'px', 'rem', 'em' ), true ) ? $unit : 'px';
		return self::css_number( $value, $unit );
	}

	private static function title_spacing( array $settings, string $property, string $device = 'desktop' ): string {
		$values = array();
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$key = $property . '_' . $side;
			if ( 'desktop' === $device ) {
				$values[] = self::dimension( $settings, array_key_exists( $key, $settings ) ? $key : $property );
			} else {
				$device_settings = $settings['responsive'][ $device ] ?? array();
				$values[] = self::responsive_dimension( $settings, $device, array_key_exists( $key, $device_settings ) ? $key : $property );
			}
		}
		return implode( ' ', $values );
	}

	private static function dimension( array $settings, string $key ): string {
		$unit = $settings[ $key . '_unit' ] ?? 'px';
		$unit = in_array( $unit, array( 'px', 'rem', 'em' ), true ) ? $unit : 'px';
		return self::css_number( $settings[ $key ], $unit );
	}

	private static function font_size( array $settings, string $key ): string {
		return self::dimension( $settings, $key );
	}

	private static function css_number( $value, string $unit ): string {
		return self::clean_number( $value ) . $unit;
	}

	private static function clean_number( $value ): string {
		$value = is_numeric( $value ) ? (float) $value : 0;
		if ( floor( $value ) === $value ) {
			return (string) (int) $value;
		}
		return rtrim( rtrim( number_format( $value, 3, '.', '' ), '0' ), '.' );
	}
}
