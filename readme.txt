=== Customizer Backup & Reset ===
Contributors: WPZOOM
Donate link: https://www.wpzoom.com/
Author URI: https://www.wpzoom.com/
Tags: customizer, customizer reset, backup, export, import
Requires PHP: 7.4
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 2.0.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Reset theme customizations made via WordPress Customizer with backup, export, and import features.

== Description ==

Reset theme customizations made via WordPress Customizer with enhanced safety features. Works with 99% of themes, including [WPZOOM themes](https://www.wpzoom.com/themes/) and popular themes like **Inspiro**, **Divi**, **Astra**, **GeneratePress**.

This plugin removes all theme modifications that are registered via Customizer API, with the added safety of backup, export, and import capabilities.

Works with all themes and plugins that use **theme_mod** settings type for storing modifications.

> Please keep in mind that some themes uses different method to save customizer settings, [let us know](https://wordpress.org/support/plugin/customizer-reset-by-wpzoom/) if this plugin isn't compatible with your theme.

= 📌 What's new in version 2.0.? =
* **NEW:** Redesigned UI & New Customizer Section
* **NEW:** Import/Export Settings - Dual format support (JSON and DAT)
* **NEW:** Additional CSS Support - Optional reset checkbox and included in all exports
* **NEW:** Backup & Reset - Automatically create a backup before resetting (5 rotating backups)
* **NEW:** Backup History - View and restore from the last 5 backups
* **NEW:** GeneratePress Theme compatibility - Including dynamic CSS cache clearing
* Improved UX with customizer section panel and inline notifications
* Added translation support


== Get Involved ==

Looking to contribute code to this plugin? Go ahead and [fork the repository over at GitHub](https://github.com/wpzoom/customizer-reset).

= 🙌 FOLLOW US =

* 🐦 [X](https://x.com/wpzoom)
* 📘 [Facebook](https://facebook.com/wpzoom)
* 📘 [Facebook Group](https://www.facebook.com/groups/wpzoom)
* 🌄 [Instagram](https://instagram.com/wpzoom)


== Frequently Asked Questions ==

= What type of customizer settings are reset?

Theme settings saved as `theme_mod` will be reset.

= Why did the plugin not reset a particular setting? =

1. Setting is not registered correctly via Customizer API
2. Setting is using option type for storing values

= Is reset reversible? =

**Yes, if you use "Backup & Reset"!** The plugin now offers a backup feature that stores your settings for 30 days. Use the "Backup & Reset" button to automatically create a backup before resetting.

If you use the regular "Reset" button without backup, the action is irreversible and you'll need to redo all modifications from scratch.

= Who built this plugin? =

This handy plugin is brought to you by the team at WPZOOM.

https://www.wpzoom.com

== Screenshots ==

1. Reset along with Save button in WordPress Customizer panel.

== Changelog ==

= 2.0.2 =
* Minor bug fix in WP 6.9

= 2.0.1 =
* Minor bug fix

= 2.0.0 =
* **NEW:** Redesigned UI with new customizer section panel
* **NEW:** Import/Export - Dual format support (JSON recommended, DAT for compatibility)
* **NEW:** Additional CSS Reset - Optional checkbox to reset Additional CSS along with theme modifications
* **NEW:** Backup System - 5 rotating backups stored for 30 days via transients
* Add compatibility with GeneratePress theme
* Add translation loading support

= 1.1.1 =
* Add compatibility with Astra Theme

= 1.1.0 =
* Refactor code to use PHP Namespaces
* Add compatibility with Divi Theme Customizer settings
* Add PHP Docblocks for all functions
* Add automatic deploy to wordpress.org with GitHub Action
* Add PHP matrix test with GitHub Action
* Add CONTRIBUTING.md
* Add LICENSE.md
* Add README.md
* Add support for WordPress Coding Standards
* Add support for PHPstan
* Add icon image 128 and 256 for wordpress.org
* Add GitHub templates for bug, features, and questions
* Add support for Composer

= 1.0.1 =
* Minor cleanup

= 1.0 =
* Initial Release
