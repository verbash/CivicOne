<?php
/**
 * CivicOne component: editable link targets for menu items.
 *
 * In Appearance → Menus, WordPress only lets staff rename a "Page" menu item;
 * there is no field to change which page it points to. Section tab menus are
 * seeded with Page items (see inc/d4c-section-tabs-seed.php), so a tab such as
 * "Training" could be relabelled but never repointed.
 *
 * This adds a "Links to" control to every menu item on the Menus screen:
 *   - a page dropdown (pick another page on the site), and
 *   - an optional web address field (link anywhere, on or off the site).
 *
 * On save the menu item itself is rewritten to the new target, so every
 * consumer (section tabs, wp_nav_menu, the Customizer) sees an ordinary
 * Page or Custom Link item. The item's label is left as staff typed it.
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST field names used by the control.
 */
const CIVICONE_MENU_LINK_PAGE_FIELD = 'civicone-link-page';
const CIVICONE_MENU_LINK_URL_FIELD  = 'civicone-link-url';

/**
 * Human-readable description of where a menu item currently links.
 *
 * @param WP_Post $item Menu item (already run through wp_setup_nav_menu_item()).
 */
function civicone_menu_item_link_describe( WP_Post $item ): string {
	if ( 'post_type' === ( $item->type ?? '' ) && (int) ( $item->object_id ?? 0 ) > 0 ) {
		$title = get_the_title( (int) $item->object_id );
		$url   = (string) get_permalink( (int) $item->object_id );

		if ( '' !== $title ) {
			/* translators: 1: page title, 2: page address. */
			return sprintf( __( 'Page “%1$s” (%2$s)', 'civicone' ), $title, $url );
		}

		return $url;
	}

	return (string) ( $item->url ?? '' );
}

/**
 * Render the "Links to" control inside each menu item's settings panel.
 *
 * @param int     $item_id   Menu item ID.
 * @param WP_Post $menu_item Menu item data object.
 */
function civicone_menu_item_link_fields( $item_id, $menu_item ): void {
	$item_id = (int) $item_id;
	if ( $item_id <= 0 || ! $menu_item instanceof WP_Post ) {
		return;
	}

	$is_page  = 'post_type' === ( $menu_item->type ?? '' ) && 'page' === ( $menu_item->object ?? '' );
	$selected = $is_page ? (int) $menu_item->object_id : 0;

	$page_id = 'civicone-link-page-' . $item_id;
	$url_id  = 'civicone-link-url-' . $item_id;

	$dropdown = wp_dropdown_pages(
		array(
			'echo'              => 0,
			'name'              => CIVICONE_MENU_LINK_PAGE_FIELD . '[' . $item_id . ']',
			'id'                => $page_id,
			'class'             => 'widefat',
			'selected'          => $selected,
			'show_option_none'  => $is_page
				? __( '— Choose a page —', 'civicone' )
				: __( '— Keep the current link —', 'civicone' ),
			'option_none_value' => '0',
			'post_status'       => array( 'publish', 'private' ),
		)
	);
	?>
	<fieldset class="field-civicone-link description description-wide" style="border-top:1px solid #dcdcde;margin-top:8px;padding-top:8px;">
		<legend style="font-weight:600;"><?php esc_html_e( 'Links to', 'civicone' ); ?></legend>
		<p class="description" style="margin:2px 0 8px;">
			<?php
			printf(
				/* translators: %s: current link target. */
				esc_html__( 'Currently: %s', 'civicone' ),
				'<code>' . esc_html( civicone_menu_item_link_describe( $menu_item ) ) . '</code>'
			);
			?>
		</p>
		<p style="margin:0 0 8px;">
			<label for="<?php echo esc_attr( $page_id ); ?>"><?php esc_html_e( 'A page on this site', 'civicone' ); ?></label><br />
			<?php
			// wp_dropdown_pages() returns escaped markup; '' when the site has no pages.
			echo $dropdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</p>
		<p style="margin:0;">
			<label for="<?php echo esc_attr( $url_id ); ?>"><?php esc_html_e( 'Or a web address (overrides the page choice)', 'civicone' ); ?></label><br />
			<input type="url" class="widefat code" id="<?php echo esc_attr( $url_id ); ?>" name="<?php echo esc_attr( CIVICONE_MENU_LINK_URL_FIELD . '[' . $item_id . ']' ); ?>" value="" placeholder="https://" />
		</p>
	</fieldset>
	<?php
}

