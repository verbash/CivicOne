# CivicOne

Self-contained civic child theme of **Twenty Twenty-Two**. CivicOne folds the
former `civicone` plugin entirely into the theme — there is **no companion
plugin**. Header, drawer nav, section navigation, block patterns, block styles,
and `assets/civicone.css` all load from the theme on theme init.

## What the theme provides

| Piece | Path |
| --- | --- |
| Brand tokens / templates | `theme.json`, `templates/`, `parts/` |
| **Standard Page** template (`templates/page.html`) | Same header/footer shell as the front page — use for `/community/` and other interior pages |
| CivicOne UI kit (header, drawer, section nav, CTA, patterns, block styles, base CSS) | `inc/civicone/*.php`, `assets/`, `patterns/` |
| D4C section tabs + nav ID | `inc/d4c-section-config.php` |
| Blog helpers | `inc/blog.php` |
| Editable menu link targets (“Links to” on each item in Appearance → Menus) | `inc/civicone/menu-item-link.php` |
| Section-tab menu seeder | `inc/d4c-section-tabs.php`, `inc/d4c-section-tabs-seed.php` |
| Header pattern | `patterns/header-bar.php` → `civicone_header_bar_markup()` in `inc/civicone/header-bar.php` |

All CivicOne components are required at the top of `functions.php` (before any
hook fires) so every `civicone_*` function exists before rendering.

## Header

Canonical markup: **`inc/civicone/header-bar.php`**, exposed as pattern
**`civicone/header-bar`** (`patterns/header-bar.php`). `parts/header.html`
references that pattern only.

Live DB template part (post **591**) should match the pattern block. Mobile
drawer and donate CTA attach via theme PHP — do not add them in the Site Editor.

## Component map

See **`civicone-component-library.md`** (repo root). All CivicOne component
paths are under `inc/civicone/`, with assets under `assets/` and patterns under
`patterns/`.
