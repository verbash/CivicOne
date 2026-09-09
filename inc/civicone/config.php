<?php
/**
 * CivicOne — client-configurable IDs and defaults (filters).
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Primary wp_navigation post ID (header + drawer).
 */
function civicone_get_primary_nav_ref(): int {
	return (int) apply_filters( 'civicone_primary_nav_id', 791 );
}

/**
 * Default GiveLively donate URL (override per client in the child theme).
 */
function civicone_get_header_cta_donate_url_default(): string {
	return (string) apply_filters(
		'civicone_header_cta_donate_url_default',
		'https://secure.givelively.org/donate/neighborhoods-west-northwest'
	);
}
