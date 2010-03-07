=== Regenerate Thumbnails ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: thumbnail, thumbnails
Requires at least: 2.6
Tested up to: 2.9
Stable tag: trunk

Allows you to regenerate all thumbnails after changing the thumbnail sizes.

== Description ==

Regenerate Thumbnails allows you to regenerate the thumbnails for all of your image attachments. This is very handy if you've changed any of your thumbnail dimensions (via Settings -> Media) after previously uploading images.

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

Then just visit your admin area and activate the plugin.

###Plugin Usage###

Visit Tools -> Regen. Thumbnails to get started.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

== Screenshots ==

1. The plugin at work.

== ChangeLog ==

= Version 2.0.2 =

* Directly query the database to only fetch what the plugin needs (the attachment ID). This will reduce the memory required as it's not storing the whole row for each attachment.

= Version 2.0.1 =

* I accidentally left a `check_admin_referer()` (nonce check) commented out.

= Version 2.0.0 =

* Recoded from scratch. Now uses an AJAX request per attachment to do the resizing. No more PHP maximum execution time errors or anything like that. Also features a pretty progress bar to let the user know how it's going.

= Version 1.1.0 =

* WordPress 2.7 updates -- code + UI. Thanks to jdub and Patrick F.

= Version 1.0.0 =

* Initial release.