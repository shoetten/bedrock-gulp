# A WORDPRESS BEDROCK / GULP STARTER KIT

This repo combines the wordpress boilerplate [Bedrock](https://github.com/roots/bedrock), with the gulp integrations from [wordpress-gulp-starter-kit](https://github.com/synapticism/wordpress-gulp-starter-kit).

I use this as a starting point for my wordpress projects.

## How to use

### Getting started

Make sure you have [composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx), npm (comes with [node](https://nodejs.org)) and [gulp](http://gulpjs.com) installed.

Then credit a new project by running:

    composer create-project shoetten/bedrock-gulp <path>
    cd <path>

If everything worked out alright, you can fire up `gulp` and start developing your theme, located at `/src/`.

### Install a wordpress plugin

Search through wordpress plugins on [WordPress Packagist](https://wpackagist.org) and install & activate it by running

    composer require wpackagist-plugin/<plugin-name>
    wp plugin activate <plugin-name>

## CREDITS

All credit goes to the authors of the original libraries - [Bedrock](https://github.com/roots/bedrock) and the [wordpress-gulp-starter-kit](https://github.com/synapticism/wordpress-gulp-starter-kit).

## LICENSE

Licensed under the [GPL 3.0](http://www.gnu.org/licenses/gpl.txt).
