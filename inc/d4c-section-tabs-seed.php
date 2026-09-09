<?php
/**
 * D4C — seed section tab menus from the hardcoded defaults.
 *
 * Creates one nav menu per section, pre-filled with that section's current
 * default tabs, and assigns it to the matching "Section tabs — …" location.
 * The result is visually identical to the hardcoded row, so seeding is a no-op
 * on the front end — it just moves the row under staff control.
 *
 * Two ways to run it:
 *   1. Appearance → Menus → "Create section tab menus from defaults" button.
 *      (Works on shared hosting with no SSH. Requires edit_theme_options.)
 *   2. WP-CLI: wp d4c seed-section-tabs [--dry-run] [--force]
 *
 * Idempotent: a section whose location already has a menu assigned is skipped
 * unless --force / the force flag is used.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu name for a section.
 *
 * The menu name doubles as the section heading shown on child pages (its title
 * is the heading; its items are the tab row), so it is the plain section label
 * — e.g. "About", "Programs and Services", "Resources" — not decorated. The
 * location display name still carries "Section tabs — …" so staff can tell
 * which slot the menu fills.
 *
 * @param array<string, mixed> $section Section config.
 */
function d4c_section_tabs_seed_menu_name( array $section ): string {
	return (string) ( $section['label'] ?? $section['slug'] ?? '' );
}

/**
 * Resolve a default tab URL to a local page, if one exists.
 *
 * @param string $url Absolute tab URL.
 * @return WP_Post|null
 */
function d4c_section_tabs_seed_resolve_page( string $url ): ?WP_Post {
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );
	if ( '' !== $host && '' !== $home && strtolower( $host ) !== strtolower( $home ) ) {
		return null;
	}

	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return null;
	}

	$page = get_page_by_path( $path );

	return $page instanceof WP_Post ? $page : null;
}

/**
 * Seed one section's menu.
 *
 * @param array<string, mixed> $section Section config (with default tabs).
 * @param bool                 $force   Re-seed even if the location is assigned.
 * @param bool                 $dry_run Report only; change nothing.
 * @return array{section: string, status: string, items: int, menu: string}
 */
function d4c_section_tabs_seed_section( array $section, bool $force = false, bool $dry_run = false ): array {
	$slug   = (string) ( $section['slug'] ?? '' );
	$name   = d4c_section_tabs_seed_menu_name( $section );
	$tabs   = is_array( $section['tabs'] ?? null ) ? $section['tabs'] : array();
	$result = array(
		'section' => $slug,
		'status'  => 'skipped',
		'items'   => 0,
		'menu'    => $name,
	);

	if ( '' === $slug || empty( $tabs ) ) {
		$result['status'] = 'no-defaults';
		return $result;
	}

	$location  = d4c_section_tabs_menu_location( $slug );
	$locations = get_nav_menu_locations();

	if ( ! $force && (int) ( $locations[ $location ] ?? 0 ) > 0 ) {
		$result['status'] = 'already-assigned';
		return $result;
	}

	if ( $dry_run ) {
		$result['status'] = 'would-create';
		$result['items']  = count( $tabs );
		return $result;
	}

	$existing = wp_get_nav_menu_object( $name );
	if ( $existing instanceof WP_Term ) {
		$menu_id = (int) $existing->term_id;
		// Clear it so re-seeding does not duplicate items.
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			if ( $item instanceof WP_Post ) {
				wp_delete_post( (int) $item->ID, true );
			}
		}
	} else {
		$menu_id = wp_create_nav_menu( $name );
	}

	if ( is_wp_error( $menu_id ) || (int) $menu_id <= 0 ) {
		$result['status'] = 'error';
		return $result;
	}

	$menu_id  = (int) $menu_id;
	$position = 0;

	foreach ( $tabs as $tab ) {
		$label = (string) ( $tab['label'] ?? '' );
		$url   = (string) ( $tab['url'] ?? '' );
		if ( '' === $label || '' === $url ) {
			continue;
		}

		++$position;
		$page = d4c_section_tabs_seed_resolve_page( $url );

		$args = array(
			'menu-item-title'    => $label,
			'menu-item-status'   => 'publish',
			'menu-item-position' => $position,
		);

		if ( $page instanceof WP_Post ) {
			$args['menu-item-type']      = 'post_type';
			$args['menu-item-object']    = 'page';
			$args['menu-item-object-id'] = (int) $page->ID;
		} else {
			$args['menu-item-type'] = 'custom';
			$args['menu-item-url']  = $url;
		}

		$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
		if ( ! is_wp_error( $item_id ) ) {
			++$result['items'];
		}
	}

	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	$result['status'] = 'seeded';

	return $result;
}

