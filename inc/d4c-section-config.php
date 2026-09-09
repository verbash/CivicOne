<?php
/**
 * D4C — section hub/tab data for CivicOne (filter into civicone_section_configs).
 *
 * @package CivicOne
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'civicone_primary_nav_id', static fn(): int => 791 );
add_filter( 'civicone_section_configs', 'd4c_register_section_configs' );
add_filter( 'civicone_header_cta_text', static fn(): string => __( 'Help support our efforts!', 'civicone' ) );
add_filter( 'civicone_header_cta_button_label', static fn(): string => __( 'Donate to D4C', 'civicone' ) );

/**
 * District Four Coalition section registry.
 *
 * @param list<array<string, mixed>> $configs Unused; replaced per site.
 * @return list<array<string, mixed>>
 */
function d4c_register_section_configs( array $configs ): array {
	unset( $configs );

	return array(
		array(
			'slug'     => 'about',
			'label'    => __( 'About', 'civicone' ),
			'hub_slug' => 'about',
			'tabs'     => array(
				array(
					'label' => __( 'About', 'civicone' ),
					'url'   => home_url( '/about/' ),
					'slug'  => 'about',
				),
				array(
					'label' => __( 'Board', 'civicone' ),
					'url'   => home_url( '/about/board/' ),
					'slug'  => 'board',
				),
				array(
					'label' => __( 'Staff', 'civicone' ),
					'url'   => home_url( '/about/staff/' ),
					'slug'  => 'staff',
				),
				array(
					'label' => __( 'Committees', 'civicone' ),
					'url'   => home_url( '/about/committees/' ),
					'slug'  => 'committees',
				),
				array(
					'label' => __( 'Coalition Members', 'civicone' ),
					'url'   => home_url( '/about/coalition-members/' ),
					'slug'  => 'coalition-members',
				),
				array(
					'label' => __( 'Transparency', 'civicone' ),
					'url'   => home_url( '/about/important-documents/' ),
					'slug'  => 'important-documents',
				),
			),
		),
		array(
			'slug'     => 'programs-and-services',
			'label'    => __( 'Programs and Services', 'civicone' ),
			'hub_slug' => 'programs-and-services',
			'tabs'     => array(
				array(
					'label' => __( 'Overview', 'civicone' ),
					'url'   => home_url( '/programs-and-services/' ),
					'slug'  => 'programs-and-services',
				),
				array(
					'label' => __( 'Grants', 'civicone' ),
					'url'   => home_url( '/programs-and-services/grants/' ),
					'slug'  => 'grants',
				),
				array(
					'label' => __( 'Programs', 'civicone' ),
					'url'   => home_url( '/programs-and-services/programs/' ),
					'slug'  => 'programs',
				),
				array(
					'label' => __( 'Services', 'civicone' ),
					'url'   => home_url( '/programs-and-services/services/' ),
					'slug'  => 'services',
				),
				array(
					'label' => __( 'Building Diverse Communities', 'civicone' ),
					'url'   => home_url( '/programs-and-services/programs/building-diverse-communities/' ),
					'slug'  => 'building-diverse-communities',
				),
				array(
					'label' => __( 'Emergency Preparedness', 'civicone' ),
					'url'   => home_url( '/programs-and-services/programs/emergency-preparedness/' ),
					'slug'  => 'emergency-preparedness',
				),
				array(
					'label' => __( 'Land Use and Transportation', 'civicone' ),
					'url'   => home_url( '/programs-and-services/programs/land-use-and-transportation/' ),
					'slug'  => 'land-use-and-transportation',
				),
				array(
					'label' => __( 'Watershed Health', 'civicone' ),
					'url'   => home_url( '/programs-and-services/programs/watershed-health/' ),
					'slug'  => 'watershed-health',
				),
			),
		),
		array(
			'slug'     => 'resources',
			'label'    => __( 'Resources', 'civicone' ),
			'hub_slug' => 'resources',
			'tabs'     => array(
				array(
					'label' => __( 'All resources', 'civicone' ),
					'url'   => home_url( '/resources/' ),
					'slug'  => 'resources',
				),
				array(
					'label' => __( 'Advocacy Resources', 'civicone' ),
					'url'   => home_url( '/resources/advocacy-resources/' ),
					'slug'  => 'advocacy-resources',
				),
				array(
					'label' => __( 'Behavioral Health', 'civicone' ),
					'url'   => home_url( '/resources/behavioral-health-crisis/' ),
					'slug'  => 'behavioral-health-crisis',
				),
				array(
					'label' => __( 'Board', 'civicone' ),
					'url'   => home_url( '/resources/board-resources/' ),
					'slug'  => 'board-resources',
				),
				array(
					'label' => __( 'Civic Involvement', 'civicone' ),
					'url'   => home_url( '/resources/civic-involvement-links/' ),
					'slug'  => 'civic-involvement-links',
				),
				array(
					'label' => __( 'Emergency Prep', 'civicone' ),
					'url'   => home_url( '/resources/emergency-pre/' ),
					'slug'  => 'emergency-pre',
				),
				array(
					'label' => __( 'Livability', 'civicone' ),
					'url'   => home_url( '/resources/livability-resources/' ),
					'slug'  => 'livability-resources',
				),
				array(
					'label' => __( 'Training', 'civicone' ),
					'url'   => home_url( '/resources/board-resources/training/' ),
					'slug'  => 'training',
				),
				array(
					'label' => __( 'Watershed Health', 'civicone' ),
					'url'   => home_url( '/resources/watershed-health/' ),
					'slug'  => 'watershed-health',
				),
				array(
					'label' => __( 'Traffic Safety', 'civicone' ),
					'url'   => home_url( '/traffic-safety-event-planning-template/' ),
					'slug'  => 'traffic-safety-event-planning-template',
				),
			),
		),
	);
}
