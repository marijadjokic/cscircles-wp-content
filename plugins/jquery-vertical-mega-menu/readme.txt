=== JQuery Vertical Mega Menu Widget ===
Contributors: remix4
Donate link: http://www.designchemical.com/blog/index.php/wordpress-plugins/wordpress-plugin-jquery-vertical-mega-menu-widget/#form-donate
Tags: jquery, flyout, mega, menu, vertical, animated, css, navigation, widget
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.3.3

Creates a widget, which allows you to add vertical mega menus to your side columns using any Wordpress custom menu.

== Description ==

Creates a widget, which allows you to add flyout vertical mega menus from any standard Wordpress custom menu using jQuery. Can handle multiple vertical mega menus on each page and offers 3 animation effects - show/hide, fade In or slide out. Widget also gives the option of selecting left or right for the flyout menu depending on where you side column is. For best results use 3 levels for the custom menu.

= Menu Options =

The widget has several parameters that can be configured to help cutomise the vertical mega menu:

* Number Items Per Row - Select the number of sub-menu items to be shown on each row of the mega menu.
* Animation Effect - The effect used to display the flyout mega menu - select either show, fade in or slide out.
* Animation Direction - Choose the direction for the flyout menu - either left or right.
* Animation Speed - The speed at which the dropdown menu will open/close
* Skin - 8 different sample skins are available to give examples of css that can be used to style your own vertical mega menu

[__See demo__](http://www.designchemical.com/lab/demo-wordpress-jquery-vertical-mega-menu-plugin.html)

[__Plugin Home Page__](http://www.designchemical.com/blog/index.php/wordpress-plugins/wordpress-plugin-jquery-vertical-mega-menu-widget/)

== Installation ==

1. Upload the plugin through `Plugins > Add New > Upload` interface or upload `jquery-vertical-mega-menu` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the widgets section, select the jQuery vertical mega menu widget and add to one of your widget areas
4. Select one of the WP menus, set the required settings and save your widget

== Frequently Asked Questions ==

[__Also check out our vertical mega menu faq page__](http://www.designchemical.com/blog/index.php/frequently-asked-questions/jquery-vertical-mega-menu/)

= The menu appears on the page but does not work. Why? =

One main reason for this is that the plugin adds the required jQuery code to your template footer. Make sure that your template files contain the wp_footer() function.

Another likely cause is due to other non-functioning plugins, which may have errors and cause the plugin javascript to not load. Remove any unwanted plugins and try again. Checking with Firebug will show where these error are occuring.

= How should I structure my custom menu to get the best results? =

The widget works by taking the standard menu structure and creating the sub-menus from the nested links. To get the full effect you need to have 3 levels within the custom menu:

1. First level is used for the main menu items
2. The second level is used to create the sub-menu headings
3. The 3rd level links are then grouped together under the headings to create the menu options

Example: Products --> Category --> Product Ranges

= The menu appears on the page and looks correct but I cant see the flyout menu? =

Make sure that your theme does not have the side column, where your menu is located, set to overflow: hidden in the style sheet file - this will hide the flyout menu

== Screenshots ==

1. Vertical mega menu widget in edit mode
2. Sample custom menu structure
3. Sample menu skins
4. Example of open mega menu

== Changelog ==

= 1.3.3 = 
* Update: change wp_enqueue_script to function

= 1.3.2 = 
* Fixed: Error with default values

= 1.3.1 = 
* Fixed: Bug with "No theme" option

= 1.3 = 
* Fixed: IE invalid argument in jQuery plugin

= 1.2 = 
* Fixed: Bug incorrectly calculating position of sub menu

= 1.1 = 
* Fixed: IE7 bug with sub-menu width

= 1.0 = 
* First release

== Upgrade Notice ==
