<?php
/**
 * CivicOne — self-contained child theme of Twenty Twenty-Two.
 *
 * Folds in the former CivicOne plugin: header, drawer, section nav, patterns,
 * block styles, and base styles (assets/civicone.css) all load from the theme.
 * There is no separate plugin.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme version — used as the enqueue-version fallback when filemtime() fails.
 * Sourced from the style.css header so it stays in sync with the theme.
 * (Replaces the former plugin version constant.)
 */
if ( ! defined( 'CIVICONE_THEME_VERSION' ) ) {
	define( 'CIVICONE_THEME_VERSION', (string) wp_get_theme()->get( 'Version' ) );
}

/**
 * Enqueue CivicOne base stylesheet (tokens + global civicone rules).
 */
function civicone_enqueue_styles(): void {
	$path = get_theme_file_path( 'assets/civicone.css' );
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : CIVICONE_THEME_VERSION;

	wp_enqueue_style(
		'civicone',
		get_theme_file_uri( 'assets/civicone.css' ),
		array(),
		$ver
	);
}
add_action( 'wp_enqueue_scripts', 'civicone_enqueue_styles' );

/**
 * Register civicone.css for the block editor.
 */
function civicone_editor_styles(): void {
	$path = get_theme_file_path( 'assets/civicone.css' );
	if ( file_exists( $path ) ) {
		add_editor_style( 'assets/civicone.css' );
	}
}
add_action( 'after_setup_theme', 'civicone_editor_styles' );

/*
 * CivicOne components (formerly the CivicOne plugin). Required at the top of
 * functions.php — before after_setup_theme / init / wp_enqueue_scripts fire —
 * so every civicone_* function exists before any hook runs. Config/registry
 * load first; patterns last (so civicone_header_bar_markup() etc. already
 * exist); bootstrap (component asset enqueues + textdomain) last of the group.
 */
require_once get_stylesheet_directory() . '/inc/civicone/config.php';
require_once get_stylesheet_directory() . '/inc/civicone/section-registry.php';
require_once get_stylesheet_directory() . '/inc/civicone/header-bar.php';
require_once get_stylesheet_directory() . '/inc/civicone/drawer-nav.php';
require_once get_stylesheet_directory() . '/inc/civicone/header-stability.php';
require_once get_stylesheet_directory() . '/inc/civicone/header-cta.php';
require_once get_stylesheet_directory() . '/inc/civicone/section-nav.php';
require_once get_stylesheet_directory() . '/inc/civicone/section-title.php';
require_once get_stylesheet_directory() . '/inc/civicone/blocks.php';
require_once get_stylesheet_directory() . '/inc/civicone/embed-wrapper.php';
require_once get_stylesheet_directory() . '/inc/civicone/hub-anchors.php';
require_once get_stylesheet_directory() . '/inc/civicone/heading-styles.php';
require_once get_stylesheet_directory() . '/inc/civicone/patterns.php';
require_once get_stylesheet_directory() . '/inc/civicone/bootstrap.php';

/*
 * D4C client shell (theme config, blog helpers, section tabs).
 */
require_once get_stylesheet_directory() . '/inc/d4c-section-config.php';
require_once get_stylesheet_directory() . '/inc/blog.php';
require_once get_stylesheet_directory() . '/inc/d4c-section-tabs.php';
require_once get_stylesheet_directory() . '/inc/d4c-section-tabs-seed.php';
