=== SrbTransLatin ===
Contributors: pedjas
Tags: transliteration, serbian, cyrillic, latin, multilanguage, script
Requires at least: 2.6.1
Tested up to: 3.8.1
Stable tag: trunk

SrbTransLatin handles using Serbian language Cyrillic and Latin script. For Cyrillic content, visitor may choose to view it using Cyrillic or Latin.

== Description ==

Contents of the site should be written using Cyrillic script. Then, this plugin will allow users to choose to read contents in Cyrillic or Latin script.

If some contents is entered in Latin script, it would stay in Latin even if user chooses to use Cyrillic. Transliteration occurs only from Cyrillic to Latin script.

Site owner may set default for script to show: 
- Cyrillic,
- Latin and
- Cyrillic if visitor accepts it (has set some Cyrillic language as acceptable in his browser)

Script also may be selected manually, by adding ?lang=cir or ?lang=lat to document url. If parameter is not specified default script is used.

Site owner may use widget to allow visitors to choose among Cyrillic and Latin script. He may choose if script options are shown as html links or items of combo box. By default, html link are dipslayed. He also may set if widget would show title or not.

When user selects script, his choice stays permanent while he is on site. All internal links within site are altered to contain information about selected script. This means, when link is copied and pasted to some other site, it would contain information which script to use for displaying contents. There is an option to remember choosen script as cookie in visitor's brower so he has no need to set it again on future visits.

When transliteration occurs, everything in the HTML document is transliterated from Cyrillic to Latin script except if contents is placed among [lang id="skip"] and [/lang] tags. This leaves user to mark part of the text he does not want to be transliterated at all, meaning, some parts of Cyrillic text may stay Cyrillic even if user chooses to view site in Latin script.

Initially, when new article is posted using Cyrillic script in title, permalink is created with conversion to Latin script. Site owner may turn it of.

Transliteration works for all feeds too (atom, rdf, rss, rss2).


**Using script selector on custom places**

If you need to put script selector in site template outside widgets areas then you can use function stl_show_selector() provided with plugin. Function accepts four parameters (all optional):

stl_show_selector (selector_type, oneline_separator, cyrillic_caption, latin_caption)

*selector_type* chooses which type of selector to display: 

- links - list of choices in form of widget items

- list - list of choices in form of dropdown selection

- oneline - list of choices as one line separated by oneline_separator
	
Default value is 'oneline'
	
	
*oneline_separator* is a string that should be inservted between script selection items. Default value is '/'.

*cirillic_caption* is a string that should be used as caption for item of cyrillic sleection. Default is 'ћирилица'

*latin_caption* is a string that should be used as caption for item of latin sleection. Default is 'латиница'

To use this function just call it from place where you need code to be inserted, like:

`<?php stl_show_selector('oneline', '/', 'ћирилица', 'латиница') ?>`


**Changing image depending on selected script**

If you want to add image on page which contains Czrillic text and you want it to be replaced with image that contains Latin script then add =cir= as suffix in image name. On transliteration, Image name wil be changed to have =lat= as suffix.

Example: filename=cir=.jpg wil be replaced with filename=lat=.jpg

You have to provide Latin version image at same path as Czrillic image is placed, of course.


***Fix url colision with other plugins**

There are some plugings that also use default identificator 'lang', which SrbTransLatin uses to pass selected script information through url. To fix this there is an option to set this identificator. If you have problems with other plugins just change this to 'script' or 'lng', or something else as you like.

Pay attention that if zou change this option, al previous urls containing script selection will become invalid. It is best to set this before site is heavily indeksed or externally linked.




This plugin is developed inspired by two plugins WP Translit by Aleksandar Urošević and srlatin by Kimmo Suominen. I actually merged functionality of these two and expanded it with a lot of new functionality I needed for my site, and later with new functionality asked by plugin users.



== Installation ==

1. Extract package and upload `srbtranslatin` directory to `/wp-content/plugins/srbtranslatin` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add widget `Serbian Transliteration (links)` or `Serbian Transliteration (list)` and set it's options:
    - set title for script selection wigdet (default: Избор писма)
    - set if title should be shown for script selection widgets (default: title is not shown)
    - set if script selection is displayed as web links or options of combo box

Open Settings / SrbTransLat and set parameters according to your needs:

- set default script to be used if user do not make choice of script (default: Cyrillic)
- set if you want selected language to be saved in visitor's browser cookie
- set if permalinks are autogenerated in Latin script even if title is in Cyrillic (default: conversion to Latin)


== Frequently Asked Questions ==

= Can this transliterate my blog which uses Serbian Latin script into Serbian Cyrillic?

No. Such conversion is hard if imposible due to not unique transliteration rules. It is recomended that you create  contents on your site using Cyrillic script if you need both scripts.


= This is really nice script which I use on my site. Can I donate some money to author?

No. This is free to use script. If you want to show appreciation, spread the word, share the link to http://pedja.supurovic.net/projekti/srbtranslatin


== Changelog ==

= 1.28 =

Fixed displaying cookie using state setting option.


= 1.27 =

Function stl_show_selector() expanded with two new parameters allowing setting captions for script selection items.

= 1.26 =

Added option to let user change script identificator in url.

Added option to switch image on script change. If you use filename=cir=.jpg as image name, if latin script is selected, then image named filename=lat=.jpg will be used instead.

= 1.25 =

Changed priority of the plugin so it allows other plugins to alter contents before transliteration.


= 1.24 =

Added option which allows user to customize title for Cyril and Latin options in widget.


= 1.23 =

Minor fix. Title of 'latinica' was displayed using Cyrillic script. Changed to Latin script.

= 1.22 =

Fixed permalink transliteration issues with the latest Wordpress version.

Added function stl_show_selector() which may be used to insert script selector anywhere in template.


= 1.21 =

Added full support for exernal language files and Serbian translation included.


= 1.20 =

Partialy rewriten code according to new Wordpress API. Also, some new functionality added.


== Upgrade Notice ==

= 1.20 =

Widget is rewritten. Widget settings are separated from plugin options to widget options form. Thus, after update you will loose widget settings. Do not forget to check them out after upgrade.