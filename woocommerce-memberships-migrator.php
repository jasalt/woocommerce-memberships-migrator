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

Phel::run($projectRootDir, 'phel-wp-plugin\main');
