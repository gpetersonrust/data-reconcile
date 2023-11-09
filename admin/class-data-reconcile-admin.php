<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.facebook.com/profile.php?id=100085916559494
 * @since      1.0.0
 *
 * @package    Data_Reconcile
 * @subpackage Data_Reconcile/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Data_Reconcile
 * @subpackage Data_Reconcile/admin
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Data_Reconcile_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Data_Reconcile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Data_Reconcile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
         $dynamic_hash  =  data_reconcile_dynamic_hash();
		  
		 $request_uri = $_SERVER['REQUEST_URI'];

		  $is_data_retrival_page = strpos($request_uri, 'data-retrieval-settings');
		//   data-retrieval-notifications
		$is_data_notifications = strpos($request_uri, 'data-retrieval-notifications');
		  

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/data-reconcile-admin.css', array(), $this->version, 'all' );
         if($is_data_retrival_page || $is_data_notifications) {
	      $data_retrival_css = DATA_RECONCILE_URL . '/dist/data-retrieve/data-retrieve' . $dynamic_hash . '.css';
           wp_enqueue_style( 'data-retrival', $data_retrival_css, array(), $this->version, 'all' );
		 }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Data_Reconcile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Data_Reconcile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/data-reconcile-admin.js', array( 'jquery' ), $this->version, false );

	}

}

/**
 * Get the dynamic hash generated for assets.
 *
 * This function retrieves the dynamic hash generated for assets by following these steps:
 * 1. Read the 'dist/app' directory and get the first file.
 * 2. Extract the hash from the file name by splitting it with '-wp'.
 * 3. Further extract the hash by splitting it with '.' to remove the file extension.
 * 4. Combine the hash with the '-wp' prefix and return the final dynamic hash.
 *
 * @since    1.0.0
 *
 * @return   string   The dynamic hash for assets.
 */
function data_reconcile_dynamic_hash() {
	// Get the path to the 'dist/app' directory.
	$directory_path = plugin_dir_path( dirname( __FILE__, 1 ) ) . 'dist/app/';

	// Get the files in the 'dist/app' directory.
	$files = scandir( $directory_path );

	// Find the first file in the directory.
	$first_file = '';
	foreach ( $files as $file ) {
		if ( ! is_dir( $directory_path . $file ) ) {
			$first_file = $file;
			break;
		}
	}

	// Extract the hash from the file name.
	$hash_parts = explode( '-wp', $first_file );
	$hash = isset( $hash_parts[1] ) ? $hash_parts[1] : '';

	// Further extract the hash by splitting it with '.' to remove the file extension.
	$hash_parts = explode( '.', $hash );
	$hash = isset( $hash_parts[0] ) ? $hash_parts[0] : '';

	// Combine the hash with the '-wp' prefix and return the final dynamic hash.
	$dynamic_hash = '-wp' . $hash;

	return $dynamic_hash;
}