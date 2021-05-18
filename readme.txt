=== Customizer Reset ===
Contributors: WPZOOM, nvartolomei, claytoncollie
Donate link: https://www.wpzoom.com/
Tags: customize, customizer reset, customizer, wpzoom, divi, theme, astra
Requires PHP: 5.6
Requires at least: 3.4
Tested up to: 5.7
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Reset theme customizations made via WordPress Customizer.

== Description ==

Reset theme customizations made via WordPress Customizer in one click. Works with 99% of themes, including [WPZOOM themes](https://www.wpzoom.com/themes/) and popular themes like **Divi**, **Astra**.

This plugin removes all theme modifications that are registered via Customizer API.

Works with all themes and plugins that use **theme_mod** settings type for storing modifications.

> Please keep in mind that some themes uses different method to save customizer settings, [let us know](https://wordpress.org/support/plugin/customizer-reset-by-wpzoom/) if this plugin isn't compatible with your theme.

= 📌 What's new in version 1.1? =
* Added compatibility with: [Divi](https://www.elegantthemes.com/gallery/divi/), [Astra](https://wordpress.org/themes/astra/)

== Get Involved ==

Looking to contribute code to this plugin? Go ahead and [fork the repository over at GitHub](https://github.com/wpzoom/customizer-reset).

== Frequently Asked Questions ==

= What type of customizer settings are reset?

Theme settings saved as `theme_mod` will be reset.

= Why did the plugin not reset a particular setting? =

1. Setting is not registered correctly via Customizer API
2. Setting is using option type for storing values

= Is reset reversible? =

*No*. Once you reset theme modifications you can not go back. You will need to redo all modifications from scratch.

= Who built this plugin? =

This handy plugin is brought to you by the team at WPZOOM.

https://www.wpzoom.com

== Screenshots ==

1. Reset along with Save button in WordPress Customizer panel.

== Changelog ==

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
