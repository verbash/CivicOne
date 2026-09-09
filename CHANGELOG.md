# Changelog

All notable changes to CivicOne. Format: Keep a Changelog; versioning: SemVer.

## 0.1.0 — 2026-09-09
### Changed
- Consolidated the CivicOne plugin into a single child theme — there is no longer a separate companion plugin to install or maintain. All UI, patterns, block styles, filters, and PHP logic now ship inside the theme.
- Unified the text domain to `civicone` across all strings and files.

### Added
- Server-rendered `civicone/section-subnav` block, placeable directly in the Site Editor, that renders the current section's tab row.
- Menu-driven section navigation: section tab rows **and** the section heading are now editable via Appearance → Menus — one menu per section, where the menu title is the section heading and its items are the tabs. Hardcoded defaults remain as a fallback when no menu is assigned.
- `post-title` block in `templates/page.html` so pages render a heading out of the box.

## [0.1.0] - 2026-09-03
### Changed (BREAKING)
- Renamed the entire kit from the internal `Civic-1` / `cv1` / `civic_1` prototype naming to **CivicOne** / `civicone`: colour palette slugs, CSS classes, `--cv1-*` custom properties, PHP function prefixes (`cv1_`, `civic_1_` → `civicone_`), constants (`CIVIC_1_*` → `CIVICONE_*`), the `civic_1_section_configs` filter, asset filenames, plugin name, folder, and repo.
- **Why:** `cv1-` colour slugs had a letter→digit boundary; a WordPress core update changed slug→CSS-class generation and invalidated saved blocks using those colours. `civicone` has no digit, so the failure cannot recur.
- Requires the paired child-theme token rename and a one-time content migration (`cv1- → civicone-`).

### Added
- Git Updater headers (`Update URI`, `GitHub Plugin URI`, `Primary Branch`).
- `CIVICONE_VERSION` constant; `readme.txt` with Stable tag; translation loader.
