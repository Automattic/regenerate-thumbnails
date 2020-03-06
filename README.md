# Regenerate Thumbnails

[![Travis CI Build Status](https://img.shields.io/travis/Viper007Bond/regenerate-thumbnails/master.svg?style=flat-square)](https://travis-ci.org/Viper007Bond/regenerate-thumbnails)
[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/regenerate-thumbnails.svg?style=flat-square)](https://wordpress.org/plugins/regenerate-thumbnails/)
[![WordPress Tested](https://img.shields.io/wordpress/v/regenerate-thumbnails.svg?style=flat-square)](https://wordpress.org/plugins/regenerate-thumbnails/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/regenerate-thumbnails.svg?style=flat-square)](https://wordpress.org/plugins/regenerate-thumbnails/advanced/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/regenerate-thumbnails.svg?style=flat-square)](https://wordpress.org/support/plugin/regenerate-thumbnails/reviews/)

Regenerate Thumbnails is a WordPress plugin that will regenerate all thumbnail sizes for one or more images that have been uploaded to your WordPress Media Library.

This is useful for situations such as:

* A new thumbnail size has been added and you want past uploads to have a thumbnail in that size.
* You've changed the dimensions of an existing thumbnail size, for example via Settings → Media.
* You've switched to a new WordPress theme that uses featured images of a different size.

It also offers the ability to delete old, unused thumbnails as well as update the content of posts to use the new thumbnail sizes.

## Alternatives

### WP-CLI

If you have command line access to your server, I highly recommend using [WP-CLI](https://wp-cli.org/) instead of this plugin as it's faster (no HTTP requests overhead) and can be run inside of a `screen` for those with many thumbnails. For details, see the documentation of its [`media regenerate` command](https://developer.wordpress.org/cli/commands/media/regenerate/).

### Jetpack's Site Accelerator Module

[Jetpack](https://jetpack.com/) is a plugin by Automattic, makers of WordPress.com. It gives your self-hosted WordPress site some of the functionality that is available to WordPress.com-hosted sites.

[The Site Accelerator Module](https://jetpack.com/support/site-accelerator/) makes the images on your site be served from WordPress.com's global content delivery network (CDN) which should speed up the loading of images. Importantly though it can create thumbnails on the fly which means you'll never need to use this plugin.

## Building The Plugin

The latest release can be [downloaded from WordPress.org](https://wordpress.org/plugins/regenerate-thumbnails/), but if you wish to build your own copy, here's how:

1. Make sure you have [Node.js](https://nodejs.org/) installed.

2. Clone this repository inside your `plugins` directory:
	```
	$ git clone https://github.com/automattic/regenerate-thumbnails.git
	$ cd regenerate-thumbnails
	```

3. Install [yarn](https://yarnpkg.com/) package manager. It's like npm but better.

4. Install the other dependencies:
	```
	yarn
	```

5. Build the plugin's JavaScript file in production mode:
	```
	yarn build-production
	```

6. Activate the plugin and visit Tools → Regenerate Thumbnails.

### Development Mode

If you're looking to make modifications to this plugin's Vue.js code, run the following command:

```
yarn watch
```

This will do the following things:

* Automatically rebuild the `build.js` file whenever any of the source files change.
* Put Vue.js in development mode which will allow you to use [a browser extension](https://github.com/vuejs/vue-devtools#vue-devtools) to help with debugging.
* Spawn a [Browsersync](https://www.browsersync.io/) server at [http://localhost:3030/](http://localhost:3030/) that will load a proxied version of your development WordPress install that automatically refresh the page in your browser when changes are made to files. Also if you open the site in multiple browsers, it will sync your navigation and scrolling between them. By default, this assumes that your WordPress install lives at `localhost`. If this is not the case (for example you're using [Varying Vagrant Vagrants](https://varyingvagrantvagrants.org/)), then edit `browsersync-config.json`.

Alternatively if you just want to manually build a development copy of the Javascript, then run this command:

```
yarn build
```

## Unit Tests

To run the [PHPUnit](https://phpunit.de/) unit tests, first run the `install-wp-tests.sh` script from the `bin` directory. Then simply run `phpunit` from the plugin's root directory.
