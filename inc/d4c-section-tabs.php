<?php
/**
 * D4C — section tab rows, editable in wp-admin (Appearance → Menus).
 *
 * Each section declared in d4c-section-config.php gets a nav-menu location
 * "d4c-tabs-{slug}". Assign a menu to that location and its top-level items
 * replace that section's hardcoded tab row. Leave it unassigned (or assign an
 * empty menu) and the PHP defaults are used unchanged.
 *
 * Staff can then edit labels, URLs, order and membership of the tab row from
 * Appearance → Menus with no code deploy.
 *
 * COMPAT SHIM — this file hooks BOTH filter names on purpose:
 *   civic_1_section_configs   → production today (Civic-1 plugin)
 *   civicone_section_configs  → CivicOne >= 0.1.0
 * Once production is running CivicOne, delete the civic_1_ registration in
 * d4c_section_tabs_boot() and the d4c_section_tabs_filter_names() legacy entry.
 * Tracked in docs/CIVICONE-HANDOFF.md.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter names this shim binds to (legacy first, current second).
 *
 * @return list<string>
 */
function d4c_section_tabs_filter_names(): array {
	return array(
		'civic_1_section_configs',
		'civicone_section_configs',
	);
}

/**
 * Nav-menu location slug for a section.
 *
 * @param string $section_slug Section slug (e.g. resources).
 */
function d4c_section_tabs_menu_location( string $section_slug ): string {
	return 'd4c-tabs-' . sanitize_key( $section_slug );
}

/**
 * Hardcoded section defaults, read straight from the config file.
 *
 * Calls d4c_register_section_configs() directly rather than the filter, so this
 * never recurses through its own override.
 *
 * @return list<array<string, mixed>>
 */
function d4c_section_tabs_defaults(): array {
	if ( ! function_exists( 'd4c_register_section_configs' ) ) {
		return array();
	}

	return d4c_register_section_configs( array() );
}

/**
 * Register one nav-menu location per section.
 */
function d4c_section_tabs_register_locations(): void {
	$locations = array();

	foreach ( d4c_section_tabs_defaults() as $section ) {
		$slug = (string) ( $section['slug'] ?? '' );
		if ( '' === $slug ) {
			continue;
		}

		$locations[ d4c_section_tabs_menu_location( $slug ) ] = sprintf(
			/* translators: %s: section label, e.g. Resources. */
			__( 'Section tabs — %s', 'civicone' ),
			(string) ( $section['label'] ?? $slug )
		);
	}

	if ( ! empty( $locations ) ) {
		register_nav_menus( $locations );
	}
}

/**
 * Derive the page slug a tab points at (used for active-tab matching).
 *
 * @param WP_Post $item Nav menu item.
 */
function d4c_section_tabs_item_slug( WP_Post $item ): string {
	if ( 'post_type' === $item->type && 'page' === $item->object ) {
		$name = get_post_field( 'post_name', (int) $item->object_id );
		if ( is_string( $name ) && '' !== $name ) {
			return $name;
		}
	}

	// Off-site tabs have no local page to match — leave the slug empty so they
	// can never be marked as the active tab.
	$host = (string) wp_parse_url( (string) $item->url, PHP_URL_HOST );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( '' !== $host && '' !== $home && strtolower( $host ) !== strtolower( $home ) ) {
		return '';
	}

	$path = (string) wp_parse_url( (string) $item->url, PHP_URL_PATH );
	$path = trim( $path, '/' );
	if ( '' === $path ) {
		return '';
	}

	$segments = explode( '/', $path );

	return sanitize_title( (string) end( $segments ) );
}

/**
 * Menu ID assigned to a section's tab location, or 0 when none.
 *
 * @param string $section_slug Section slug.
 */
function d4c_section_tabs_menu_id( string $section_slug ): int {
	$location = d4c_section_tabs_menu_location( $section_slug );
	$menus    = get_nav_menu_locations();

	return (int) ( $menus[ $location ] ?? 0 );
}

