<?php
/**
 * CivicOne — server-rendered dynamic blocks.
 *
 * Registers the civicone/section-subnav block, whose front-end and editor
 * preview both render through civicone_render_section_subnav(). The block is
 * placed before core/post-content in templates/page.html; the render_block
 * fallback in section-nav.php covers DB-customized templates that lack it, and
 * a shared de-dup flag keeps the two from double-rendering.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the section-subnav editor script and the block type.
 */
function civicone_register_blocks(): void {
	wp_register_script(
		'civicone-section-subnav-editor',
		get_theme_file_uri( '/blocks/section-subnav/index.js' ),
		array( 'wp-blocks', 'wp-block-editor', 'wp-element', 'wp-server-side-render', 'wp-i18n' ),
		CIVICONE_THEME_VERSION,
		true
	);

	register_block_type(
		get_theme_file_path( '/blocks/section-subnav' ),
		array( 'render_callback' => 'civicone_render_section_subnav_block' )
	);
}
add_action( 'init', 'civicone_register_blocks' );

/**
 * Render callback for civicone/section-subnav.
 *
 * @param array<string, mixed> $attrs   Block attributes.
 * @param string               $content Inner content (unused).
 * @return string
 */
function civicone_render_section_subnav_block( $attrs = array(), $content = '' ): string {
	unset( $attrs, $content );

	// Already emitted this request (e.g. by the post-content fallback) — skip.
	if ( civicone_section_subnav_already_rendered() ) {
		return '';
	}

	$section = civicone_get_current_section_config();

	if ( null === $section ) {
		// In the editor (REST render) there is no front-end section context, so
		// show a small placeholder instead of a blank block.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return sprintf(
				'<div class="civicone-section-subnav-placeholder">%s</div>',
				esc_html__( 'CivicOne section sub-nav — appears on section pages; edit tabs in Appearance → Menus', 'civicone' )
			);
		}

		// Off a section page on the front end: render nothing, and do not mark
		// rendered so the fallback stays free to act elsewhere.
		return '';
	}

	civicone_section_subnav_mark_rendered();

	$inner = civicone_render_section_subnav( $section );

	return sprintf( '<div %s>%s</div>', get_block_wrapper_attributes(), $inner );
}
