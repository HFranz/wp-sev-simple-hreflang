=== SEV Simple hreflang ===
Contributors: hfranz
Tags: hreflang, seo, multilingual, alternate, navigation
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds hreflang alternates for pages, posts, and categories and outputs them correctly in the page head — including a self-referencing link.

== Description ==

SEV Simple hreflang adds a field to pages, posts, and categories where you can enter any number of alternate-language versions (hreflang + URL). The plugin automatically outputs these as `<link rel="alternate" hreflang="…">` tags in the `<head>` of the corresponding view — including a self-referencing link to the page itself, as recommended by Google.

**Why no block for the hreflang tags themselves?**

hreflang annotations are only evaluated by search engines in the `<head>` (or via HTTP header or sitemap), not in the visible page content. A block inserted into the content would end up in the `<body>` and simply be ignored. That's why this plugin deliberately uses post/term meta for the hreflang data itself, instead of a block.

**Language Link block for the Navigation block**

For block themes, the plugin also adds a "hreflang Language Link" block that can be inserted inside the Navigation block. Set the target language (e.g. `en-US`) and the block links to the current page's hreflang alternate for that language. If the current page has no matching alternate, the menu item is left out of the navigation entirely — not just hidden.

**Features**

* Sidebar panel in the block editor for pages and posts.
* Dedicated form field when adding/editing categories (there's no block editor there).
* Also supports the static "Posts page" (Settings > Reading).
* Automatic self-referencing hreflang link, based on the site's language.
* Language Link block for the Navigation block, shown only where a translation exists.
* No settings, no external calls — just activate it.

**Multisite**

The plugin is fully multisite-compatible. It can be network-activated (active on every site) or activated per site individually, whichever fits your setup — each site keeps its own hreflang data, since post/term meta is already scoped per site.

== Installation ==

1. Upload the `sev-simple-hreflang` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. When editing a page/post: open the sidebar, expand the "hreflang (alternate language versions)" panel. For categories: the field is directly in the form.

== Frequently Asked Questions ==

= Why don't I see a block for hreflang tags in the editor? =

There deliberately isn't one. hreflang tags must live in `<head>`, not in the content — a content block would be ignored by search engines.

= Can I add a language switcher to my navigation menu? =

Yes, if your theme is a block theme. Add the "hreflang Language Link" block inside the Navigation block, set the target hreflang code (e.g. `en-US`), and it links to the current page's matching alternate. Pages without a matching alternate simply don't show that menu item.

= Does this work with WPML/Polylang? =

The plugin works independently of multilingual plugins and manages hreflang values manually per page/post/category. If WPML or Polylang is active and already outputs hreflang, you may end up with duplicate tags — in that case, please use only one of the two solutions.

= Is x-default supported? =

Yes — the hreflang field is a free-text field, so `x-default` can be entered directly.

= Does this work on Multisite? =

Yes. The plugin can be network-activated or activated per site; each site manages its own hreflang data independently.

== Screenshots ==

1. Sidebar panel in the block editor

== Changelog ==

= 1.1.0 =
* Add "hreflang Language Link" block for the Navigation block (block themes), linking to the current page's alternate for a chosen language and omitted when no matching alternate exists.

= 1.0.0 =
* Initial release.
