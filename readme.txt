=== Regenerate Thumbnails ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: thumbnail, thumbnails
Requires at least: 2.8
Tested up to: 3.0
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

= Version 2.1.0 =

Lots of new features!

* Thanks to a lot of jQuery help from [Boris Schapira](http://borisschapira.com/), a failed image regeneration will no longer stop the whole process.
* The results of each image regeneration is now outputted. You can easily see which images were successfully regenerated and which failed. Was inspired by a concept by Boris.
* There is now a button on the regeneration page that will allow you to abort resizing images for any reason. Based on code by Boris.
* You can now regenerate single images from the Media page. The link to do so will show up in the actions list when you hover over the row.
* You can now bulk regenerate multiple from the Media page. Check the boxes and then select "Regenerate Thumbnails" form the "Bulk Actions" dropdown. WordPress 3.1+ only.
* The total time that the regeneration process took is now displayed in the final status message.
* jQuery UI Progressbar version upgraded.

= Version 2.0.3 =

* Switch out deprecated function call.

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