/**
 * Decide what a submitted "Links to" control asks for.
 *
 * Pure function (no WordPress state) so it is easy to test.
 *
 * @param string $type          Current item type ('post_type', 'custom', 'taxonomy', …).
 * @param string $object        Current item object ('page', 'custom', …).
 * @param int    $object_id     Current object ID.
 * @param int    $chosen_page   Page ID chosen in the dropdown (0 = none).
 * @param string $chosen_url    Sanitized web address typed (may be '').
 * @return array{action: string, page_id?: int, url?: string} action is 'none', 'page' or 'url'.
 */
function civicone_menu_item_link_plan( string $type, string $object, int $object_id, int $chosen_page, string $chosen_url ): array {
	if ( '' !== $chosen_url ) {
		return array(
			'action' => 'url',
			'url'    => $chosen_url,
		);
	}

	if ( $chosen_page <= 0 ) {
		return array( 'action' => 'none' );
	}

	$already_that_page = 'post_type' === $type && 'page' === $object && $object_id === $chosen_page;
	if ( $already_that_page ) {
		return array( 'action' => 'none' );
	}

	return array(
		'action'  => 'page',
		'page_id' => $chosen_page,
	);
}

/**
 * Whether the current request is a genuine Menus-screen save by an editor.
 */
function civicone_menu_item_link_request_ok(): bool {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return false;
	}

	// Core verifies this nonce before saving the Menus screen; re-check so the
	// rewrite only ever runs from that form (never REST, Customizer or seeding).
	$nonce = isset( $_POST['update-nav-menu-nonce'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['update-nav-menu-nonce'] ) ) : '';

	return '' !== $nonce && false !== wp_verify_nonce( $nonce, 'update-nav_menu' );
}

/**
 * Apply the "Links to" control after core has saved a menu item.
 *
 * @param int $menu_id         Menu ID.
 * @param int $menu_item_db_id Menu item post ID.
 */
function civicone_menu_item_link_save( $menu_id, $menu_item_db_id ): void {
	unset( $menu_id );
	$item_id = (int) $menu_item_db_id;

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- checked in civicone_menu_item_link_request_ok().
	if ( $item_id <= 0 || ( ! isset( $_POST[ CIVICONE_MENU_LINK_PAGE_FIELD ][ $item_id ] ) && ! isset( $_POST[ CIVICONE_MENU_LINK_URL_FIELD ][ $item_id ] ) ) ) {
		return;
	}

	if ( ! civicone_menu_item_link_request_ok() ) {
		return;
	}

	$chosen_page = isset( $_POST[ CIVICONE_MENU_LINK_PAGE_FIELD ][ $item_id ] ) ? absint( wp_unslash( $_POST[ CIVICONE_MENU_LINK_PAGE_FIELD ][ $item_id ] ) ) : 0;
	$raw_url     = isset( $_POST[ CIVICONE_MENU_LINK_URL_FIELD ][ $item_id ] ) ? trim( (string) wp_unslash( $_POST[ CIVICONE_MENU_LINK_URL_FIELD ][ $item_id ] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$chosen_url = '' === $raw_url ? '' : esc_url_raw( $raw_url );

	if ( $chosen_page > 0 ) {
		$page = get_post( $chosen_page );
		if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
			$chosen_page = 0;
		}
	}

	$plan = civicone_menu_item_link_plan(
		(string) get_post_meta( $item_id, '_menu_item_type', true ),
		(string) get_post_meta( $item_id, '_menu_item_object', true ),
		(int) get_post_meta( $item_id, '_menu_item_object_id', true ),
		$chosen_page,
		$chosen_url
	);

	if ( 'page' === $plan['action'] ) {
		update_post_meta( $item_id, '_menu_item_type', 'post_type' );
		update_post_meta( $item_id, '_menu_item_object', 'page' );
		update_post_meta( $item_id, '_menu_item_object_id', (int) $plan['page_id'] );
		update_post_meta( $item_id, '_menu_item_url', '' );
		return;
	}

	if ( 'url' === $plan['action'] ) {
		update_post_meta( $item_id, '_menu_item_type', 'custom' );
		update_post_meta( $item_id, '_menu_item_object', 'custom' );
		update_post_meta( $item_id, '_menu_item_object_id', $item_id );
		update_post_meta( $item_id, '_menu_item_url', $plan['url'] );
	}
}

add_action( 'wp_nav_menu_item_custom_fields', 'civicone_menu_item_link_fields', 10, 2 );
add_action( 'wp_update_nav_menu_item', 'civicone_menu_item_link_save', 20, 2 );
