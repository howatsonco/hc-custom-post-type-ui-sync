<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              howatsonco.com.au
 * @since             1.0.0
 * @package           custom-post-type-ui-sync
 *
 * @wordpress-plugin
 * Plugin Name:       H+C Custom Post Type UI Sync
 * Plugin URI:        https://github.com/howatsonco/custom-post-type-ui-sync
 * Description:       H+C Custom Post Type UI Sync
 * Version:           1.0.0
 * Author:            Howatson + Co
 * Author URI:        howatsonco.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-post-type-ui-sync
 */

use HC\CustomPostTypeUiSync\Sync;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

// Define HCCPTUIS_PLUGIN_FILE.
if (!defined('HCCPTUIS_PLUGIN_FILE')) {
  define('HCCPTUIS_PLUGIN_FILE', __FILE__);
}

require dirname(__FILE__) . '/vendor/autoload.php';

/**
 * Returns the main instance of HCCPTUIS to prevent the need to use globals.
 *
 * @return CustomPostTypeUiSync
 */
function HCCPTUIS()
{
  return Sync::instance();
}

// Global for backwards compatibility.
$GLOBALS['hccptuis'] = HCCPTUIS();
