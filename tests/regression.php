<?php
// Standalone checks: php tests/regression.php (no WordPress installation required).
define( 'ABSPATH', __DIR__ );
set_error_handler( static function ( $level, $message, $file, $line ) {
	throw new ErrorException( $message, 0, $level, $file, $line );
} );
function get_option( $name, $default = array() ) { return $GLOBALS['saved'] ?? $default; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) ); }
function sanitize_html_class( $value ) { return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $value ); }
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function sanitize_hex_color( $value ) { return preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $value ) ? $value : null; }
function wp_unslash( $value ) { return stripslashes( $value ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function esc_html( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function selected( $value, $expected, $echo = true ) { return (string) $value === (string) $expected ? 'selected="selected"' : ''; }
function checked( $value, $expected, $echo = true ) { return (bool) $value === (bool) $expected ? 'checked="checked"' : ''; }
require __DIR__ . '/../includes/class-sopsr-news-slider-settings.php';
require __DIR__ . '/../includes/class-sopsr-news-slider-render.php';
require __DIR__ . '/../includes/class-sopsr-news-slider-admin.php';
function verify( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
$defaults = SOPSR_News_Slider_Settings::defaults();
foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		$key = $property . '_' . $side;
		verify( 0 === $defaults[ $key ] && 'px' === $defaults[ $key . '_unit' ], 'Zero title spacing default: ' . $key );
	}
}
$keys = array( 'title_clamp_min', 'title_clamp_max', 'title_desktop', 'title_tablet', 'title_mobile', 'cta_font_size' );
$dimension_keys = array( 'content_max_width', 'content_padding_x', 'content_padding_y', 'title_max_width', 'title_padding', 'title_margin', 'cta_border_width', 'cta_border_radius', 'cta_padding_y', 'cta_padding_x', 'controls_size' );
$responsive_style_keys = array(
	'content_halign', 'content_valign', 'content_max_width', 'content_max_width_unit',
	'content_padding_x', 'content_padding_x_unit', 'content_padding_y', 'content_padding_y_unit',
	'title_weight', 'title_line_height', 'title_max_width', 'title_max_width_unit', 'title_padding', 'title_padding_unit', 'title_margin', 'title_margin_unit', 'title_text_shadow',
	'cta_border_width', 'cta_border_width_unit', 'cta_border_radius', 'cta_border_radius_unit',
	'cta_font_size', 'cta_font_size_unit', 'cta_font_weight', 'cta_padding_y', 'cta_padding_y_unit',
	'cta_padding_x', 'cta_padding_x_unit', 'controls_size', 'controls_size_unit',
);
$legacy = $defaults;
foreach ( $keys as $key ) { unset( $legacy[ $key . '_unit' ] ); }
foreach ( $dimension_keys as $key ) { unset( $legacy[ $key . '_unit' ] ); }
foreach ( array( 'title_padding', 'title_margin' ) as $key ) { unset( $legacy[ $key ], $legacy[ $key . '_unit' ] ); }
foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		$key = $property . '_' . $side;
		unset( $legacy[ $key ], $legacy[ $key . '_unit' ] );
		foreach ( array( 'tablet', 'mobile' ) as $device ) { unset( $legacy['responsive'][ $device ][ $key ], $legacy['responsive'][ $device ][ $key . '_unit' ] ); }
	}
}
foreach ( array( 'tablet', 'mobile' ) as $device ) {
	foreach ( $responsive_style_keys as $key ) { unset( $legacy['responsive'][ $device ][ $key ] ); }
}
unset( $legacy['cta_focus_text_color'], $legacy['cta_focus_bg_color'] );
$legacy['cta_text_color'] = '#123456';
$legacy['cta_bg_color'] = '#abcdef';
$legacy['content_halign'] = 'right';
$legacy['content_max_width'] = 777;
$legacy['title_weight'] = 800;
$legacy['cta_font_weight'] = 500;
$legacy['controls_size'] = 55;
$GLOBALS['saved'] = $legacy;
$settings = SOPSR_News_Slider_Settings::get_all();
verify( $GLOBALS['saved'] === $legacy, 'Loading must not mutate saved settings.' );
foreach ( $keys as $key ) { verify( 'px' === $settings[ $key . '_unit' ], 'Legacy px fallback: ' . $key ); }
foreach ( $dimension_keys as $key ) { verify( 'px' === $settings[ $key . '_unit' ], 'Legacy dimension unit fallback: ' . $key ); }
verify( 0 === $settings['title_padding'] && 'px' === $settings['title_padding_unit'], 'Legacy title padding default.' );
verify( 0 === $settings['title_margin'] && 'px' === $settings['title_margin_unit'], 'Legacy title margin default.' );
foreach ( array( 'tablet', 'mobile' ) as $device ) {
	verify( 'right' === $settings['responsive'][ $device ]['content_halign'], 'Legacy content alignment inheritance.' );
	verify( 777 === $settings['responsive'][ $device ]['content_max_width'], 'Legacy content width inheritance.' );
	verify( 800 === $settings['responsive'][ $device ]['title_weight'], 'Legacy title weight inheritance.' );
	verify( 0 === $settings['responsive'][ $device ]['title_padding'] && 'px' === $settings['responsive'][ $device ]['title_padding_unit'], 'Legacy title padding inheritance.' );
	verify( 0 === $settings['responsive'][ $device ]['title_margin'] && 'px' === $settings['responsive'][ $device ]['title_margin_unit'], 'Legacy title margin inheritance.' );
	verify( 500 === $settings['responsive'][ $device ]['cta_font_weight'], 'Legacy CTA weight inheritance.' );
	verify( 55 === $settings['responsive'][ $device ]['controls_size'], 'Legacy control size inheritance.' );
}
verify( '#123456' === $settings['cta_focus_text_color'] && '#abcdef' === $settings['cta_focus_bg_color'], 'Legacy CTA palette.' );
$css_method = new ReflectionMethod( SOPSR_News_Slider_Render::class, 'dynamic_css' );
$css_method->setAccessible( true );
$legacy_css = $css_method->invoke( null, 'test-slider', $settings );
verify( str_contains( $legacy_css, 'clamp(26px,3.1vw,52px)' ), 'Legacy clamp CSS.' );
verify( str_contains( $legacy_css, '--sopsr-cta-font:16px' ), 'Legacy CTA CSS.' );
$old_spacing = $legacy;
$old_spacing['title_padding'] = 7;
$old_spacing['title_padding_unit'] = 'rem';
$old_spacing['title_margin'] = 9;
$old_spacing['title_margin_unit'] = 'em';
$old_spacing['responsive']['tablet']['title_margin'] = 3;
$old_spacing['responsive']['tablet']['title_margin_unit'] = 'px';
$GLOBALS['saved'] = $old_spacing;
$migrated = SOPSR_News_Slider_Settings::get_all();
verify( 7 === $migrated['title_padding_top'] && 'rem' === $migrated['title_padding_left_unit'], 'Old title padding inheritance.' );
verify( 3 === $migrated['responsive']['tablet']['title_margin_right'] && 'px' === $migrated['responsive']['tablet']['title_margin_right_unit'], 'Old tablet title margin inheritance.' );
verify( 9 === $migrated['responsive']['mobile']['title_margin_bottom'] && 'em' === $migrated['responsive']['mobile']['title_margin_bottom_unit'], 'Old mobile title margin inheritance.' );
verify( str_contains( $css_method->invoke( null, 'test-slider', $migrated ), 'padding:7rem 7rem 7rem 7rem;margin:9em 9em 9em 9em;' ), 'Old title spacing CSS.' );
foreach ( array( 'px', 'rem', 'em' ) as $unit ) {
	$input = $defaults;
	foreach ( $keys as $key ) { $input[ $key ] = '1.25'; $input[ $key . '_unit' ] = $unit; }
	foreach ( $dimension_keys as $key ) { $input[ $key ] = '1.25'; $input[ $key . '_unit' ] = $unit; }
	foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$key = $property . '_' . $side;
			$input[ $key ] = '1.25';
			$input[ $key . '_unit' ] = $unit;
		}
	}
	foreach ( array( 'tablet', 'mobile' ) as $device ) {
		foreach ( array( 'content_max_width', 'content_padding_x', 'content_padding_y', 'title_max_width', 'title_padding', 'title_margin', 'cta_border_width', 'cta_border_radius', 'cta_font_size', 'cta_padding_y', 'cta_padding_x', 'controls_size' ) as $key ) {
			$input['responsive'][ $device ][ $key ] = '2.5';
			$input['responsive'][ $device ][ $key . '_unit' ] = $unit;
		}
		foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				$key = $property . '_' . $side;
				$input['responsive'][ $device ][ $key ] = '2.5';
				$input['responsive'][ $device ][ $key . '_unit' ] = $unit;
			}
		}
		$input['responsive'][ $device ]['content_halign'] = 'left';
		$input['responsive'][ $device ]['content_valign'] = 'bottom';
		$input['responsive'][ $device ]['title_weight'] = '300';
		$input['responsive'][ $device ]['title_line_height'] = '1.2';
		$input['responsive'][ $device ]['title_text_shadow'] = 0;
		$input['responsive'][ $device ]['cta_font_weight'] = '400';
	}
	$input['title_clamp_max'] = '3.5';
	$input['cta_focus_text_color'] = '#fedcba';
	$input['cta_focus_bg_color'] = '#456789';
	$input['custom_css'] = '{{slider}} .example{color:red;}';
	$out = SOPSR_News_Slider_Settings::sanitize( $input );
	foreach ( $keys as $key ) { verify( is_float( $out[ $key ] ) && $unit === $out[ $key . '_unit' ], 'Numeric value and unit preserved.' ); }
	foreach ( $dimension_keys as $key ) { verify( is_float( $out[ $key ] ) && $unit === $out[ $key . '_unit' ], 'Dimension value and unit preserved.' ); }
	$css = $css_method->invoke( null, 'test-slider', $out );
	verify( substr_count( $css, '{' ) === substr_count( $css, '}' ), 'Generated CSS braces.' );
	verify( str_contains( $css, "clamp(1.25{$unit},3.1vw,3.5{$unit})" ), 'Clamp units.' );
	verify( str_contains( $css, "--sopsr-cta-font:1.25{$unit}" ), 'CTA units.' );
	verify( str_contains( $css, "--sopsr-content-max:1.25{$unit}" ), 'Desktop content units.' );
	verify( str_contains( $css, "--sopsr-control-size:1.25{$unit}" ), 'Desktop control units.' );
	verify( str_contains( $css, "--sopsr-content-max:2.5{$unit}" ), 'Responsive content units.' );
	verify( str_contains( $css, "--sopsr-cta-radius:2.5{$unit}" ), 'Responsive CTA units.' );
	verify( str_contains( $css, "--sopsr-control-size:2.5{$unit}" ), 'Responsive control units.' );
	verify( str_contains( $css, '.sopsr-news-slider__content{align-items:end;justify-items:start;text-align:left;}' ), 'Responsive content alignment.' );
	verify( str_contains( $css, 'font-weight:300;line-height:1.2;max-width:2.5' . $unit . ';padding:' . implode( ' ', array_fill( 0, 4, '2.5' . $unit ) ) . ';margin:' . implode( ' ', array_fill( 0, 4, '2.5' . $unit ) ) . ';text-shadow:none;' ), 'Responsive title styling.' );
	verify( str_contains( $css, '--sopsr-cta-focus-text:#fedcba;--sopsr-cta-focus-bg:#456789' ), 'Focus palette.' );
	verify( str_contains( $css, '#test-slider .example' ) && ! str_contains( $css, '{{slider}}' ), 'Custom CSS token.' );
	$out['title_font_mode'] = 'responsive';
	$css = $css_method->invoke( null, 'test-slider', $out );
	verify( 3 === substr_count( $css, "font-size:1.25{$unit}" ), 'All responsive title sizes.' );
}
$separate = $defaults;
foreach ( array( 'top' => array( 1, 'px' ), 'right' => array( 2, 'rem' ), 'bottom' => array( 3, 'em' ), 'left' => array( 4, 'px' ) ) as $side => $pair ) {
	$separate['title_margin_' . $side] = $pair[0];
	$separate['title_margin_' . $side . '_unit'] = $pair[1];
}
$separate['responsive']['tablet']['title_margin_top'] = 5;
$separate['responsive']['tablet']['title_margin_top_unit'] = 'rem';
$separate['title_padding_left'] = 6;
$separate['title_padding_left_unit'] = 'em';
$separate = SOPSR_News_Slider_Settings::sanitize( $separate );
$separate_css = $css_method->invoke( null, 'test-slider', $separate );
verify( str_contains( $separate_css, 'margin:1px 2rem 3em 4px;' ), 'Independent desktop title margin sides.' );
verify( str_contains( $separate_css, 'margin:5rem 0px 0px 0px;' ), 'Independent tablet title margin sides.' );
verify( str_contains( $separate_css, 'padding:0px 0px 0px 6em;' ), 'Independent desktop title padding sides.' );
foreach ( array( 'vw', 'REM', 'px;color:red', array( 'em' ), '' ) as $bad_unit ) {
	$input = $defaults;
	foreach ( $keys as $key ) { $input[ $key . '_unit' ] = $bad_unit; $input[ $key ] = 'invalid'; }
	foreach ( $dimension_keys as $key ) { $input[ $key . '_unit' ] = $bad_unit; $input[ $key ] = 'invalid'; }
	foreach ( array( 'tablet', 'mobile' ) as $device ) {
		foreach ( array( 'content_max_width', 'content_padding_x', 'content_padding_y', 'title_max_width', 'title_padding', 'title_margin', 'cta_border_width', 'cta_border_radius', 'cta_font_size', 'cta_padding_y', 'cta_padding_x', 'controls_size' ) as $key ) {
			$input['responsive'][ $device ][ $key . '_unit' ] = $bad_unit;
			$input['responsive'][ $device ][ $key ] = 'invalid';
		}
	}
	$input['cta_focus_text_color'] = 'red; background:url(x)';
	$out = SOPSR_News_Slider_Settings::sanitize( $input );
	foreach ( $keys as $key ) { verify( 'px' === $out[ $key . '_unit' ] && (float) $defaults[ $key ] === $out[ $key ], 'Invalid input fallback.' ); }
	foreach ( $dimension_keys as $key ) { verify( 'px' === $out[ $key . '_unit' ] && (float) $defaults[ $key ] === $out[ $key ], 'Invalid dimension fallback.' ); }
	foreach ( array( 'tablet', 'mobile' ) as $device ) {
		verify( 'px' === $out['responsive'][ $device ]['controls_size_unit'], 'Invalid responsive unit fallback.' );
		verify( (float) $defaults['controls_size'] === $out['responsive'][ $device ]['controls_size'], 'Invalid responsive value fallback.' );
	}
	verify( $out['cta_focus_text_color'] === $defaults['cta_focus_text_color'], 'Focus color sanitization.' );
}
$cards_method = new ReflectionMethod( SOPSR_News_Slider_Admin::class, 'responsive_style_cards' );
$cards_method->setAccessible( true );
$card_keys = array(
	'content'  => array( 'content_halign', 'content_valign', 'content_max_width', 'content_max_width_unit', 'content_padding_x', 'content_padding_x_unit', 'content_padding_y', 'content_padding_y_unit' ),
	'title'    => array( 'title_weight', 'title_line_height', 'title_max_width', 'title_max_width_unit', 'title_text_shadow' ),
	'cta'      => array( 'cta_border_width', 'cta_border_width_unit', 'cta_border_radius', 'cta_border_radius_unit', 'cta_font_size', 'cta_font_size_unit', 'cta_font_weight', 'cta_padding_y', 'cta_padding_y_unit', 'cta_padding_x', 'cta_padding_x_unit' ),
	'controls' => array( 'controls_size', 'controls_size_unit' ),
);
foreach ( $card_keys as $type => $expected_keys ) {
	ob_start();
	$cards_method->invoke( null, $type, $defaults, $defaults );
	$html = ob_get_clean();
	foreach ( array( 'tablet', 'mobile' ) as $device ) {
		foreach ( $expected_keys as $key ) {
			$name = 'sopsr_news_slider_settings[responsive][' . $device . '][' . $key . ']';
			verify( str_contains( $html, 'name="' . $name . '"' ), 'Missing admin field: ' . $name );
		}
	}
	preg_match_all( '/\sid="([^"]+)"/', $html, $ids );
	verify( count( $ids[1] ) === count( array_unique( $ids[1] ) ), 'Duplicate responsive admin field ID in ' . $type . '.' );
}
foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
	foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
		$key = $property . '_' . $side;
		$defaults[ $key ] = 0;
		$defaults[ $key . '_unit' ] = 'px';
	}
}
$spacing_method = new ReflectionMethod( SOPSR_News_Slider_Admin::class, 'title_spacing_controls' );
$spacing_method->setAccessible( true );
ob_start();
$spacing_method->invoke( null, $defaults, $defaults );
$spacing_html = ob_get_clean();
foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
	foreach ( array( 'title_padding', 'title_margin' ) as $property ) {
		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
			$key = $property . '_' . $side;
			$name = 'sopsr_news_slider_settings' . ( 'desktop' === $device ? '[' . $key . ']' : '[responsive][' . $device . '][' . $key . ']' );
			verify( str_contains( $spacing_html, 'name="' . $name . '"' ), 'Missing title spacing field: ' . $name );
			$unit_name = substr( $name, 0, -1 ) . '_unit]';
			verify( str_contains( $spacing_html, 'name="' . $unit_name . '"' ), 'Missing title spacing unit: ' . $unit_name );
		}
	}
}
echo "PASS: legacy inheritance, responsive admin fields, title padding/margin, px/rem/em dimensions, media CSS, invalid input and Custom CSS.\n";
