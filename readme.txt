=== Regenerate Thumbnails ===
Contributors: Viper007Bond
Tags: thumbnail, thumbnails, post thumbnail, post thumbnails
Requires at least: 4.7
Tested up to: 5.7
Requires PHP: 5.2.4
Stable tag: 3.1.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Regenerate the thumbnails for one or more of your image uploads. Useful when changing their sizes or your theme.

== Description ==

Regenerate Thumbnails allows you to regenerate all thumbnail sizes for one or more images that have been uploaded to your Media Library.

This is useful for situations such as:

* A new thumbnail size has been added and you want past uploads to have a thumbnail in that size.
* You've changed the dimensions of an existing thumbnail size, for example via Settings â Media.
* You've switched to a new WordPress theme that uses featured images of a different size.

It also offers the ability to delete old, unused thumbnails in order to free up server space.

= In Memory of Alex Mills =

In February 2019 Alex Mills, the author of this plugin, [passed away](https://alex.blog/2019/02/27/from-alexs-family/). He leaves behind a number of plugins which will be maintained by Automattic and members of the WordPress community. If this plugin is useful to you please consider donating to the Oregon Health and Science University. You can find more information [here](https://alex.blog/2019/03/13/in-memory-of-alex-donation-link-update/).

= Alternatives =

**WP-CLI**

If you have command line access to your server, I highly recommend using [WP-CLI](https://wp-cli.org/) instead of this plugin as it's faster (no HTTP requests overhead) and can be run inside of a `screen` for those with many thumbnails. For details, see the documentation of its [`media regenerate` command](https://developer.wordpress.org/cli/commands/media/regenerate/).

**Jetpack's Photon Module**

[Jetpack](https://jetpack.com/) is a plugin by Automattic, makers of WordPress.com. It gives your self-hosted WordPress site some of the functionality that is available to WordPress.com-hosted sites.

[The Photon module](https://jetpack.com/support/photon/) makes the images on your site be served from WordPress.com's global content delivery network (CDN) which should speed up the loading of images. Importantly though it can create thumbnails on the fly which means you'll never need to use this plugin.

I personally use Photon on my own website.

*Disclaimer: I work for Automattic but I would recommend Photon even if I didn't.*

= Need Help? Found A Bug? Want To Contribute Code? =

Support for this plugin is provided via the [WordPress.org forums](https://wordpress.org/support/plugin/regenerate-thumbnails).

The source code for this plugin is available on [GitHub](https://github.com/automattic/regenerate-thumbnails).

== Installation ==

1. Go to your admin area and select Plugins â Add New from the menu.
2. Search for "Regenerate Thumbnails".
3. Click install.
4. Click activate.
5. Navigate to Tools â Regenerate Thumbnails.

== Frequently Asked Questions ==

= Is this plugin [GDPR](https://en.wikipedia.org/wiki/General_Data_Protection_Regulation) compliant? =

This plugin does not log nor transmit any user data. Infact it doesn't even do anything on the user-facing part of your website, only in the admin area. This means it should be compliant but I'm not a lawyer.

== Screenshots ==

1. The main plugin interface.
2. Regenerating in progress.
3. Interface for regenerating a single attachment.
4. Individual images can be regenerated from the media library in list view.
5. They can also be regenerated from the edit attachment screen.

== ChangeLog ==

= Version 3.1.5 =

* Fix: Don't overwrite 'All X Attachment' button label with featured images count.

= Version 3.1.4 =

* Fix: Don't attempt to regenerate SVG's.
* Bump tested version.
* Update dependencies.

= Version 3.1.3 =

* Update plugin dependencies to the latest version.

= Version 3.1.2 =
* Use wp_get_original_image_path() in WordPress 5.3

= Version 3.1.1 =

* Minor fix to avoid a divide by zero error when displaying thumbnail filenames.

= Version 3.1.0 =

* Bring back the ability to delete old, unregistered thumbnail sizes. Support for updating post contents is still disabled (too buggy).
* Various code improvements including string localization disambiguation.

= Version 3.0.2 =

* Fix slowdown in certain cases in the media library.
* Fix not being able to regenerate existing thumbnails for single images. Props @idofri.
* Fix JavaScript error that could occur if the REST API response was unexpected (empty or PHP error).
* Fix bug related to multibyte filenames.
* If an image is used as the featured image on multiple posts, only regenerate it once instead of once per post.

= Version 3.0.1 =

* Temporarily disable the update post functionality. I tested it a lot but it seems there's still some bugs.
* Temporarily disable the delete old thumbnails functionality. It seems to work fine but without the update post functionality, it's not as useful.
* Try to more gracefully handle cases where there's missing metadata for attachments.
* Wait until `init` to initialize the plugin so themes can filter the plugin's capability. `plugins_loaded` is too early.
* Fix a JavaScript error that would cause the whole regeneration process to stop if an individual image returned non-JSON, such as a 500 error code.
* Accept GET requests for the regenerate REST API endpoint instead of just POSTs. For some reasons some people's sites are using GET despite the code saying use POST.
* Make the attachment ID clickable in error messages.
* Fetch 25 attachments at a time instead of 5. I was using 5 for testing.
* PHP notice fixes.

= Version 3.0.0 =

* Complete rewrite from scratch using Vue.js and the WordPress REST API.

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

= Upgrade Notice =
Support for WordPress 5.3
