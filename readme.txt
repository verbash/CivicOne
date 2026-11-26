=== CivicOne ===
Contributors: turingtools
Tags: full-site-editing, block-patterns, navigation, accessibility
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 8.1
Stable tag: 0.1.1
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

CivicOne — a civic/nonprofit site framework for WordPress. An FSE child theme of Twenty Twenty-Two with menu-editable section hubs, header/drawer nav, and content patterns.

== Description ==
CivicOne is a full-site-editing child theme of Twenty Twenty-Two for civic and nonprofit sites. It provides section hubs with menu-editable tab rows and headings, a header bar with mobile drawer navigation, a server-rendered section sub-nav block, and a library of content patterns and block styles. Section tab rows and headings are editable through Appearance → Menus (one menu per section: the menu title is the heading, its items are the tabs), with hardcoded defaults as a fallback. Consolidated from the former CivicOne plugin plus child theme into one installable, git-updatable theme — no companion plugin required.

== Changelog ==
= 0.1.1 =
* Menu items in Appearance → Menus now have a “Links to” setting, so staff can change which page (or web address) a section tab points to, not just its label.

= 0.1.0 =
* Consolidated the CivicOne plugin into a single child theme — no separate companion plugin required.
* Added the server-rendered `civicone/section-subnav` block, placeable in the Site Editor.
* Section tab rows and the section heading are now editable via Appearance → Menus (one menu per section: title = heading, items = tabs), with hardcoded defaults as a fallback.
* Added a `post-title` block to `templates/page.html` so pages have a heading out of the box.
* Unified the text domain to `civicone`.
