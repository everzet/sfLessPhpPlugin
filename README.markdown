sfLessPhpPlugin
====

*LESS in symfony.*

sfLessPhpPlugin is a plugin for symfony applications. It automatically parses your applications `.less` files through LESS and outputs CSS files.

In details, sfLessPhpPlugin does the following:

* Recursively looks for LESS (`.less`) files in `data/stylesheets`
* Ignores partials (prefixed with underscore: `_partial.less`) - these can be included with `@import` in your LESS files
* Saves the resulting CSS files to `web/css` using the same directory structure as `data/stylesheets`

LESS
----

LESS extends CSS with: variables, mixins, operations and nested rules. For more information, see [http://lesscss.org](http://lesscss.org).

Installation
============

Using git clone
-----------------------------------

Use this to install as a plugin in a symfony app:

	$ cd plugins && git clone git://github.com/everzet/sfLessPhpPlugin.git

Using git submodules
-----------------------------------

Use this if you prefer to use git submodules for plugins:

	$ git submodule add git://github.com/everzet/sfLessPhpPlugin.git plugins/sfLessPhpPlugin


Usage
=====

After installation, you need to create directory `data/stylesheets`. Any LESS file placed in this directory, including subdirectories, will
automatically be parsed through LESS and saved as a corresponding CSS file in `web/css`. Example:

	data/stylesheets/clients/screen.less => web/css/clients/screen.css
	
If you prefix a file with an underscore, it is considered to be a partial, and will not be parsed unless included in another file. Example:

	<file: data/stylesheets/clients/partials/_form.less>
	@text_dark: #222;
	
	<file: data/stylesheets/clients/screen.less>
	@import "partials/_form";
	
	input { color: @text_dark; }

The example above will result in a single CSS file in `web/css/clients/screen.css`.


Configuration
=============

To set the source path (the location of your LESS files), add in apps/APP/config/app.yml:

	app_sf_less_php_plugin:
	  path: "/path/to/less/files"

sfLessPhpPlugin rechecks data/stylesheets/*.less at every routes init. To prevent this, add this in your apps/APP/config/app.yml:

  prod:
    app_sf_less_php_plugin:
  	  compile:  false

Tasks
=====

sfLessPhpPlugin provides a set of CLI tasks to help manage your CSS files.

To parse all LESS files and save the resulting CSS files to the destination path, run:

	$ symfony less:compile

To delete all CSS files before parsing LESS, run:

	$ symfony less:compile --with-clean=true


Git
===

If you are using git to version control your code and LESS for all your stylesheets, you can add this entry to your `.gitignore` file:

	web/css/*.css


Contributors
============
* everzet ([http://github.com/everzet](http://github.com/everzet))

sfLessPhpPlugin is based on lessphp by leafo ([http://github.com/leafo](http://github.com/leafo))
LESS is maintained by Alexis Sellier [http://github.com/cloudhead](http://github.com/cloudhead)