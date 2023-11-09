<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://www.facebook.com/profile.php?id=100085916559494
 * @since      1.0.0
 *
 * @package    Data_Reconcile
 * @subpackage Data_Reconcile/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Data_Reconcile
 * @subpackage Data_Reconcile/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Data_Reconcile_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'data-reconcile',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
