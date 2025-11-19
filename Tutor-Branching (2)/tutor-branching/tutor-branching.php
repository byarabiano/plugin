<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://byarabiano.com
 * @since             1.0.0
 * @package           Tutor_Branching
 *
 * @wordpress-plugin
 * Plugin Name:       Tutor Branching
 * Plugin URI:        https://byarabiano.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Byarabian
 * Author URI:        https://byarabiano.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tutor-branching
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TUTOR_BRANCHING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tutor-branching-activator.php
 */
function activate_tutor_branching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tutor-branching-activator.php';
	Tutor_Branching_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tutor-branching-deactivator.php
 */
function deactivate_tutor_branching() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tutor-branching-deactivator.php';
	Tutor_Branching_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tutor_branching' );
register_deactivation_hook( __FILE__, 'deactivate_tutor_branching' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tutor-branching.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tutor_branching() {

	$plugin = new Tutor_Branching();
	$plugin->run();

}
run_tutor_branching();
