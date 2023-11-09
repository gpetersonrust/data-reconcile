<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://www.facebook.com/profile.php?id=100085916559494
 * @since             1.0.0
 * @package           Data_Reconcile
 *
 * @wordpress-plugin
 * Plugin Name:       Data Reconcile
 * Plugin URI:        https://https://www.facebook.com/profile.php?id=100085916559494
 * Description:       This plugin will be used to reconcile data
 * Version:           1.0.0
 * Author:            Gino Peterson
 * Author URI:        https://https://www.facebook.com/profile.php?id=100085916559494/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       data-reconcile
 * Domain Path:       /languages
 */


// define dir path
 
define('DATA_RECONCILE_DIR_PATH', plugin_dir_path(__FILE__));
define('DATA_RECONCILE_URL', plugins_url('', __FILE__));

 

require DATA_RECONCILE_DIR_PATH . 'loader.php';



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'DATA_RECONCILE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-data-reconcile-activator.php
 */
function activate_data_reconcile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-data-reconcile-activator.php';
	Data_Reconcile_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-data-reconcile-deactivator.php
 */
function deactivate_data_reconcile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-data-reconcile-deactivator.php';
	Data_Reconcile_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_data_reconcile' );
register_deactivation_hook( __FILE__, 'deactivate_data_reconcile' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-data-reconcile.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_data_reconcile() {

	$plugin = new Data_Reconcile();
	$plugin->run();

}
run_data_reconcile();
