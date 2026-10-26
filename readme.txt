=== Credipost ===
Contributors: khuzemahsaeed
Donate link: https://portfolio-khuzemah-saeed.vercel.app/
Tags: content curation, scraper, syndication, post creator, video embed
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Curate, scrape, prune, and syndicate web articles and video embeds safely to WordPress posts and multisite networks.

== Description ==

**Credipost** is a secure, high-performance content curation and syndication tool for WordPress. It empowers site editors to fetch external articles or video links, interactively prune unwanted content blocks before publishing, select target categories, and publish posts to the local site or across a WordPress Multisite network.

### Key Features

* **Safe Web Scraping**: Uses `wp_safe_remote_get()` to block SSRF and safely extract web content.
* **Intelligent Noise Filtering**: Cleans navigation, scripts, ads, and sidebars via `DOMDocument` and `DOMXPath`.
* **Automatic URL Resolution**: Relative links and images are resolved to fully qualified absolute URLs.
* **Native Video Embeds**: Generates responsive embeds for YouTube, Vimeo, Dailymotion, and more via `wp_oembed_get()`.
* **Visual Block Pruning**: Hover over any paragraph, heading, or image to remove it with an instant Undo option.
* **Editorial Customization**: Edit the post title, set post status (Draft, Pending Review, Publish), and assign categories.
* **WordPress Multisite Ready**: Switch and publish to any network subsite with proper capability checks.
* **Enterprise Security**: Full nonce verification, strict capability checks, and XSS sanitization via `wp_kses_post()`.
* **Zero Script Conflicts**: Fully decoupled from bundled jQuery; relies cleanly on WordPress core jQuery.

== Installation ==

1. Upload the `crediblock` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Access the plugin via the **Credipost** menu in the WordPress Admin sidebar.

== Frequently Asked Questions ==

= Who can use Credipost? =
Only users with the `edit_posts` capability (Authors, Editors, Administrators) can curate and publish content.

= Can I use it on the frontend? =
Yes, insert the shortcode `[CREDIPOST]` into any page. Only logged-in users with author permissions can use the curation controls.

= Does it support Multisite? =
Yes! If WordPress Multisite is active, a dropdown allows authorized users to publish posts across network subsites.

== Changelog ==

= 2.0.0 =
* Complete refactor for security and architecture standards.
* Replaced legacy regex scraper with modern DOMDocument-based `Credipost_Scraper`.
* Added CSRF protection (nonces) and capability verification across all endpoints.
* Fixed SSRF risks by enforcing `wp_safe_remote_get()`.
* Added XSS sanitization (`wp_kses_post`).
* Removed core jQuery hijacking and scoped all CSS to prevent admin and theme conflicts.
* Added customizable post titles, post status selection (Draft/Publish), and block-level Undo pruning.
* Added native `wp_oembed_get()` video embed integration.
* Full i18n support with `credipost.pot`.

= 1.1.0 =
* Legacy release with basic scraping and shortcode support.
