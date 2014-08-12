=== Menu Maker ===
Contributors: indranil
Tags: menu, navigation, option
Requires at least: 2.7
Donate link: http://pledgie.com/campaigns/7540
Tested up to: 2.9
Stable tag: 0.6

The Menu Maker plugin helps in creating a menu for your site. Usually this can be used for creating a navigation menu.

== Description ==

The Menu Maker plugin helps in creating a menu for your site. Usually this can be used for creating a navigation menu. Check for updates at [Troidus.com](http://troidus.com/ "Troidus").

= Instructions =

Mostly, it is self-explanatory. But for posts and pages, you need to enter the ID. And for home, the link field should be blank.

= What's new in version 0.6? =

The possible choices are the home page, a single post or page, an external link or a category.

You can specify the active class, and a home navigation item.

= Upcoming in next version =

Multiple lists. A revamped interface. (I usually get very little time to work on this, so if you really want these features, drop me a line and I'll work faster, or donate to the cause!! :P )

== Installation ==

1. Upload the `menumaker` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Options > Menu Maker and change options and add menus
1. Place `<?php id_menu_maker(); ?>` in your templates

= Uninstalling =

Deactivate the plugin, then uninstall it from the WP Admin interface. This performs a thorough uninstallation of the plugin, removing any DB rows created.

== Frequently Asked Questions ==

= How do I add posts/pages =

To add a post or page, insert the ID of the post/page into the last field of the menu maker. That will automatically create a link to the post/page.

= Can it take external links =

Yup, it currently supports external links, posts, and pages.

== Screenshots ==

1. The options page.

== Changelog ==

0.6 - Now adds Categories to the menu option! Go ahead and enjoy..

0.5.2 - Uninstalls gracefully.

0.5 - Home page link type, and Active menu item.

0.4.4 - Removed the removal of all data on deactivation of plugin.

0.4.2 - Initial release.