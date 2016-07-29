=== Regenerate Thumbnails ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: thumbnail, thumbnails
Requires at least: 2.8
Tested up to: 4.6
Stable tag: trunk

Allows you to regenerate your thumbnails after changing the thumbnail sizes.

== Description ==

Regenerate Thumbnails allows you to regenerate the thumbnails for your image attachments. This is very handy if you've changed any of your thumbnail dimensions (via Settings -> Media) after previously uploading images or have changed to a theme with different featured post image dimensions.

You can either regenerate the thumbnails for all image uploads, individual image uploads, or specific multiple image uploads.

See the [screenshots tab](http://wordpress.org/extend/plugins/regenerate-thumbnails/screenshots/) for more details.

== Installation ==

1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "Regenerate Thumbnails".
3. Click install.
4. Click activate.

== Screenshots ==

1. The plugin at work regenerating thumbnails
2. You can resize single images by hovering over their row in the Media Library
2. You can resize specific multiples images using the checkboxes and the "Bulk Actions" dropdown

== ChangeLog ==

= Version 2.2.6 =

* PHP 7 compatibility.

= Version 2.2.5 =

* Updates relating to plugin language pack support.

= Version 2.2.4 =

* Better AJAX response error handling in the JavaScript. This should fix a long-standing bug in this plugin. Props Hew Sutton.

= Version 2.2.3 =

* Make the capability required to use this plugin filterable so themes and other plugins can change it. Props [Jackson Whelan](http://jacksonwhelan.com/).

= Version 2.2.2 =

* Don't check the nonce until we're sure that the action called was for this plugin. Fixes lots of "Are you sure you want to do this?" error messages.

= Version 2.2.1 =

* Fix the bottom bulk action dropdown. Thanks Stefan for pointing out the issue!

= Version 2.2.0 =

* Changes to the Bulk Action functionality were made shortly before the release of WordPress 3.1 which broke the way I implemented the specific multiple image regeneration feature. This version adds to the Bulk Action menu using Javascript as that's the only way to do it currently.

= Version 2.1.3 =

* Move the `error_reporting()` call in the AJAX handler to the beginning so that we're more sure that no PHP errors are outputted. Some hosts disable usage of `set_time_limit()` and calling it was causing a PHP warning to be outputted.

= Version 2.1.2 =

* When regenerating all images, newest images are done first rather than the oldest.
* Fixed a bug with regeneration error reporting in some browsers. Thanks to pete-sch for reporting the error.
* Supress PHP errors in the AJAX handler to avoid sending an invalid JSON response. Thanks to pete-sch for reporting the error.
* Better and more detailed error reporting for when `wp_generate_attachment_metadata()` fails.

= Version 2.1.1 =

* Clean up the wording a bit to better match the new features and just be easier to understand.
* Updated screenshots.

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

== Upgrade Notice ==

= 2.2.4 =
Better AJAX response error handling in the JavaScript. This should fix a long-standing bug in this plugin. Props Hew Sutton.

= 2.2.3 =
Make the capability required to use this plugin filterable so themes and other plugins can change it. Props [Jackson Whelan](http://jacksonwhelan.com/).

= 2.2.2 =
Fixes lots of "Are you sure you want to do this?" error messages.

= 2.2.1 =
Fix the bottom bulk action dropdown. Thanks Stefan for pointing out the issue!