/**
 * Assigned menu's name for a section, trimmed, or '' when no usable menu.
 *
 * The menu name is the section heading shown on child pages — see the seed
 * file for the "menu title is the heading, menu items are the tabs" model.
 *
 * @param string $section_slug Section slug.
 */
function d4c_section_tabs_menu_name( string $section_slug ): string {
	$menu_id = d4c_section_tabs_menu_id( $section_slug );
	if ( $menu_id <= 0 ) {
		return '';
	}

	$menu = wp_get_nav_menu_object( $menu_id );
	if ( ! $menu instanceof WP_Term ) {
		return '';
	}

	return trim( (string) $menu->name );
}

/**
 * Tabs for a section, read from its assigned menu.
 *
 * @param string $section_slug Section slug.
 * @return list<array{label: string, url: string, slug: string}> Empty when no usable menu.
 */
function d4c_section_tabs_from_menu( string $section_slug ): array {
	$menu_id = d4c_section_tabs_menu_id( $section_slug );

	if ( $menu_id <= 0 ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! is_array( $items ) ) {
		return array();
	}

	$tabs = array();

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post ) {
			continue;
		}

		// Tab rows are flat — ignore submenu items.
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}

		$label = trim( wp_strip_all_tags( (string) $item->title ) );
		$url   = trim( (string) $item->url );

		if ( '' === $label || '' === $url || '#' === $url ) {
			continue;
		}

		$tabs[] = array(
			'label' => $label,
			'url'   => $url,
			'slug'  => d4c_section_tabs_item_slug( $item ),
		);
	}

	return $tabs;
}

/**
 * Replace hardcoded tab rows with menu-driven ones where a menu is assigned.
 *
 * Falls back to the incoming config for any section without a usable menu, so a
 * missing or empty menu can never blank out a tab row on the front end.
 *
 * @param mixed $configs Section configs from d4c-section-config.php.
 * @return mixed
 */
function d4c_section_tabs_apply( $configs ) {
	if ( ! is_array( $configs ) ) {
		return $configs;
	}

	foreach ( $configs as $index => $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}

		$slug = (string) ( $section['slug'] ?? '' );
		if ( '' === $slug ) {
			continue;
		}

		// When a menu is assigned, its title becomes the section heading — even
		// if it has no usable items (tabs then keep their default below). When
		// no menu is assigned, leave the hardcoded default label untouched.
		$menu_name = d4c_section_tabs_menu_name( $slug );
		if ( '' !== $menu_name ) {
			$configs[ $index ]['label'] = $menu_name;
		}

		$tabs = d4c_section_tabs_from_menu( $slug );
		if ( empty( $tabs ) ) {
			continue;
		}

		$configs[ $index ]['tabs'] = $tabs;
	}

	return $configs;
}

/**
 * Point staff at the right screen from the Menus admin.
 */
function d4c_section_tabs_admin_notice(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( null === $screen || 'nav-menus' !== $screen->id ) {
		return;
	}

	echo '<div class="notice notice-info"><p>';
	echo esc_html__(
		'Section headings and tabs: assign a menu to a “Section tabs — …” location to control that section. The menu’s title becomes the section heading shown on its child pages, and the menu’s items become the tab row under it. Leave a location unassigned to keep the built-in defaults.',
		'civicone'
	);
	echo '</p></div>';
}

/**
 * Wire everything up.
 */
function d4c_section_tabs_boot(): void {
	add_action( 'after_setup_theme', 'd4c_section_tabs_register_locations', 20 );
	add_action( 'admin_notices', 'd4c_section_tabs_admin_notice' );

	foreach ( d4c_section_tabs_filter_names() as $filter ) {
		add_filter( $filter, 'd4c_section_tabs_apply', 20 );
	}
}

d4c_section_tabs_boot();
