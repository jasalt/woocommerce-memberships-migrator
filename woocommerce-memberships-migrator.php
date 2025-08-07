<?php
/*
Plugin Name: WooCommerce Memberships Migrator
Description: Migrate customer accounts and memberships from old site via MySQL connection to new site.
Version: 0.1
Author: Jarkko Saltiola
Author URI: https://codeberg.org/jasalt
*/

use Phel\Phel;

$projectRootDir = __DIR__ . '/';
require $projectRootDir . 'vendor/autoload.php';

if (isset($PHP_SELF) && $PHP_SELF !== "./vendor/bin/phel"){
	// Initialize Phel environment in regular WP plugin context. This can be
	// nalso narrowed to only specific routes or conditions to avoid it's
	// runtime overhead where it's not needed.

	// TODO enable if using as plugin
	// Phel::run($projectRootDir, 'phel-wp-plugin\main');

} else {
	// Avoid re-initializing Phel environment during REPL session when requiring
	// wp-load.php which initializes all plugins.
	print("Running REPL, skip running plugin Phel::run \n");
}
