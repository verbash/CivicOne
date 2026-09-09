<?php
/**
 * Title: CivicOne header bar
 * Slug: civicone/header-bar
 * Categories: civicone-layout
 * Block Types: core/template-part/header
 * Description: Logo left, primary nav (wp_navigation 791), CivicOne mobile drawer. Source of truth: inc/civicone/header-bar.php — do not edit markup here.
 * Inserter: no
 *
 * @package CivicOne
 */

echo civicone_header_bar_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block HTML from theme.