/**
 * Seed every section.
 *
 * @param bool $force   Re-seed assigned locations.
 * @param bool $dry_run Report only.
 * @return list<array{section: string, status: string, items: int, menu: string}>
 */
function d4c_section_tabs_seed_all( bool $force = false, bool $dry_run = false ): array {
	$out = array();

	foreach ( d4c_section_tabs_defaults() as $section ) {
		$out[] = d4c_section_tabs_seed_section( $section, $force, $dry_run );
	}

	return $out;
}

/**
 * Admin-post handler for the seed button.
 */
function d4c_section_tabs_seed_handle_request(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to manage menus.', 'civicone' ) );
	}

	check_admin_referer( 'd4c_seed_section_tabs' );

	$force   = isset( $_POST['force'] ) && '1' === (string) $_POST['force'];
	$results = d4c_section_tabs_seed_all( $force, false );

	$seeded  = 0;
	$skipped = 0;
	foreach ( $results as $r ) {
		if ( 'seeded' === $r['status'] ) {
			++$seeded;
		} else {
			++$skipped;
		}
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'd4c_seeded'  => $seeded,
				'd4c_skipped' => $skipped,
			),
			admin_url( 'nav-menus.php' )
		)
	);
	exit;
}
add_action( 'admin_post_d4c_seed_section_tabs', 'd4c_section_tabs_seed_handle_request' );

/**
 * Seed button + result notice on the Menus screen.
 */
function d4c_section_tabs_seed_admin_ui(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( null === $screen || 'nav-menus' !== $screen->id || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( isset( $_GET['d4c_seeded'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: created count, 2: skipped count. */
					__( 'Section tab menus: %1$d created, %2$d skipped (already assigned).', 'civicone' ),
					(int) $_GET['d4c_seeded'], // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					(int) ( $_GET['d4c_skipped'] ?? 0 ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				)
			)
		);
	}

	$unassigned = 0;
	$locations  = get_nav_menu_locations();
	foreach ( d4c_section_tabs_defaults() as $section ) {
		$loc = d4c_section_tabs_menu_location( (string) ( $section['slug'] ?? '' ) );
		if ( (int) ( $locations[ $loc ] ?? 0 ) <= 0 ) {
			++$unassigned;
		}
	}

	if ( $unassigned <= 0 ) {
		return;
	}

	echo '<div class="notice notice-info"><p>';
	echo esc_html__(
		'Section headings and tab rows can be built from the current built-in defaults, then edited here like any menu. Each seeded menu’s title is the section heading shown on its child pages, and its items are the tab row under it. This does not change how the site looks — the seeded menus match what is on the site today.',
		'civicone'
	);
	echo '</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'd4c_seed_section_tabs' );
	echo '<input type="hidden" name="action" value="d4c_seed_section_tabs" />';
	echo '<p><button type="submit" class="button button-primary">';
	echo esc_html__( 'Create section tab menus from defaults', 'civicone' );
	echo '</button></p></form></div>';
}
add_action( 'admin_notices', 'd4c_section_tabs_seed_admin_ui' );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'd4c seed-section-tabs',
		/**
		 * Seed section tab menus from the theme defaults.
		 *
		 * @param list<string>          $args       Positional args.
		 * @param array<string, string> $assoc_args Flags.
		 */
		static function ( array $args, array $assoc_args ): void {
			unset( $args );
			$dry   = isset( $assoc_args['dry-run'] );
			$force = isset( $assoc_args['force'] );

			$rows = d4c_section_tabs_seed_all( $force, $dry );

			WP_CLI\Utils\format_items( 'table', $rows, array( 'section', 'status', 'items', 'menu' ) );

			if ( $dry ) {
				WP_CLI::log( 'Dry run — nothing was changed. Re-run without --dry-run to apply.' );
			}
		}
	);
}
