# srigi/webloader-require-filter

This is a plugin for [janmarek/webloader](https://github.com/janmarek/webloader) which adds availability to interpret sprockets directives in you Javascript and Coffeescript files. It is based on [rafacgarciaa/php-lilo](https://github.com/rafacgarciaa/php-lilo).

Lilo is a fast engine that allow you scan a file to extract a dependency graph using a subset of Sprockets directives. Following directives are supported:

```js
//= require
//= require_directory
//= require_tree
```

For more information about them please visit [Sprockets](https://github.com/rails/sprockets).

## Usage (javascript)

In your javascript files, write Sprockets-style comments to indicate dependencies, e.g.

```js
//= require ../bower_components/jquery/dist/jquery.js
```

If you want to bring in a whole folder of files, use

```js
//= require_tree libs
```

Please note, that sprocket directives must be at the top of the javascript file!

```js
//= require ../bower_components/jquery/dist/jquery.js

;(function($) {
  // your code

}(jQuery))
```

## Installation (Webloader)

Install this package via Composer:

    composer require srigi/webloader-require-filter

Configure Webloader to use filter:

    services:
        requireFilter: Srigi\Webloader\Filters\RequireFilter

    extensions:
        webloader:
            js:
                default:
                    sourceDir: %wwwDir%/../assets/scripts
                    joinFiles: not(%debugMode%)
                    fileFilters:
                        - @requireFilter
                    watchFiles:
                        - {files: ["*.js"], from: %wwwDir%/../assets/scripts}

By default **webloader-require-filter** works with Javascript files. You can also process Coffeescript files, just configure service:

    services:
        requireFilter: Srigi\Webloader\Filters\RequireFilter(['js', 'coffee'])

Currently **webloader-require-filter** seach dependencies in `sourceDir` of the processed file. Don't forget to setup `sourceDir` in your webloader configuration!

## Credits
- [Lilo](https://github.com/rafacgarciaa/php-lilo), a file concatenation tool for PHP inspired by Sprockets and based in Snockets
- [Sprockets](https://github.com/rails/sprockets), Rack-based asset packaging system
