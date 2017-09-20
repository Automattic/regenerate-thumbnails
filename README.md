# Regenerate Thumbnails [![Build Status](https://travis-ci.org/Viper007Bond/regenerate-thumbnails.svg?branch=dev%2Fv3-rewrite)](https://travis-ci.org/Viper007Bond/regenerate-thumbnails)

Regenerate Thumbnails is a WordPress plugin that will regenerate all thumbnail sizes for one or more images that have been uploaded to your WordPress Media Library.

This is useful for situations such as:

* A new thumbnail size has been added and you want past uploads to have a thumbnail in that size.
* You've changed the dimensions of an existing thumbnail size, for example via Settings → Media.
* You've switched to a new WordPress theme that uses featured images of a different size.

## Alternatives

### WP-CLI

If you have command line access to your server, I highly recommend using [WP-CLI](https://wp-cli.org/) instead of this plugin as it's faster (no HTTP requests overhead) and can be run inside of a `screen` for those with many thumbnails. For details, see the documentation of its [regenerate](https://wp-cli.org/commands/media/regenerate/) command.

### Jetpack's Photon Module

[Jetpack](https://jetpack.com/) is a plugin by Automattic, makers of WordPress.com. It gives your self-hosted WordPress site some of the functionality that is available to WordPress.com-hosted sites.

[The Photon module](https://jetpack.com/support/photon/) makes the images on your site be served from WordPress.com's global content delivery network (CDN) which should speed up the loading of images. Importantly though it can create thumbnails on the fly which means you'll never need to use this plugin.

I personally use Photon on my own website.

*Disclaimer: I work for Automattic but I would recommend Photon even if I didn't.*
