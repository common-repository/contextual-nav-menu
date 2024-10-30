<?php 
/* 
Plugin Name: Contextual Nav Menu
Plugin URI: http://www.juzed.fr/
Description: Add some Contextual Menu Features 
Version: 1.2.1
Author: Julien Zerbib
Author URI: http://www.juzed.fr/
  
  
	Copyright 2013  Julien Zerbib  ( email : contact@juzed.fr )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Contextual_Nav_Menu {

	/**
	 * Construct the plugin object
	 */
	public function __construct() {

		// Init plugin
		require_once( dirname( __FILE__ ) . '/inc/init.php' );

	} // END public function __construct
	
	/**
	 * Activate the plugin
	 */
	public static function activate() {

		// Do nothing

	} // END public static function activate

	/**
	 * Deactivate the plugin
	 */        
	public static function deactivate() {

		// Do nothing
 
	} // END public static function deactivate

} // END class Contextual_Nav_Menu

// Installation and uninstallation hooks
register_activation_hook( __FILE__, array( 'Contextual_Nav_Menu', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Contextual_Nav_Menu', 'deactivate' ) );

// instantiate the plugin class
$Contextual_Nav_Menu_Plugin = new Contextual_Nav_Menu();