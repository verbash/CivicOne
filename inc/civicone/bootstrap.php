<?php
/**
 * CivicOne — register component assets and theme integration hooks.
 *
 * The component files themselves are required from the theme functions.php
 * (before any hook fires); this file only registers their asset enqueues,
 * the textdomain loader, and the generator-meta removal.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue CivicOne component stylesheets (drawer, mobile, CTA).
 */
function civicone_enqueue_component_styles(): void {
	$deps_base = array( 'civicone' );

	$mobile = get_theme_file_path( 'assets/civicone-components-mobile.css' );
	if ( file_exists( $mobile ) ) {
		wp_enqueue_style(
			'civicone-mobile',
			get_theme_file_uri( 'assets/civicone-components-mobile.css' ),
			$deps_base,
			(string) filemtime( $mobile )
		);
	}

	$drawer = get_theme_file_path( 'assets/civicone-drawer-nav.css' );
	if ( file_exists( $drawer ) && function_exists( 'civicone_should_load_drawer_nav' ) && civicone_should_load_drawer_nav() ) {
		wp_enqueue_style(
			'civicone-drawer-nav',
			get_theme_file_uri( 'assets/civicone-drawer-nav.css' ),
			$deps_base,
			(string) filemtime( $drawer )
		);
	}

	$cta = get_theme_file_path( 'assets/civicone-header-cta.css' );
	if ( file_exists( $cta ) ) {
		wp_enqueue_style(
			'civicone-header-cta',
			get_theme_file_uri( 'assets/civicone-header-cta.css' ),
			array( 'civicone', 'civicone-critical-header' ),
			(string) filemtime( $cta )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'civicone_enqueue_component_styles', 15 );

/**
 * Drawer navigation script.
 */
function civicone_enqueue_component_scripts(): void {
	if ( ! function_exists( 'civicone_should_load_drawer_nav' ) || ! civicone_should_load_drawer_nav() ) {
		return;
	}

	$js = get_theme_file_path( 'assets/civicone-drawer-nav.js' );
	if ( ! file_exists( $js ) ) {
		return;
	}

	wp_enqueue_script(
		'civicone-drawer-nav',
		get_theme_file_uri( 'assets/civicone-drawer-nav.js' ),
		array(),
		(string) filemtime( $js ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'civicone_enqueue_component_scripts', 15 );

/**
 * Editor styles for component CSS (in addition to civicone.css).
 */
function civicone_editor_component_styles(): void {
	foreach ( array( 'civicone-drawer-nav.css', 'civicone-components-mobile.css', 'civicone-header-cta.css' ) as $file ) {
		$path = get_theme_file_path( 'assets/' . $file );
		if ( file_exists( $path ) ) {
			add_editor_style( 'assets/' . $file );
		}
	}
}
add_action( 'after_setup_theme', 'civicone_editor_component_styles', 11 );

/**
 * No public WordPress generator meta on the front end.
 */
function civicone_remove_public_wp_branding(): void {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'civicone_remove_public_wp_branding' );

/**
 * Load translations (GitHub-distributed theme — not on wp.org auto-loader).
 */
function civicone_load_textdomain(): void {
	load_theme_textdomain( 'civicone', get_stylesheet_directory() . '/languages' );
}
add_action( 'init', 'civicone_load_textdomain' );
