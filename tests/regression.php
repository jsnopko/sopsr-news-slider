<?php
// Standalone checks: php tests/regression.php (no WordPress installation required).
define( 'ABSPATH', __DIR__ );
set_error_handler( static function ( $level, $message, $file, $line ) {
	throw new ErrorException( $message, 0, $level, $file, $line );
} );
function get_option( $name, $default = array() ) { return $GLOBALS['saved'] ?? $default; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $value ) ); }
function sanitize_text_field( $value ) { return strip_tags( $value ); }
function sanitize_hex_color( $value ) { return preg_match( '/^#(?:[a-f0-9]{3}|[a-f0-9]{6})$/i', $value ) ? $value : null; }
function wp_unslash( $value ) { return stripslashes( $value ); }
require __DIR__ . '/../includes/class-sopsr-news-slider-settings.php';
require __DIR__ . '/../includes/class-sopsr-news-slider-render.php';
function verify( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
}
$defaults = SOPSR_News_Slider_Settings::defaults();
$keys = array( 'title_clamp_min', 'title_clamp_max', 'title_desktop', 'title_tablet', 'title_mobile', 'cta_font_size' );
$legacy = $defaults;
foreach ( $keys as $key ) { unset( $legacy[ $key . '_unit' ] ); }
unset( $legacy['cta_focus_text_color'], $legacy['cta_focus_bg_color'] );
$legacy['cta_text_color'] = '#123456';
$legacy['cta_bg_color'] = '#abcdef';
$GLOBALS['saved'] = $legacy;
$settings = SOPSR_News_Slider_Settings::get_all();
verify( $GLOBALS['saved'] === $legacy, 'Loading must not mutate saved settings.' );
foreach ( $keys as $key ) { verify( 'px' === $settings[ $key . '_unit' ], 'Legacy px fallback: ' . $key ); }
verify( '#123456' === $settings['cta_focus_text_color'] && '#abcdef' === $settings['cta_focus_bg_color'], 'Legacy CTA palette.' );
$css_method = new ReflectionMethod( SOPSR_News_Slider_Render::class, 'dynamic_css' );
$css_method->setAccessible( true );
$legacy_css = $css_method->invoke( null, 'test-slider', $legacy );
verify( str_contains( $legacy_css, 'clamp(26px,3.1vw,52px)' ), 'Legacy clamp CSS.' );
verify( str_contains( $legacy_css, '--sopsr-cta-font:16px' ), 'Legacy CTA CSS.' );
foreach ( array( 'px', 'rem', 'em' ) as $unit ) {
	$input = $defaults;
	foreach ( $keys as $key ) { $input[ $key ] = '1.25'; $input[ $key . '_unit' ] = $unit; }
	$input['title_clamp_max'] = '3.5';
	$input['cta_focus_text_color'] = '#fedcba';
	$input['cta_focus_bg_color'] = '#456789';
	$input['custom_css'] = '{{slider}} .example{color:red;}';
	$out = SOPSR_News_Slider_Settings::sanitize( $input );
	foreach ( $keys as $key ) { verify( is_float( $out[ $key ] ) && $unit === $out[ $key . '_unit' ], 'Numeric value and unit preserved.' ); }
	$css = $css_method->invoke( null, 'test-slider', $out );
	verify( str_contains( $css, "clamp(1.25{$unit},3.1vw,3.5{$unit})" ), 'Clamp units.' );
	verify( str_contains( $css, "--sopsr-cta-font:1.25{$unit}" ), 'CTA units.' );
	verify( str_contains( $css, '--sopsr-cta-focus-text:#fedcba;--sopsr-cta-focus-bg:#456789' ), 'Focus palette.' );
	verify( str_contains( $css, '#test-slider .example' ) && ! str_contains( $css, '{{slider}}' ), 'Custom CSS token.' );
	$out['title_font_mode'] = 'responsive';
	$css = $css_method->invoke( null, 'test-slider', $out );
	verify( 3 === substr_count( $css, "font-size:1.25{$unit}" ), 'All responsive title sizes.' );
}
foreach ( array( 'vw', 'REM', 'px;color:red', array( 'em' ), '' ) as $bad_unit ) {
	$input = $defaults;
	foreach ( $keys as $key ) { $input[ $key . '_unit' ] = $bad_unit; $input[ $key ] = 'invalid'; }
	$input['cta_focus_text_color'] = 'red; background:url(x)';
	$out = SOPSR_News_Slider_Settings::sanitize( $input );
	foreach ( $keys as $key ) { verify( 'px' === $out[ $key . '_unit' ] && (float) $defaults[ $key ] === $out[ $key ], 'Invalid input fallback.' ); }
	verify( $out['cta_focus_text_color'] === $defaults['cta_focus_text_color'], 'Focus color sanitization.' );
}
echo "PASS: legacy settings, all font units, decimal values, invalid input, responsive/clamp CSS, focus palette and Custom CSS.\n";
