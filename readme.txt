=== Debug Bar ===
Contributors: wordpressdotorg, ryan, westi, koopersmith, duck_, mitchoyoshitaka, dd32
Tags: debug
Tested up to: 4.5
Stable tag: 0.8.4
Requires at least: 3.2

Adds a debug menu to the admin bar that shows query, cache, and other helpful debugging information.

== Description ==

Adds a debug menu to the admin bar that shows query, cache, and other helpful debugging information.

A must for developers!

When `WP_DEBUG` is enabled it also tracks PHP Warnings and Notices to make them easier to find.

When `SAVEQUERIES` is enabled the mysql queries are tracked and displayed.

To enable these options, add the following code to your `wp-config.php` file:
`
define( 'WP_DEBUG', true );
define( 'SAVEQUERIES', true );
`

Add a PHP/MySQL console with the [Debug Bar Console plugin](https://wordpress.org/plugins/debug-bar-console/).

There are numerous other add-ons available to get more insight into, for instance, the registered Post Types, Shortcodes, WP Cron, Language file loading, Actions and Filters and so on. Just [search the plugin directory for 'Debug Bar'](https://wordpress.org/plugins/search.php?q=debug+bar).

== Upgrade Notice ==

= 0.8.4 =
Updated to avoid incompatibilities with some extensions.

= 0.8.3 =
Updated to avoid PHP7 Deprecated notices.

= 0.8.2 =
Updated to handle a new deprecated message in WordPress 4.0.

= 0.8.1 =
Minor security fix.

= 0.8 =
WordPress 3.3 compatibility
UI refresh
Removed jQuery UI requirement
Full screen by default
New debug-bar query parameter to show on page load
Removed display cookies
JavaScript error tracking (disabled by default)

= 0.7 =
Made compatible with PHP < 5.2.0
CSS Tweaks
Load JavaScript in Footer
Fixed display issues for WP_Query debug on CPT archives pages
SQL/DB error tracking

= 0.6 =
Added maximize/restore button
Added cookie to keep track of debug bar state
Added post type information to WP_Query tab
Bug fix where bottom of page was obscured in the admin

= 0.5 =
New UI
Backend rewritten with a class for each panel
Many miscellaneous improvements

= 0.4.1 =
Compatibility updates for trunk

= 0.4 =
Added DB Version information
Updated PHP Warning and Notice tracking so that multiple different errors on the same line are tracked
Compatibility updates for trunk

= 0.3 =
Added WordPress Query infomation
Added Request parsing information

= 0.2 =
Added PHP Notice / Warning tracking when WP_DEBUG enabled
Added deprecated function usage tracking

= 0.1 =
Initial Release

== Changelog ==

= Trunk =
* [Bugfix] PHP Error logging was only started when the admin bar was initialized which meant it did not catch nor show any errors generated before that point.
* [Bugfix] Deprecated notice logging was only started when the admin bar was initialized which meant it did not catch nor show any errors generated before that point.
* [Bugfix] Fix some error notices for PHP 5.2.
* [Bugfix] Diminish bleed through of front-end CSS styling.
* [Bugfix] Added the missing message text for deprecated arguments.
* [Bugfix] Fixed the classification of the various deprecated notices - previously all would be seen as functions.
* [Bugfix] Prevent things looking weird when the current locale is RTL based.
* [Enhancement] Added logging of PHP strict, deprecated and silenced error notices to the PHP panel.
* [Enhancement] Added logging of deprecated constructors to the deprecated panel.
* [Enhancement] Add a new panel showing 'doing it wrong' notices.
* [Enhancement] Even though display of deprecated/doing it wrong and other notices is not send to the screen - as they are displayed in their respective debug bar panels instead -, *do* continue to send them to the error log if one has been set up.
* [Enhancement] Improve usability by showing error counts in the panel menu for PHP/Deprecated/JS/Doing It Wrong.
* [Enhancement] Improve usability by colourizing the button in the admin bar late. This allows for the button to show there are warning or notices even after the button was created.
* [Enhancement] Improve usability by also colourizing the button in the admin bar for Deprecated/Doing it Wrong notices.
* [Enhancement] Improve readability of the WP Query - Queried object information.
* [Enhancement] Introduce the `debug_bar_enable` filter which allows for selectively giving access to the debug bar for additional users, such as site-admins in a multi-site environment.
* [Enhancement] Allow for large(r) number of add-ons in the panel menu.
* [Enhancement] Throw deprecated constructor notices for Debug Bar plugins using the old constructors.
* [Enhancement] The Debug bar plugin is now fully translatable. If you'd like to translate the plugin to your own language, head on over to [GlotPress](https://translate.wordpress.org/projects/wp-plugins/debug-bar).
* Minor refactoring

= 0.8.4 =
Updated to avoid incompatibilities with some extensions.

= 0.8.3 =
Updated to avoid PHP7 Deprecated notices.

= 0.8.2 =
Updated to handle a new deprecated message in WordPress 4.0.

= 0.8.1 =
Minor security fix.

= 0.8 =
WordPress 3.3 compatibility
UI refresh
Removed jQuery UI requirement
Full screen by default
New debug-bar query parameter to show on page load
Removed display cookies
JavaScript error tracking (disabled by default)

= 0.7 =
Made compatible with PHP < 5.2.0
CSS Tweaks
Load JavaScript in Footer
Fixed display issues for WP_Query debug on CPT archives pages
SQL/DB error tracking

= 0.6 =
Added maximize/restore button
Added cookie to keep track of debug bar state
Added post type information to WP_Query tab
Bug fix where bottom of page was obscured in the admin

= 0.5 =
New UI
Backend rewritten with a class for each panel
Many miscellaneous improvements

= 0.4.1 =
Compatibility updates for trunk

= 0.4 =
Added DB Version information
Updated PHP Warning and Notice tracking so that multiple different errors on the same line are tracked
Compatibility updates for trunk

= 0.3 =
Added WordPress Query infomation
Added Request parsing information

= 0.2 =
Added PHP Notice / Warning tracking when WP_DEBUG enabled
Added deprecated function usage tracking

= 0.1 =
Initial Release

== Installation ==

Use automatic installer.
