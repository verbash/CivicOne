<?php
/**
 * Blog / news archive helpers (read time, shared query markup lives in templates).
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current view is a post listing (blog page or archives).
 */
function civicone_is_post_listing_view(): bool {
	if ( is_singular( 'post' ) ) {
		return false;
	}

	if ( is_home() || is_archive() ) {
		return true;
	}

	$blog_page_id = (int) get_option( 'page_for_posts' );
	if ( $blog_page_id && is_page( $blog_page_id ) ) {
		return true;
	}

	// News page (post 9) when not set as posts page.
	if ( is_page( 'blog' ) ) {
		return true;
	}

	return false;
}

/**
 * Append estimated read time after post date in query loops.
 *
 * @param string   $block_content Rendered block HTML.
 * @param array    $block         Block payload.
 * @param WP_Block $instance      Block instance.
 */
function civicone_append_read_time_to_post_date( string $block_content, array $block, WP_Block $instance ): string {
	if ( 'core/post-date' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	if ( ! civicone_is_post_listing_view() ) {
		return $block_content;
	}

	$post_id = (int) get_the_ID();
	if ( $post_id <= 0 ) {
		return $block_content;
	}

	$content = (string) get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$mins    = max( 1, (int) ceil( $words / 200 ) );

	return $block_content . sprintf(
		'<span class="civicone-read-time"> · %s</span>',
		esc_html(
			sprintf(
				/* translators: %d: minutes to read */
				_n( '%d min read', '%d min read', $mins, 'civicone' ),
				$mins
			)
		)
	);
}
add_filter( 'render_block', 'civicone_append_read_time_to_post_date', 10, 3 );
