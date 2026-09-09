<?php
/**
 * Section page-tabs + anchor-tabs (About, Programs and Services, Resources).
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Markup for section subnav (page tabs + optional hub anchor tabs).
 *
 * @param array<string, mixed> $section Section config.
 */
function civicone_render_section_subnav( array $section ): string {
	$current  = civicone_get_current_section_tab_slug( $section );
	$on_hub   = civicone_is_section_hub( $section );
	$tabs     = $section['tabs'];
	$anchors  = array();
	if ( $on_hub && function_exists( 'civicone_get_hub_on_page_anchors' ) ) {
		$anchors = civicone_get_hub_on_page_anchors();
	}
	$modifier = ' civicone-section-subnav--' . sanitize_html_class( (string) $section['slug'] );

	ob_start();
	?>
	<nav class="civicone-section-subnav<?php echo $on_hub ? ' civicone-section-subnav--on-hub' : ''; ?><?php echo esc_attr( $modifier ); ?>" aria-label="<?php echo esc_attr( (string) $section['label'] ); ?>">
		<ul class="civicone-section-subnav__tabs" role="list">
			<?php foreach ( $tabs as $tab ) : ?>
				<?php
				$is_current = ( $current === $tab['slug'] );
				$li_class   = $is_current ? ' is-active' : '';
				?>
				<li class="civicone-section-subnav__item<?php echo esc_attr( $li_class ); ?>" role="listitem">
					<a href="<?php echo esc_url( $tab['url'] ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $tab['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php if ( ! empty( $anchors ) ) : ?>
		<ul class="civicone-section-subnav__anchors" role="list" aria-label="<?php esc_attr_e( 'On this page', 'civicone' ); ?>">
			<?php foreach ( $anchors as $anchor ) : ?>
				<li class="civicone-section-subnav__item civicone-section-subnav__item--anchor" role="listitem">
					<a href="<?php echo esc_url( $anchor['url'] ); ?>"><?php echo esc_html( $anchor['label'] ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</nav>
	<?php
	return (string) ob_get_clean();
}

/**
 * Mark the section subnav as rendered for this request.
 *
 * Shared de-dup flag so the civicone/section-subnav block and the
 * post-content auto-inject fallback never both render the row.
 */
function civicone_section_subnav_mark_rendered(): void {
	$GLOBALS['civicone_section_subnav_rendered'] = true;
}

/**
 * Whether the section subnav has already been rendered this request.
 */
function civicone_section_subnav_already_rendered(): bool {
	return ! empty( $GLOBALS['civicone_section_subnav_rendered'] );
}

/**
 * Prepend section subnav to post content on section pages.
 *
 * Fallback for templates that do NOT contain the civicone/section-subnav block
 * (e.g. a DB-customized page template). The shared flag keeps it from
 * double-rendering when the block is present.
 *
 * @param string $content Block content.
 * @param array  $block   Block data.
 */
function civicone_prepend_section_subnav_to_post_content( string $content, array $block ): string {
	if ( civicone_section_subnav_already_rendered() ) {
		return $content;
	}

	if ( ( $block['blockName'] ?? '' ) !== 'core/post-content' ) {
		return $content;
	}

	$section = civicone_get_current_section_config();
	if ( null === $section ) {
		return $content;
	}

	civicone_section_subnav_mark_rendered();

	return civicone_render_section_subnav( $section ) . $content;
}
add_filter( 'render_block', 'civicone_prepend_section_subnav_to_post_content', 9, 2 );

