# Regenerate Thumbnails

[![Travis CI Build Status](https://travis-ci.org/Viper007Bond/regenerate-thumbnails.svg?branch=dev%2Fv3-rewrite)](https://travis-ci.org/Viper007Bond/regenerate-thumbnails)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/regenerate-thumbnails.svg)](https://wordpress.org/plugins/regenerate-thumbnails/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/regenerate-thumbnails.svg)](https://wordpress.org/support/plugin/regenerate-thumbnails/reviews/)


Regenerate Thumbnails is a WordPress plugin that will regenerate all thumbnail sizes for one or more images that have been uploaded to your WordPress Media Library.

This is useful for situations such as:

* A new thumbnail size has been added and you want past uploads to have a thumbnail in that size.
* You've changed the dimensions of an existing thumbnail size, for example via Settings → Media.
* You've switched to a new WordPress theme that uses featured images of a different size.

## Alternatives

### WP-CLI

If you have command line access to your server, I highly recommend using [WP-CLI](https://wp-cli.org/) instead of this plugin as it's faster (no HTTP requests overhead) and can be run inside of a `screen` for those with many thumbnails. For details, see the documentation of its [`media regenerate` command](https://developer.wordpress.org/cli/commands/media/regenerate/).

### Jetpack's Photon Module

[Jetpack](https://jetpack.com/) is a plugin by Automattic, makers of WordPress.com. It gives your self-hosted WordPress site some of the functionality that is available to WordPress.com-hosted sites.

[The Photon module](https://jetpack.com/support/photon/) makes the images on your site be served from WordPress.com's global content delivery network (CDN) which should speed up the loading of images. Importantly though it can create thumbnails on the fly which means you'll never need to use this plugin.

I personally use Photon on my own website.

*Disclaimer: I work for Automattic but I would recommend Photon even if I didn't.*

## Building The Plugin

The latest release can be [downloaded from WordPress.org](https://wordpress.org/plugins/regenerate-thumbnails/), but if you wish to build your own copy, here's how:

1. Make sure you have [Node.js](https://nodejs.org/) installed.

2. Clone this repository inside your `plugins` directory:
	```
	$ git clone https://github.com/Viper007Bond/regenerate-thumbnails.git
	$ cd regenerate-thumbnails
	```

3. Install [yarn](https://www.npmjs.com/package/yarn) package:
	```
	npm install -g yarn
	```

4. Install the other dependencies:
	```
	yarn
	```

5. Build the plugin's JavaScript file in production mode:
	```
	yarn build
	```

6. Activate the plugin and visit Tools → Regenerate Thumbnails.

### Building The JavaScript File In Development Mode

The development build will create a build without minifying or deduping code and will enable Vue.js's development mode as well.

```
yarn dev
```

Alternatively if you want to automatically rebuild the file any time one of the source files changes, use the `watch` command:

```
yarn watch
```

## Unit Tests

To run the [PHPUnit](https://phpunit.de/) unit tests, first run the `install-wp-tests.sh` script from the `bin` directory. Then simply run `phpunit` from the plugin's root directory.