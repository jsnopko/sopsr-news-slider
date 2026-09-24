<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SOPSR_News_Slider_Admin {
	private const PAGE_SLUG = 'sopsr-news-slider';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'admin_post_sopsr_slider_clear_log', array( __CLASS__, 'clear_log' ) );
		add_action( 'admin_post_sopsr_slider_download_splide', array( __CLASS__, 'download_splide' ) );
	}

	public static function menu(): void {
		add_menu_page(
			'ŠOP SR Slider',
			'ŠOP SR Slider',
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' ),
			'dashicons-images-alt2',
			58
		);
	}

	public static function enqueue( string $hook ): void {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'sopsr-news-slider-preview', SOPSR_NS_URL . 'assets/css/frontend.css', array(), SOPSR_NS_VERSION );
		wp_enqueue_style(
			'sopsr-news-slider-admin',
			SOPSR_NS_URL . 'assets/css/admin.css',
			array( 'wp-color-picker', 'sopsr-news-slider-preview' ),
			SOPSR_NS_VERSION
		);

		wp_enqueue_script(
			'sopsr-news-slider-admin',
			SOPSR_NS_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			SOPSR_NS_VERSION,
			true
		);

		wp_localize_script(
			'sopsr-news-slider-admin',
			'SOPSRSliderAdmin',
			array(
				'previewImage' => self::preview_image_url(),
				'defaults'     => SOPSR_News_Slider_Settings::defaults(),
				'confirmReset' => 'Naozaj chcete vrátiť túto sekciu na predvolené hodnoty? Zmena sa uloží až po kliknutí na „Uložiť nastavenia“.',
				'confirmAll'   => 'Naozaj chcete vrátiť všetky polia na predvolené hodnoty? Zmena sa uloží až po kliknutí na „Uložiť nastavenia“.',
				'mediaTitle'   => 'Vyberte fallback obrázok',
				'mediaButton'  => 'Použiť obrázok',
			)
		);
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$s = SOPSR_News_Slider_Settings::get_all();
		$d = SOPSR_News_Slider_Settings::defaults();
		$categories = get_categories( array( 'hide_empty' => false ) );
		$image_sizes = array_values( array_unique( array_merge( array( 'full' ), get_intermediate_image_sizes() ) ) );
		$languages = array_merge(
			array(
				'sk' => 'Slovenčina',
				'en' => 'English',
				'de' => 'Deutsch',
				'pl' => 'Polski',
				'hu' => 'Magyar',
			),
			SOPSR_News_Slider_Settings::languages()
		);
		$assets = SOPSR_News_Slider_Assets::splide_urls();

		self::notice();
		?>
		<div class="wrap sopsr-slider-admin">
			<div class="sopsr-slider-admin__header">
				<div>
					<h1>ŠOP SR News Slider</h1>
					<p>Hero/news slider založený na Splide <?php echo esc_html( SOPSR_NS_SPLIDE_VERSION ); ?>. Shortcode: <code>[sopsr_news_slider]</code></p>
				</div>
				<div class="sopsr-slider-admin__status">
					<span class="sopsr-status-dot sopsr-status-dot--ok"></span>
					Splide: <strong><?php echo esc_html( $assets['source'] ); ?></strong>
				</div>
			</div>

			<form method="post" action="options.php" id="sopsr-slider-settings-form">
				<?php settings_fields( 'sopsr_news_slider_group' ); ?>

				<div class="sopsr-slider-admin__layout">
					<main class="sopsr-slider-admin__settings">

						<?php self::section_open( 'content', 'Obsah', 'Výber článkov a obsah slidu.' ); ?>
							<?php self::number( 'post_count', 'Počet aktualít', $s['post_count'], $d['post_count'], 1, 30, 1, 'Maximálny počet slidov.' ); ?>
							<?php self::category_multiselect( 'include_categories', 'Zahrnúť iba kategórie', $s['include_categories'], $d['include_categories'], $categories, 'Prázdne = všetky kategórie.' ); ?>
							<?php self::category_multiselect( 'exclude_categories', 'Vylúčené kategórie', $s['exclude_categories'], $d['exclude_categories'], $categories, 'Môžete označiť viac kategórií. Polylang preklady termov sa mapujú automaticky.' ); ?>
							<?php self::text( 'pinned_post_ids', 'Pripnuté ID článkov', implode( ',', (array) $s['pinned_post_ids'] ), '', 'Napr. 123,456. Zobrazia sa ako prvé a zvyšok doplnia najnovšie články.' ); ?>
							<?php self::select( 'missing_image_mode', 'Ak chýba featured image', $s['missing_image_mode'], $d['missing_image_mode'], array( 'skip' => 'Preskočiť článok', 'fallback' => 'Použiť fallback obrázok', 'background' => 'Farebné pozadie bez obrázka' ) ); ?>
							<?php self::media_field( 'fallback_image_id', 'Fallback obrázok', (int) $s['fallback_image_id'], (int) $d['fallback_image_id'] ); ?>
							<?php self::checkbox( 'show_date', 'Zobraziť dátum', $s['show_date'], $d['show_date'] ); ?>
							<?php self::checkbox( 'show_excerpt', 'Zobraziť excerpt', $s['show_excerpt'], $d['show_excerpt'] ); ?>
							<?php self::number( 'excerpt_words', 'Dĺžka excerptu', $s['excerpt_words'], $d['excerpt_words'], 5, 100, 1, 'Počet slov.' ); ?>
							<?php self::checkbox( 'title_link', 'Nadpis je klikateľný', $s['title_link'], $d['title_link'] ); ?>
							<?php self::select( 'heading_level', 'HTML úroveň nadpisu', $s['heading_level'], $d['heading_level'], array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ), 'Zvoľte podľa heading štruktúry stránky.' ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'layout', 'Rozmery a responsive', 'Samostatná šírka, výška a obrázok pre Desktop / Tablet / Mobile.' ); ?>
							<?php self::checkbox( 'full_bleed', 'Full width / vystúpiť z Kadence kontajnera', $s['full_bleed'], $d['full_bleed'], 'Pri percentuálnej šírke sa hodnota interpretuje ako vw.' ); ?>
							<?php self::number( 'tablet_breakpoint', 'Tablet breakpoint', $s['tablet_breakpoint'], $d['tablet_breakpoint'], 600, 1600, 1, 'max-width v px' ); ?>
							<?php self::number( 'mobile_breakpoint', 'Mobile breakpoint', $s['mobile_breakpoint'], $d['mobile_breakpoint'], 320, 1000, 1, 'max-width v px' ); ?>
							<div class="sopsr-responsive-grid">
								<?php foreach ( array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ) as $device => $label ) : ?>
									<?php self::device_card( $device, $label, $s['responsive'][ $device ], $d['responsive'][ $device ] ); ?>
								<?php endforeach; ?>
							</div>
							<?php self::select( 'content_halign', 'Horizontálne zarovnanie obsahu – Desktop', $s['content_halign'], $d['content_halign'], array( 'left' => 'Vľavo', 'center' => 'Na stred', 'right' => 'Vpravo' ) ); ?>
							<?php self::select( 'content_valign', 'Vertikálne zarovnanie obsahu – Desktop', $s['content_valign'], $d['content_valign'], array( 'top' => 'Hore', 'center' => 'Na stred', 'bottom' => 'Dole' ) ); ?>
							<?php self::dimension( 'content_max_width', 'Max. šírka textového obsahu – Desktop', $s, $d, 0.01, 2400, 0.01 ); ?>
							<?php self::dimension( 'content_padding_x', 'Horizontálny padding obsahu – Desktop', $s, $d, 0, 200, 0.01 ); ?>
							<?php self::dimension( 'content_padding_y', 'Vertikálny padding obsahu – Desktop', $s, $d, 0, 200, 0.01 ); ?>
							<?php self::responsive_style_cards( 'content', $s, $d ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'image-overlay', 'Obrázok a overlay', 'WordPress responsive images, object-fit, farba a gradient.' ); ?>
							<?php self::select_from_list( 'image_size', 'WordPress image size', $s['image_size'], $d['image_size'], $image_sizes ); ?>
							<?php self::select( 'image_alt_mode', 'ALT obrázka', $s['image_alt_mode'], $d['image_alt_mode'], array( 'media' => 'Použiť ALT z Media Library', 'decorative' => 'Dekoratívny obrázok (alt="")' ) ); ?>
							<?php self::select( 'lazy_mode', 'Načítavanie ďalších obrázkov', $s['lazy_mode'], $d['lazy_mode'], array( 'native' => 'WordPress/browser native lazy-load', 'splide-nearby' => 'Splide lazy: nearby', 'splide-sequential' => 'Splide lazy: sequential', 'off' => 'Bez lazy-load' ) ); ?>
							<?php self::color( 'image_background_color', 'Pozadie obrázka pri contain', $s['image_background_color'], $d['image_background_color'] ); ?>
							<?php self::select( 'overlay_type', 'Overlay', $s['overlay_type'], $d['overlay_type'], array( 'none' => 'Žiadny', 'solid' => 'Jednofarebný', 'gradient-top-bottom' => 'Gradient zhora nadol', 'gradient-bottom-top' => 'Gradient zdola nahor' ) ); ?>
							<?php self::color( 'overlay_color', 'Farba overlay', $s['overlay_color'], $d['overlay_color'] ); ?>
							<?php self::range( 'overlay_opacity', 'Opacity overlay', $s['overlay_opacity'], $d['overlay_opacity'], 0, 100, 1, '%' ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'title', 'Nadpis', 'Typografia názvu aktuality.' ); ?>
							<?php self::color( 'title_color', 'Farba nadpisu', $s['title_color'], $d['title_color'] ); ?>
							<?php self::select( 'title_font_mode', 'Veľkosť nadpisu', $s['title_font_mode'], $d['title_font_mode'], array( 'clamp' => 'CSS clamp()', 'responsive' => 'Samostatná veľkosť Desktop/Tablet/Mobile' ) ); ?>
							<div class="sopsr-inline-fields">
								<?php self::font_size( 'title_clamp_min', 'Clamp min', $s, $d ); ?>
								<?php self::number_compact( 'title_clamp_fluid', 'Clamp fluid', $s['title_clamp_fluid'], $d['title_clamp_fluid'], 0.1, 20, 0.1, 'vw' ); ?>
								<?php self::font_size( 'title_clamp_max', 'Clamp max', $s, $d ); ?>
							</div>
							<div class="sopsr-inline-fields">
								<?php self::font_size( 'title_desktop', 'Desktop', $s, $d ); ?>
								<?php self::font_size( 'title_tablet', 'Tablet', $s, $d ); ?>
								<?php self::font_size( 'title_mobile', 'Mobile', $s, $d ); ?>
							</div>
							<?php self::select( 'title_weight', 'Font weight – Desktop', (string) $s['title_weight'], (string) $d['title_weight'], array( '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900' ) ); ?>
							<?php self::number( 'title_line_height', 'Line-height – Desktop', $s['title_line_height'], $d['title_line_height'], 0.8, 2.5, 0.05 ); ?>
							<?php self::dimension( 'title_max_width', 'Max. šírka nadpisu – Desktop', $s, $d, 0.01, 2000, 0.01 ); ?>
							<?php self::checkbox( 'title_text_shadow', 'Text shadow – Desktop', $s['title_text_shadow'], $d['title_text_shadow'] ); ?>
							<?php self::responsive_style_cards( 'title', $s, $d ); ?>
							<?php self::title_spacing_controls( $s, $d ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'cta', 'CTA / „Viac informácií“', 'Vzhľad odkazu a WCAG accessible name.' ); ?>
							<?php self::checkbox( 'cta_enabled', 'Zobraziť CTA', $s['cta_enabled'], $d['cta_enabled'] ); ?>
							<?php self::checkbox( 'cta_title_attribute', 'Pridať aj title atribút', $s['cta_title_attribute'], $d['cta_title_attribute'], 'Accessible name je vždy riešený cez aria-label; title je iba doplnok.' ); ?>
							<?php self::color( 'cta_text_color', 'Text', $s['cta_text_color'], $d['cta_text_color'] ); ?>
							<?php self::color( 'cta_bg_color', 'Pozadie', $s['cta_bg_color'], $d['cta_bg_color'] ); ?>
							<?php self::color( 'cta_hover_text_color', 'Hover text', $s['cta_hover_text_color'], $d['cta_hover_text_color'] ); ?>
							<?php self::color( 'cta_hover_bg_color', 'Hover pozadie', $s['cta_hover_bg_color'], $d['cta_hover_bg_color'] ); ?>
							<?php self::color( 'cta_focus_text_color', 'Farba textu CTA pri focus', $s['cta_focus_text_color'], $d['cta_focus_text_color'] ); ?>
							<?php self::color( 'cta_focus_bg_color', 'Pozadie CTA pri focus', $s['cta_focus_bg_color'], $d['cta_focus_bg_color'] ); ?>
							<?php self::color( 'cta_border_color', 'Border', $s['cta_border_color'], $d['cta_border_color'] ); ?>
							<div class="sopsr-inline-fields">
								<?php self::dimension_compact( 'cta_border_width', 'Border – Desktop', $s, $d, 0, 100, 0.01 ); ?>
								<?php self::dimension_compact( 'cta_border_radius', 'Radius – Desktop', $s, $d, 0, 200, 0.01 ); ?>
								<?php self::font_size( 'cta_font_size', 'Font – Desktop', $s, $d ); ?>
							</div>
							<?php self::select( 'cta_font_weight', 'Font weight – Desktop', (string) $s['cta_font_weight'], (string) $d['cta_font_weight'], array( '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900' ) ); ?>
							<div class="sopsr-inline-fields">
								<?php self::dimension_compact( 'cta_padding_y', 'Padding Y – Desktop', $s, $d, 0, 200, 0.01 ); ?>
								<?php self::dimension_compact( 'cta_padding_x', 'Padding X – Desktop', $s, $d, 0, 300, 0.01 ); ?>
							</div>
							<?php self::responsive_style_cards( 'cta', $s, $d ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'slider', 'Slider', 'Najpoužívanejšie Splide nastavenia.' ); ?>
							<?php self::select( 'transition', 'Prechod', $s['transition'], $d['transition'], array( 'slide' => 'Slide', 'fade' => 'Fade' ) ); ?>
							<?php self::checkbox( 'loop', 'Loop', $s['loop'], $d['loop'], 'Pri Fade sa loop technicky realizuje cez rewind.' ); ?>
							<?php self::checkbox( 'rewind', 'Rewind', $s['rewind'], $d['rewind'] ); ?>
							<?php self::checkbox( 'rewind_by_drag', 'Rewind by drag', $s['rewind_by_drag'], $d['rewind_by_drag'] ); ?>
							<?php self::number( 'speed', 'Transition speed', $s['speed'], $d['speed'], 0, 10000, 50, 'ms' ); ?>
							<?php self::number( 'rewind_speed', 'Rewind speed', $s['rewind_speed'], $d['rewind_speed'], 0, 10000, 50, 'ms' ); ?>
							<?php self::checkbox( 'autoplay', 'Autoplay', $s['autoplay'], $d['autoplay'], 'Ak je zapnutý, Pause/Play ovládanie sa zobrazuje povinne.' ); ?>
							<?php self::number( 'interval', 'Autoplay interval', $s['interval'], $d['interval'], 1000, 60000, 250, 'ms' ); ?>
							<?php self::checkbox( 'reset_progress', 'Reset autoplay progress po prerušení', $s['reset_progress'], $d['reset_progress'] ); ?>
							<?php self::checkbox( 'progress_bar', 'Zobraziť autoplay progress bar', $s['progress_bar'], $d['progress_bar'] ); ?>
							<?php self::checkbox( 'arrows', 'Šípky', $s['arrows'], $d['arrows'] ); ?>
							<?php self::checkbox( 'pagination', 'Pagination', $s['pagination'], $d['pagination'] ); ?>
							<?php self::checkbox( 'drag', 'Drag / swipe', $s['drag'], $d['drag'] ); ?>
							<?php self::select( 'keyboard', 'Keyboard shortcuts', $s['keyboard'], $d['keyboard'], array( 'off' => 'Vypnuté', 'focused' => 'Iba keď je focus v slideri', 'global' => 'Globálne (neodporúčané)' ) ); ?>
							<?php self::checkbox( 'wait_for_transition', 'Wait for transition', $s['wait_for_transition'], $d['wait_for_transition'] ); ?>
							<?php self::text( 'easing', 'Easing', $s['easing'], $d['easing'], 'Napr. cubic-bezier(0.25, 1, 0.5, 1)' ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'controls', 'Ovládanie a farby', 'Šípky, pagination a focus.' ); ?>
							<?php self::color( 'controls_color', 'Farba ikon', $s['controls_color'], $d['controls_color'] ); ?>
							<?php self::color( 'controls_bg_color', 'Pozadie ovládania', $s['controls_bg_color'], $d['controls_bg_color'] ); ?>
							<?php self::range( 'controls_bg_opacity', 'Opacity pozadia', $s['controls_bg_opacity'], $d['controls_bg_opacity'], 0, 100, 1, '%' ); ?>
							<?php self::dimension( 'controls_size', 'Veľkosť ovládacích tlačidiel – Desktop', $s, $d, 0.01, 200, 0.01 ); ?>
							<?php self::responsive_style_cards( 'controls', $s, $d ); ?>
							<?php self::color( 'pagination_active_color', 'Aktívna pagination', $s['pagination_active_color'], $d['pagination_active_color'] ); ?>
							<?php self::color( 'pagination_color', 'Neaktívna pagination', $s['pagination_color'], $d['pagination_color'] ); ?>
							<?php self::color( 'focus_color', 'Focus outline', $s['focus_color'], $d['focus_color'] ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'languages', 'Jazyky a prístupnosť', 'CTA a screen-reader texty pre Polylang. Accessibility-critical správanie je zamknuté.' ); ?>
							<div class="sopsr-a11y-locks">
								<span>✓ pauseOnHover</span><span>✓ pauseOnFocus</span><span>✓ paginationKeyboard</span><span>✓ live region</span><span>✓ prefers-reduced-motion</span><span>✓ focus-visible</span>
							</div>
							<?php foreach ( $languages as $slug => $name ) : ?>
								<?php
								$current = $s['translations'][ $slug ] ?? SOPSR_News_Slider_Settings::translation_defaults()[ $slug ] ?? SOPSR_News_Slider_Settings::translation_defaults()['en'];
								$deflang = $d['translations'][ $slug ] ?? SOPSR_News_Slider_Settings::translation_defaults()['en'];
								?>
								<div class="sopsr-language-card">
									<h3><?php echo esc_html( $name ); ?> <code><?php echo esc_html( $slug ); ?></code></h3>
									<?php self::nested_text( "translations[$slug][cta_text]", 'CTA text', $current['cta_text'] ?? '', $deflang['cta_text'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][cta_prefix]", 'CTA aria/title prefix', $current['cta_prefix'] ?? '', $deflang['cta_prefix'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][slider_label]", 'Názov slidera', $current['slider_label'] ?? '', $deflang['slider_label'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][prev]", 'Predchádzajúca', $current['prev'] ?? '', $deflang['prev'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][next]", 'Nasledujúca', $current['next'] ?? '', $deflang['next'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][first]", 'Prvá', $current['first'] ?? '', $deflang['first'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][last]", 'Posledná', $current['last'] ?? '', $deflang['last'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][slideX]", 'Pagination: slide %s', $current['slideX'] ?? '', $deflang['slideX'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][pageX]", 'Pagination: page %s', $current['pageX'] ?? '', $deflang['pageX'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][slideLabel]", 'Slide label (%s / %s)', $current['slideLabel'] ?? '', $deflang['slideLabel'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][play]", 'Play', $current['play'] ?? '', $deflang['play'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][pause]", 'Pause', $current['pause'] ?? '', $deflang['pause'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][select]", 'Pagination aria-label', $current['select'] ?? '', $deflang['select'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][carousel]", 'aria-roledescription slidera', $current['carousel'] ?? '', $deflang['carousel'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][slide]", 'aria-roledescription slidu', $current['slide'] ?? '', $deflang['slide'] ?? '' ); ?>
									<?php self::nested_text( "translations[$slug][empty_text]", 'Text pri 0 výsledkoch', $current['empty_text'] ?? '', $deflang['empty_text'] ?? '' ); ?>
								</div>
							<?php endforeach; ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'advanced', 'Advanced Splide', 'Menej časté parametre. Interné/rizikové options ako clones/classes/focusableNodes zámerne nie sú sprístupnené.' ); ?>
							<?php self::number( 'start', 'Start index', $s['start'], $d['start'], 0, 100, 1 ); ?>
							<?php self::number( 'per_page', 'perPage', $s['per_page'], $d['per_page'], 1, 10, 1, 'Pri Fade sa vždy použije 1.' ); ?>
							<?php self::number( 'per_move', 'perMove', $s['per_move'], $d['per_move'], 1, 10, 1 ); ?>
							<div class="sopsr-inline-fields">
								<?php self::number_compact( 'gap', 'Gap', $s['gap'], $d['gap'], 0, 500, 0.1, '' ); ?>
								<?php self::select_compact( 'gap_unit', 'Jednotka', $s['gap_unit'], $d['gap_unit'], array( 'px' => 'px', 'rem' => 'rem', 'vw' => 'vw' ) ); ?>
							</div>
							<div class="sopsr-inline-fields">
								<?php self::number_compact( 'padding_left', 'Padding left', $s['padding_left'], $d['padding_left'], 0, 1000, 0.1, '' ); ?>
								<?php self::number_compact( 'padding_right', 'Padding right', $s['padding_right'], $d['padding_right'], 0, 1000, 0.1, '' ); ?>
								<?php self::select_compact( 'padding_unit', 'Jednotka', $s['padding_unit'], $d['padding_unit'], array( 'px' => 'px', 'rem' => 'rem', 'vw' => 'vw' ) ); ?>
							</div>
							<?php self::checkbox( 'wheel', 'Mouse wheel navigation', $s['wheel'], $d['wheel'], 'Default OFF, aby slider nekradol scroll stránky.' ); ?>
							<?php self::number( 'wheel_sleep', 'Wheel sleep', $s['wheel_sleep'], $d['wheel_sleep'], 0, 5000, 50, 'ms' ); ?>
							<?php self::checkbox( 'release_wheel', 'Release wheel na konci', $s['release_wheel'], $d['release_wheel'] ); ?>
							<?php self::number( 'flick_power', 'Flick power', $s['flick_power'], $d['flick_power'], 0, 2000, 10 ); ?>
							<?php self::number( 'flick_max_pages', 'Flick max pages', $s['flick_max_pages'], $d['flick_max_pages'], 1, 10, 1 ); ?>
							<?php self::checkbox( 'omit_end', 'omitEnd', $s['omit_end'], $d['omit_end'] ); ?>
						<?php self::section_close(); ?>

						<?php self::section_open( 'custom-debug', 'Custom CSS a debug', 'Custom CSS podporuje token {{slider}}, ktorý sa nahradí ID konkrétneho slidera.' ); ?>
							<?php self::textarea( 'custom_css', 'Custom CSS', $s['custom_css'], $d['custom_css'], "{{slider}} .sopsr-news-slider__title {\n  letter-spacing: .01em;\n}" ); ?>
							<?php self::checkbox( 'debug_logging', 'PHP debug log pluginu', $s['debug_logging'], $d['debug_logging'], 'Loguje iba technické údaje pluginu, nie obsah formulárov ani heslá.' ); ?>
							<?php self::checkbox( 'debug_console', 'JavaScript debug do Console', $s['debug_console'], $d['debug_console'] ); ?>
						<?php self::section_close(); ?>

						<div class="sopsr-save-row">
							<?php submit_button( 'Uložiť nastavenia', 'primary', 'submit', false ); ?>
							<button type="button" class="button" id="sopsr-reset-all">Reset všetkých polí na default</button>
						</div>
					</main>

					<aside class="sopsr-slider-admin__preview-column">
						<div class="sopsr-preview-card">
							<div class="sopsr-preview-card__head">
								<strong>Live preview</strong>
								<button type="submit" class="button button-primary">Uložiť zmeny</button>
								<div class="sopsr-preview-devices" role="group" aria-label="Preview zariadenie">
									<button type="button" class="button button-small is-active" data-preview-device="desktop">Desktop</button>
									<button type="button" class="button button-small" data-preview-device="tablet">Tablet</button>
									<button type="button" class="button button-small" data-preview-device="mobile">Mobile</button>
								</div>
							</div>
							<div class="sopsr-preview-stage" data-device="desktop">
								<div class="sopsr-preview-slide sopsr-news-slider" id="sopsr-slider-preview">
									<div class="sopsr-news-slider__arrows" aria-hidden="true">
										<span class="splide__arrow splide__arrow--prev"><svg viewBox="0 0 24 24" focusable="false"><path d="M15.5 4.5 8 12l7.5 7.5-1.4 1.4L5.2 12l8.9-8.9z"/></svg></span>
										<span class="splide__arrow splide__arrow--next"><svg viewBox="0 0 24 24" focusable="false"><path d="m8.5 19.5 7.5-7.5-7.5-7.5 1.4-1.4 8.9 8.9-8.9 8.9z"/></svg></span>
									</div>
									<div class="splide__pagination" aria-hidden="true"><span class="splide__pagination__page is-active"></span><span class="splide__pagination__page"></span><span class="splide__pagination__page"></span><span class="splide__pagination__page"></span><span class="splide__pagination__page"></span></div>
									<img class="sopsr-preview-image" src="<?php echo esc_url( self::preview_image_url() ); ?>" alt="">
									<div class="sopsr-preview-overlay"></div>
									<div class="sopsr-preview-content">
										<div class="sopsr-preview-inner">
											<div class="sopsr-preview-title sopsr-news-slider__title">Ukážkový názov aktuality ŠOP SR</div>
											<a class="sopsr-preview-cta sopsr-news-slider__cta" href="#">Viac informácií</a>
										</div>
									</div>
								</div>
							</div>
							<p class="description">Preview je vizuálny náhľad jedného slidu; frontend používa reálny Splide.</p>
						</div>

						<div class="sopsr-diagnostics-card">
							<h2>Diagnostika</h2>
							<table>
								<tr><th>Plugin</th><td><?php echo esc_html( SOPSR_NS_VERSION ); ?></td></tr>
								<tr><th>Splide</th><td><?php echo esc_html( SOPSR_NS_SPLIDE_VERSION ); ?> / <?php echo esc_html( $assets['source'] ); ?></td></tr>
								<tr><th>PHP</th><td><?php echo esc_html( PHP_VERSION ); ?></td></tr>
								<tr><th>WordPress</th><td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td></tr>
								<tr><th>Polylang</th><td><?php echo function_exists( 'pll_current_language' ) ? 'aktívny' : 'nenájdený'; ?></td></tr>
								<tr><th>Log</th><td><code><?php echo esc_html( SOPSR_News_Slider_Logger::path() ); ?></code></td></tr>
							</table>

							<?php if ( 'cdn' === $assets['source'] ) : ?>
								<p><strong>Splide sa teraz načítava z CDN.</strong> Pre produkciu si ho môžete jedným klikom uložiť lokálne do uploads.</p>
								<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sopsr_slider_download_splide' ), 'sopsr_slider_download_splide' ) ); ?>">Stiahnuť Splide lokálne</a>
							<?php else : ?>
								<p>Splide sa načítava lokálne.</p>
							<?php endif; ?>

							<h3>Posledné logy</h3>
							<pre class="sopsr-debug-log"><?php echo esc_html( SOPSR_News_Slider_Logger::tail( 80 ) ?: 'Log je zatiaľ prázdny.' ); ?></pre>
							<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=sopsr_slider_clear_log' ), 'sopsr_slider_clear_log' ) ); ?>">Vymazať log</a>
						</div>
					</aside>
				</div>
			</form>
		</div>
		<?php
	}

	public static function clear_log(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden', 403 );
		}
		check_admin_referer( 'sopsr_slider_clear_log' );
		$ok = SOPSR_News_Slider_Logger::clear();
		wp_safe_redirect( add_query_arg( 'sopsr_slider_notice', $ok ? 'log-cleared' : 'log-error', admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) );
		exit;
	}

	public static function download_splide(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Forbidden', 403 );
		}
		check_admin_referer( 'sopsr_slider_download_splide' );
		$result = SOPSR_News_Slider_Assets::download_splide_locally();
		$notice = is_wp_error( $result ) ? 'splide-error' : 'splide-downloaded';
		if ( is_wp_error( $result ) ) {
			set_transient( 'sopsr_slider_admin_error', $result->get_error_message(), 60 );
			SOPSR_News_Slider_Logger::log( 'error', 'Local Splide download failed.', array( 'error' => $result->get_error_message() ) );
		}
		wp_safe_redirect( add_query_arg( 'sopsr_slider_notice', $notice, admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ) );
		exit;
	}

	private static function notice(): void {
		$notice = isset( $_GET['sopsr_slider_notice'] ) ? sanitize_key( wp_unslash( $_GET['sopsr_slider_notice'] ) ) : '';
		if ( ! $notice ) {
			return;
		}

		$map = array(
			'log-cleared'       => array( 'success', 'Debug log bol vymazaný.' ),
			'log-error'         => array( 'error', 'Debug log sa nepodarilo vymazať.' ),
			'splide-downloaded' => array( 'success', 'Splide bol stiahnutý lokálne a frontend ho začne používať.' ),
		);

		if ( 'splide-error' === $notice ) {
			$message = get_transient( 'sopsr_slider_admin_error' );
			delete_transient( 'sopsr_slider_admin_error' );
			$map[ $notice ] = array( 'error', 'Splide sa nepodarilo stiahnuť lokálne: ' . ( $message ?: 'neznáma chyba' ) );
		}

		if ( isset( $map[ $notice ] ) ) {
			printf(
				'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
				esc_attr( $map[ $notice ][0] ),
				esc_html( $map[ $notice ][1] )
			);
		}
	}

	private static function preview_image_url(): string {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		foreach ( $posts as $post ) {
			$url = get_the_post_thumbnail_url( $post, 'large' );
			if ( $url ) {
				return $url;
			}
		}

		return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode( '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="500"><rect width="100%" height="100%" fill="#66766b"/><text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" fill="white" font-size="42" font-family="sans-serif">ŠOP SR Slider Preview</text></svg>' );
	}

	private static function section_open( string $id, string $title, string $description = '' ): void {
		$open = 'content' === $id ? ' open' : '';
		echo '<details class="sopsr-settings-section" id="sopsr-section-' . esc_attr( $id ) . '"' . $open . '>';
		echo '<summary><span><strong>' . esc_html( $title ) . '</strong>';
		if ( $description ) {
			echo '<small>' . esc_html( $description ) . '</small>';
		}
		echo '</span><button type="button" class="button-link sopsr-reset-section">Reset sekcie</button></summary><div class="sopsr-settings-section__body">';
	}

	private static function section_close(): void {
		echo '</div></details>';
	}

	private static function field_name( string $key ): string {
		preg_match_all( '/[^\\[\\]]+/', $key, $matches );
		$tokens = $matches[0] ?? array();
		$name   = SOPSR_News_Slider_Settings::OPTION_NAME;
		foreach ( $tokens as $token ) {
			$name .= '[' . $token . ']';
		}
		return $name;
	}

	private static function row_open( string $label, string $for = '', string $help = '' ): void {
		echo '<div class="sopsr-field">';
		echo '<div class="sopsr-field__label">';
		if ( $for ) {
			echo '<label for="' . esc_attr( $for ) . '"><strong>' . esc_html( $label ) . '</strong></label>';
		} else {
			echo '<strong>' . esc_html( $label ) . '</strong>';
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</div><div class="sopsr-field__control">';
	}

	private static function row_close(): void {
		echo '</div></div>';
	}

	private static function text( string $key, string $label, $value, $default, string $help = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		self::row_open( $label, $id, $help );
		printf( '<input class="regular-text" type="text" id="%1$s" name="%2$s" value="%3$s" data-default="%4$s">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $default ) );
		self::row_close();
	}

	private static function nested_text( string $key, string $label, $value, $default ): void {
		$id = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		echo '<div class="sopsr-language-field"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label>';
		printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" data-default="%4$s">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $default ) );
		echo '</div>';
	}

	private static function number( string $key, string $label, $value, $default, $min, $max, $step, string $help = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id, $help );
		printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" data-default="%7$s">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $min ), esc_attr( (string) $max ), esc_attr( (string) $step ), esc_attr( (string) $default ) );
		self::row_close();
	}

	private static function dimension( string $key, string $label, array $settings, array $defaults, $min, $max, $step ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id );
		self::dimension_inputs( $key, '', $settings, $defaults, $min, $max, $step, 'sopsr-dimension-field' );
		self::row_close();
	}

	private static function dimension_compact( string $key, string $label, array $settings, array $defaults, $min, $max, $step ): void {
		self::dimension_inputs( $key, $label, $settings, $defaults, $min, $max, $step, 'sopsr-font-field' );
	}

	private static function dimension_inputs( string $key, string $label, array $settings, array $defaults, $min, $max, $step, string $class ): void {
		$id       = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		$unit_key = $key . '_unit';
		$unit_id  = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $unit_key ) );
		$label_markup = '' !== $label ? '<span>' . esc_html( $label ) . '</span>' : '';
		echo '<div class="' . esc_attr( $class ) . '"><label class="sopsr-compact-field" for="' . esc_attr( $id ) . '">' . $label_markup;
		printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" data-default="%7$s"></label>', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $settings[ $key ] ), esc_attr( (string) $min ), esc_attr( (string) $max ), esc_attr( (string) $step ), esc_attr( (string) $defaults[ $key ] ) );
		echo '<label class="sopsr-compact-field" for="' . esc_attr( $unit_id ) . '"><span>Jednotka</span><select id="' . esc_attr( $unit_id ) . '" name="' . esc_attr( self::field_name( $unit_key ) ) . '" data-default="' . esc_attr( (string) ( $defaults[ $unit_key ] ?? 'px' ) ) . '">';
		foreach ( array( 'px', 'rem', 'em' ) as $unit ) {
			echo '<option value="' . esc_attr( $unit ) . '" ' . selected( $settings[ $unit_key ] ?? 'px', $unit, false ) . '>' . esc_html( $unit ) . '</option>';
		}
		echo '</select></label></div>';
	}

	private static function font_size( string $key, string $label, array $settings, array $defaults ): void {
		self::dimension_compact( $key, $label, $settings, $defaults, 0.01, 140, 0.01 );
	}

	private static function number_compact( string $key, string $label, $value, $default, $min, $max, $step, string $suffix ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		echo '<label class="sopsr-compact-field" for="' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span>';
		printf( '<span><input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" data-default="%7$s"> %8$s</span>', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $min ), esc_attr( (string) $max ), esc_attr( (string) $step ), esc_attr( (string) $default ), esc_html( $suffix ) );
		echo '</label>';
	}

	private static function checkbox( string $key, string $label, $value, $default, string $help = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id, $help );
		printf( '<input type="hidden" name="%1$s" value="0"><label class="sopsr-switch"><input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s data-default="%4$s"><span class="sopsr-switch__track"></span></label>', esc_attr( self::field_name( $key ) ), esc_attr( $id ), checked( ! empty( $value ), true, false ), esc_attr( ! empty( $default ) ? '1' : '0' ) );
		self::row_close();
	}

	private static function select( string $key, string $label, $value, $default, array $options, string $help = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id, $help );
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( self::field_name( $key ) ) . '" data-default="' . esc_attr( (string) $default ) . '">';
		foreach ( $options as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( (string) $option_value ) . '" ' . selected( (string) $value, (string) $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		echo '</select>';
		self::row_close();
	}

	private static function select_compact( string $key, string $label, $value, $default, array $options ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		echo '<label class="sopsr-compact-field" for="' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span><select id="' . esc_attr( $id ) . '" name="' . esc_attr( self::field_name( $key ) ) . '" data-default="' . esc_attr( (string) $default ) . '">';
		foreach ( $options as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( (string) $option_value ) . '" ' . selected( (string) $value, (string) $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		echo '</select></label>';
	}

	private static function select_from_list( string $key, string $label, $value, $default, array $options ): void {
		$assoc = array();
		foreach ( $options as $option ) {
			$assoc[ $option ] = $option;
		}
		self::select( $key, $label, $value, $default, $assoc );
	}

	private static function color( string $key, string $label, $value, $default ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id );
		printf( '<input class="sopsr-color-picker" type="text" id="%1$s" name="%2$s" value="%3$s" data-default="%4$s">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $default ) );
		self::row_close();
	}

	private static function range( string $key, string $label, $value, $default, $min, $max, $step, string $suffix ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id );
		printf( '<div class="sopsr-range"><input type="range" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" data-default="%7$s"><output>%3$s%8$s</output></div>', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $min ), esc_attr( (string) $max ), esc_attr( (string) $step ), esc_attr( (string) $default ), esc_html( $suffix ) );
		self::row_close();
	}

	private static function textarea( string $key, string $label, $value, $default, string $placeholder = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id, 'Použite {{slider}} ako selector aktuálnej inštancie.' );
		printf( '<textarea class="large-text code" rows="12" id="%1$s" name="%2$s" data-default="%3$s" placeholder="%4$s">%5$s</textarea>', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $default ), esc_attr( $placeholder ), esc_textarea( (string) $value ) );
		self::row_close();
	}

	private static function category_multiselect( string $key, string $label, array $selected_values, array $defaults, array $categories, string $help = '' ): void {
		$id = 'sopsr-' . sanitize_html_class( $key );
		self::row_open( $label, $id, $help );
		echo '<select multiple size="8" class="sopsr-multiselect" id="' . esc_attr( $id ) . '" name="' . esc_attr( self::field_name( $key ) ) . '[]" data-default="' . esc_attr( wp_json_encode( array_values( $defaults ) ) ) . '">';
		foreach ( $categories as $category ) {
			printf( '<option value="%1$d" %2$s>%3$s (#%1$d)</option>', (int) $category->term_id, selected( in_array( (int) $category->term_id, array_map( 'intval', $selected_values ), true ), true, false ), esc_html( $category->name ) );
		}
		echo '</select>';
		self::row_close();
	}

	private static function media_field( string $key, string $label, int $value, int $default ): void {
		$id  = 'sopsr-' . sanitize_html_class( $key );
		$url = $value ? wp_get_attachment_image_url( $value, 'medium' ) : '';
		self::row_open( $label, $id );
		printf( '<input type="hidden" id="%1$s" name="%2$s" value="%3$d" data-default="%4$d">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), $value, $default );
		echo '<div class="sopsr-media-field">';
		echo '<div class="sopsr-media-preview">';
		if ( $url ) {
			echo '<img src="' . esc_url( $url ) . '" alt="">';
		}
		echo '</div><div><button type="button" class="button sopsr-media-select">Vybrať obrázok</button> <button type="button" class="button-link-delete sopsr-media-remove">Odstrániť</button></div></div>';
		self::row_close();
	}

	private static function device_card( string $device, string $label, array $value, array $default ): void {
		echo '<div class="sopsr-device-card" data-device-card="' . esc_attr( $device ) . '"><h3>' . esc_html( $label ) . '</h3>';
		self::nested_number_compact( "responsive[$device][width_value]", 'Šírka', $value['width_value'], $default['width_value'], 1, 3000, 0.1 );
		self::nested_select_compact( "responsive[$device][width_unit]", 'Jednotka šírky', $value['width_unit'], $default['width_unit'], array( '%' => '%', 'px' => 'px', 'vw' => 'vw' ) );
		self::nested_select_compact( "responsive[$device][height_mode]", 'Výška', $value['height_mode'], $default['height_mode'], array( 'fixed' => 'Fixed', 'auto' => 'Auto podľa obrázka', 'aspect' => 'Aspect ratio', 'clamp' => 'Clamp', 'viewport' => 'Viewport (vh)' ) );
		self::nested_number_compact( "responsive[$device][height_value]", 'Hodnota výšky', $value['height_value'], $default['height_value'], 1, 3000, 0.1 );
		self::nested_select_compact( "responsive[$device][height_unit]", 'Jednotka výšky', $value['height_unit'], $default['height_unit'], array( 'px' => 'px', 'vh' => 'vh', 'vw' => 'vw' ) );
		echo '<div class="sopsr-device-subgrid">';
		self::nested_number_compact( "responsive[$device][aspect_w]", 'Aspect W', $value['aspect_w'], $default['aspect_w'], 0.1, 100, 0.1 );
		self::nested_number_compact( "responsive[$device][aspect_h]", 'Aspect H', $value['aspect_h'], $default['aspect_h'], 0.1, 100, 0.1 );
		self::nested_number_compact( "responsive[$device][clamp_min]", 'Clamp min px', $value['clamp_min'], $default['clamp_min'], 1, 3000, 1 );
		self::nested_number_compact( "responsive[$device][clamp_fluid]", 'Clamp fluid vw', $value['clamp_fluid'], $default['clamp_fluid'], 1, 200, 0.1 );
		self::nested_number_compact( "responsive[$device][clamp_max]", 'Clamp max px', $value['clamp_max'], $default['clamp_max'], 1, 4000, 1 );
		echo '</div>';
		self::nested_select_compact( "responsive[$device][fit]", 'Image fit', $value['fit'], $default['fit'], array( 'cover' => 'cover', 'contain' => 'contain', 'fill' => 'fill', 'none' => 'none', 'scale-down' => 'scale-down' ) );
		self::nested_number_compact( "responsive[$device][position_x]", 'Object position X', $value['position_x'], $default['position_x'], 0, 100, 1 );
		self::nested_number_compact( "responsive[$device][position_y]", 'Object position Y', $value['position_y'], $default['position_y'], 0, 100, 1 );
		echo '</div>';
	}

	private static function responsive_style_cards( string $type, array $settings, array $defaults ): void {
		echo '<div class="sopsr-responsive-overrides">';
		foreach ( array( 'tablet' => 'Tablet', 'mobile' => 'Mobile' ) as $device => $label ) {
			$value   = $settings['responsive'][ $device ];
			$default = $defaults['responsive'][ $device ];
			echo '<div class="sopsr-device-card"><h3>' . esc_html( $label ) . '</h3>';
			if ( 'content' === $type ) {
				self::nested_select_compact( "responsive[$device][content_halign]", 'Horizontálne zarovnanie obsahu', $value['content_halign'], $default['content_halign'], array( 'left' => 'Vľavo', 'center' => 'Na stred', 'right' => 'Vpravo' ) );
				self::nested_select_compact( "responsive[$device][content_valign]", 'Vertikálne zarovnanie obsahu', $value['content_valign'], $default['content_valign'], array( 'top' => 'Hore', 'center' => 'Na stred', 'bottom' => 'Dole' ) );
				self::nested_dimension_compact( $device, 'content_max_width', 'Max. šírka textového obsahu', $value, $default, 0.01, 2400 );
				self::nested_dimension_compact( $device, 'content_padding_x', 'Horizontálny padding obsahu', $value, $default, 0, 200 );
				self::nested_dimension_compact( $device, 'content_padding_y', 'Vertikálny padding obsahu', $value, $default, 0, 200 );
			} elseif ( 'title' === $type ) {
				self::nested_select_compact( "responsive[$device][title_weight]", 'Font weight', (string) $value['title_weight'], (string) $default['title_weight'], array( '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900' ) );
				self::nested_number_compact( "responsive[$device][title_line_height]", 'Line-height', $value['title_line_height'], $default['title_line_height'], 0.8, 2.5, 0.05 );
				self::nested_dimension_compact( $device, 'title_max_width', 'Max. šírka nadpisu', $value, $default, 0.01, 2000 );
				self::nested_checkbox_compact( "responsive[$device][title_text_shadow]", 'Text shadow', $value['title_text_shadow'], $default['title_text_shadow'] );
			} elseif ( 'cta' === $type ) {
				self::nested_dimension_compact( $device, 'cta_border_width', 'Border', $value, $default, 0, 100 );
				self::nested_dimension_compact( $device, 'cta_border_radius', 'Radius', $value, $default, 0, 200 );
				self::nested_dimension_compact( $device, 'cta_font_size', 'Font', $value, $default, 0.01, 140 );
				self::nested_select_compact( "responsive[$device][cta_font_weight]", 'Font weight', (string) $value['cta_font_weight'], (string) $default['cta_font_weight'], array( '300' => '300', '400' => '400', '500' => '500', '600' => '600', '700' => '700', '800' => '800', '900' => '900' ) );
				self::nested_dimension_compact( $device, 'cta_padding_y', 'Padding Y', $value, $default, 0, 200 );
				self::nested_dimension_compact( $device, 'cta_padding_x', 'Padding X', $value, $default, 0, 300 );
			} elseif ( 'controls' === $type ) {
				self::nested_dimension_compact( $device, 'controls_size', 'Veľkosť ovládacích tlačidiel', $value, $default, 0.01, 200 );
			}
			echo '</div>';
		}
		echo '</div>';
	}

	private static function title_spacing_controls( array $settings, array $defaults ): void {
		echo '<div class="sopsr-title-spacing"><div class="sopsr-title-spacing__tabs" role="group" aria-label="Zariadenie pre rozostupy nadpisu">';
		foreach ( array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ) as $device => $label ) {
			printf( '<button type="button" class="sopsr-title-spacing__tab%1$s" data-title-spacing-device="%2$s" aria-controls="sopsr-title-spacing-%2$s" aria-pressed="%3$s">%4$s</button>', 'desktop' === $device ? ' is-active' : '', esc_attr( $device ), 'desktop' === $device ? 'true' : 'false', esc_html( $label ) );
		}
		echo '</div>';
		foreach ( array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ) as $device => $label ) {
			$value = 'desktop' === $device ? $settings : $settings['responsive'][ $device ];
			$default = 'desktop' === $device ? $defaults : $defaults['responsive'][ $device ];
			echo '<div class="sopsr-title-spacing__panel" id="sopsr-title-spacing-' . esc_attr( $device ) . '" data-title-spacing-panel="' . esc_attr( $device ) . '"' . ( 'desktop' === $device ? '' : ' hidden' ) . '>';
			foreach ( array( 'title_padding' => 'Padding', 'title_margin' => 'Margin' ) as $property => $heading ) {
				echo '<fieldset class="sopsr-title-spacing__group"><legend>' . esc_html( $heading . ' – ' . $label ) . '</legend><div class="sopsr-title-spacing__sides">';
				foreach ( array( 'top' => 'Hore', 'right' => 'Vpravo', 'bottom' => 'Dole', 'left' => 'Vľavo' ) as $side => $side_label ) {
					$key = $property . '_' . $side;
					$field = 'desktop' === $device ? $key : "responsive[$device][$key]";
					$unit_field = 'desktop' === $device ? $key . '_unit' : "responsive[$device][{$key}_unit]";
					$id = 'desktop' === $device ? 'sopsr-' . $key : 'sopsr-title-spacing-' . $device . '-' . $key;
					echo '<div class="sopsr-title-spacing__side"><label for="' . esc_attr( $id ) . '">' . esc_html( $side_label ) . '</label>';
					printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" min="0" max="500" step="0.01" data-default="%4$s">', esc_attr( $id ), esc_attr( self::field_name( $field ) ), esc_attr( (string) $value[ $key ] ), esc_attr( (string) $default[ $key ] ) );
					$unit_id = $id . '-unit';
					echo '<label class="screen-reader-text" for="' . esc_attr( $unit_id ) . '">' . esc_html( $side_label . ' – jednotka' ) . '</label><select id="' . esc_attr( $unit_id ) . '" name="' . esc_attr( self::field_name( $unit_field ) ) . '" data-default="' . esc_attr( (string) $default[ $key . '_unit' ] ) . '">';
					foreach ( array( 'px', 'rem', 'em' ) as $unit ) {
						echo '<option value="' . esc_attr( $unit ) . '" ' . selected( $value[ $key . '_unit' ], $unit, false ) . '>' . esc_html( $unit ) . '</option>';
					}
					echo '</select></div>';
				}
				echo '</div></fieldset>';
			}
			echo '</div>';
		}
		echo '</div>';
	}

	private static function nested_dimension_compact( string $device, string $key, string $label, array $value, array $default, $min, $max ): void {
		echo '<div class="sopsr-device-subgrid">';
		self::nested_number_compact( "responsive[$device][$key]", $label, $value[ $key ], $default[ $key ], $min, $max, 0.01 );
		self::nested_select_compact( "responsive[$device][{$key}_unit]", 'Jednotka', $value[ $key . '_unit' ], $default[ $key . '_unit' ], array( 'px' => 'px', 'rem' => 'rem', 'em' => 'em' ) );
		echo '</div>';
	}

	private static function nested_checkbox_compact( string $key, string $label, $value, $default ): void {
		$id = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		echo '<div class="sopsr-device-field"><span>' . esc_html( $label ) . '</span>';
		printf( '<input type="hidden" name="%1$s" value="0"><label class="sopsr-switch"><input type="checkbox" id="%2$s" name="%1$s" value="1" %3$s data-default="%4$s"><span class="sopsr-switch__track"></span></label>', esc_attr( self::field_name( $key ) ), esc_attr( $id ), checked( ! empty( $value ), true, false ), esc_attr( ! empty( $default ) ? '1' : '0' ) );
		echo '</div>';
	}

	private static function nested_number_compact( string $key, string $label, $value, $default, $min, $max, $step ): void {
		$id = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		echo '<label class="sopsr-device-field" for="' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span>';
		printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" min="%4$s" max="%5$s" step="%6$s" data-default="%7$s">', esc_attr( $id ), esc_attr( self::field_name( $key ) ), esc_attr( (string) $value ), esc_attr( (string) $min ), esc_attr( (string) $max ), esc_attr( (string) $step ), esc_attr( (string) $default ) );
		echo '</label>';
	}

	private static function nested_select_compact( string $key, string $label, $value, $default, array $options ): void {
		$id = 'sopsr-' . sanitize_html_class( str_replace( array( '[', ']' ), '-', $key ) );
		echo '<label class="sopsr-device-field" for="' . esc_attr( $id ) . '"><span>' . esc_html( $label ) . '</span><select id="' . esc_attr( $id ) . '" name="' . esc_attr( self::field_name( $key ) ) . '" data-default="' . esc_attr( (string) $default ) . '">';
		foreach ( $options as $option_value => $option_label ) {
			echo '<option value="' . esc_attr( (string) $option_value ) . '" ' . selected( (string) $value, (string) $option_value, false ) . '>' . esc_html( $option_label ) . '</option>';
		}
		echo '</select></label>';
	}
}
