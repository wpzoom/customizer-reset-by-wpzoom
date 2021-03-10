=== Customizer Reset ===
Contributors: WPZOOM, nvartolomei, clytoncollie
Donate link: http://wpzoom.com/
Tags: customize, customizer, reset
Requires at least: 3.4
Tested up to: 5.7
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Reset theme customizations made via WordPress Customizer.

== Description ==

Reset theme customizations made via WordPress Customizer.

This should work with all themes and plugins that uses theme_mod settings type for storing modifications,
basically it removes all theme modifications that are registered via Customizer API.

= Get Involved =

Looking to contribute code to this plugin? Go ahead and [fork the repository over at GitHub](https://github.com/wpzoom/customizer-reset).

== Frequently Asked Questions ==

= Why it didn't reset something? =

1. Setting is not registered correctly via Customizer API
2. Setting is using option type for storing values

= Is reset reversible? =

*No*. Once you reset theme modifications you can not go back, you will need to redo all modifications from scratch.

== Screenshots ==

1. Reset along with Save button in WordPress Customizer panel.

== Changelog ==

= 1.0.1 =
* Minor cleanup

= 1.0 =
* Initial Release
