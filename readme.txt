=== Plugin Name ===
Contributors: wpzoom, nvartolomei
Donate link: http://wpzoom.com/
Tags: customize, customizer, reset
Requires at least: 3.4
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Reset theme customizations (theme_mods) made via WordPress Customizer

== Description ==

Reset theme customizations made via WordPress Customizer.

This should work with all themes and plugins that uses theme_mod settings type for storing modifications,
basically it removes all theme modifications that are registered via Customizer API.

== Frequently Asked Questions ==

= Why it didn't reset something? =

1. Setting is not registered correctly via Customizer API
2. Setting is using option type for storing values

= Is reset reversible? =

*No*. Once you reset theme modifications you can not go back, you will need to redo all modifications from scratch.

== Screenshots ==

1. Reset along with Save button.

== Changelog ==

= 1.0 =
* Initial Release
