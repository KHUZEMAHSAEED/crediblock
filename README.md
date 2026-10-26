# Credipost (Crediblock)

[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3-8892BF.svg)](https://www.php.net)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)
[![Version](https://img.shields.io/badge/Version-2.0.0-orange.svg)](https://crediblock.com/)

**Credipost** is a secure, high-performance content curation and syndication plugin for WordPress. It allows site editors to fetch external web articles or video embeds, interactively prune unwanted blocks (with undo support), configure metadata and taxonomy terms, and publish directly to the local site or across a WordPress Multisite network.

---

## ✨ Features

- **🛡️ Secure External Scraping**: Powered by WordPress's native `wp_safe_remote_get()` API. Automatically blocks Server-Side Request Forgery (SSRF) by restricting internal loopbacks (`127.0.0.1`, `10.x.x.x`, `169.254.x.x`) and enforcing secure protocols.
- **🧹 Clean HTML & Noise Filtering**: Replaces brittle regex scrapers with PHP `DOMDocument` and `DOMXPath`. Automatically strips unwanted noise (`<script>`, `<style>`, `<nav>`, `<header>`, `<footer>`, `.sidebar`, `.comments`) while preserving clean semantic headings, paragraphs, blockquotes, lists, and images.
- **🔗 Automatic URL Resolution**: Converts relative image paths (`/assets/hero.jpg`) and links into fully qualified absolute URLs automatically.
- **🎥 Native Video oEmbed**: Paste any supported video link (YouTube, Vimeo, Dailymotion, etc.) and generate responsive embeds powered by WordPress core `wp_oembed_get()`.
- **✂️ Visual Block Pruning & Undo**: Hover over any paragraph, heading, or image in the preview to remove it with a single click. A sticky undo bar allows restoring deleted blocks instantly.
- **📝 Full Editorial Control**:
  - Customize Post Title before saving.
  - Select Post Status (`Draft`, `Pending Review`, or `Publish Immediately`).
  - Assign categories dynamically.
  - Choose destination subsite in WordPress Multisite installations.
- **🌐 WordPress Multisite Ready**: Seamlessly switch between network sites with subsite permission checks (`current_user_can_for_blog()`).
- **🔒 Enterprise Security**:
  - Full CSRF protection via nonces (`check_ajax_referer()`).
  - Strict capability verification (`current_user_can('edit_posts')`).
  - Stored XSS prevention with `wp_kses_post()`.
- **🚀 Zero Conflict Architecture**:
  - Completely decoupled from bundled jQuery overrides — uses official WordPress core jQuery.
  - Scoped admin and frontend CSS to prevent theme and dashboard styling contamination.
  - Assets are only enqueued on the plugin's screen.
- **🌍 Internationalization (i18n)**: 100% translation-ready with an up-to-date `.pot` file.

---

## 📁 Architecture Overview

```text
crediblock/
├── credipost.php                       # Plugin bootstrap & lifecycle hooks
├── uninstall.php                       # Cleanup on plugin deletion
├── includes/
│   ├── class-credipost.php             # Core orchestration & dependency loader
│   ├── class-credipost-loader.php      # Hook & shortcode registry
│   ├── class-credipost-scraper.php     # DOM-based web scraping service
│   ├── class-credipost-i18n.php        # Text domain internationalization
│   ├── class-credipost-activator.php   # Activation tasks
│   └── class-credipost-deactivator.php # Deactivation tasks
├── admin/
│   ├── class-credipost-admin.php       # Admin menus, asset enqueuing & AJAX endpoints
│   ├── css/credipost-admin.css         # Scoped admin styling & print rules
│   ├── js/credipost-admin.js           # AJAX scraping, pruning & undo interactions
│   └── partials/
│       └── credipost-admin-display.php # Admin curation dashboard view
├── public/
│   ├── class-credipost-public.php      # [CREDIPOST] shortcode handler & on-demand assets
│   ├── css/credipost-public.css        # Scoped frontend shortcode styling
│   ├── js/credipost-public.js          # Frontend curation script
│   └── partials/
│       └── credipost-public-display.php# Shortcode view template
└── languages/
    └── credipost.pot                   # Translation template
```

---

## 🚀 Installation

### From Zip Archive
1. Download or clone this repository into `/wp-content/plugins/crediblock/`.
2. In the WordPress Admin dashboard, navigate to **Plugins > Installed Plugins**.
3. Locate **Credipost** and click **Activate**.

### Requirements
- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher (fully compatible with PHP 8.1, 8.2, and 8.3)
- **PHP Extensions**: `ext-dom`, `ext-libxml`, `ext-mbstring`

---

## 📖 Usage

### 1. In WordPress Admin
1. Navigate to the **Credipost** menu item in your WordPress admin sidebar.
2. Enter the URL of any web article or video link in the URL bar.
3. Click **Fetch Article** to extract the article's text, images, and title, or click **Get Video Embed** for multimedia.
4. Review the extracted content:
   - **Edit Title**: Customize the post title.
   - **Prune Content**: Hover over unwanted paragraphs, headings, or images and click the red cross (**×**) to remove them. Use the **Undo** button if you make a mistake.
   - **Configure Settings**: Select the desired category, post status (`Draft` recommended), and target multisite blog (if applicable).
5. Click **Save as Post**. A link to edit the newly created post in the WordPress editor will appear upon completion.
6. Optional: Click **Print Article** to generate a clean, print-friendly copy without admin chrome.

### 2. Frontend Shortcode (Optional)
Authorized editors (`edit_posts` capability) can also use the curation tool directly on frontend pages:

```text
[CREDIPOST]
```
*(or lowercase `[credipost]`)*

---

## 🛡️ Security

Credipost adheres to WordPress Coding Standards and OWASP security recommendations:
- **CSRF Defense**: All AJAX requests are signed with `wp_create_nonce('credipost_admin_nonce')` and verified server-side.
- **SSRF Defense**: External HTTP requests are dispatched exclusively via `wp_safe_remote_get()`, eliminating internal network enumeration.
- **XSS Defense**: Content is sanitized with `wp_kses_post()` before insertion into the database, neutralizing malicious scripts and attributes.
- **Privilege Separation**: Low-privilege users (Subscribers, Guests) cannot execute any scraping or publishing endpoints.

---

## 🌐 Localization

The plugin is fully internationalized. To translate Credipost into your language:
1. Open [`languages/credipost.pot`](languages/credipost.pot) in **Poedit** or your preferred translation tool.
2. Create your `.po` and `.mo` files (e.g. `credipost-es_ES.po`).
3. Save the compiled translation files in the `/languages/` directory.

---

## 📄 License

Credipost is open-source software licensed under the **GNU General Public License v2.0 or later** (GPL-2.0+).  
See [LICENSE](http://www.gnu.org/licenses/gpl-2.0.txt) for details.
