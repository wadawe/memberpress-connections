<?php
/**
 * The MemberpressConnections bootstrap file
 * 
 * /memberpress-connections/memberpress-connections.php
 *
 * @wordpress-plugin
 * Plugin Name: Memberpress Connections
 * Description: Link accounts to Memberpress members on a WordPress website using OAuth Login
 * Version: 1.0.0
 * Author: wadawe
 * Author URI: https://github.com/wadawe
 * License: GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * 
 * Copyright (C) 2020 wadawe
 */

// Only allow Wordpress to load plugin
if ( ! defined( 'WPINC' ) ) die( "You are not allowed to call this page directly." );

// Define plugin constants
define( "MPCONNS_NAME", "MemberpressConnections" );
define( "MPCONNS_SLUG", "memberpress-connections" );
define( "MPCONNS_VERSION", "1.0.0" );
define( "MPCONNS_AUTHOR", "wadawe" );
define( "MPCONNS_AUTHOR_URI", "https://github.com/wadawe" );
define( "MPCONNS_ROOT_URL", plugins_url( "/" . MPCONNS_SLUG ) );

// Load relevant classes and files
require_once ABSPATH . "wp-admin/classes/plugin.php";
require plugin_dir_path( __FILE__ ) . "classes/auth.php";
require plugin_dir_path( __FILE__ ) . "classes/hooks.php";
require plugin_dir_path( __FILE__ ) . "classes/options.php";
require plugin_dir_path( __FILE__ ) . "classes/components.php";

/**
 * Handle Wordpress activation hook
 * @return null
 */
function activate_mpConns() {

    // Ensure user has permission to activate plugin
    if ( ! current_user_can( "activate_plugins" ) ) wp_die( "You do not have permission to active plugins." );
    if ( ! is_plugin_active( "memberpress/memberpress.php" ) ) wp_die( "Sorry, this plugin requires Memberpress to function." );

    // Register plugin options with Wordpress
    MPConns_Options::registerOptions();

}
register_activation_hook( __FILE__, "activate_mpConns" );

/**
 * Handle Wordpress deactivation hook
 * @return null
 */
function deactivate_mpConns() {}
register_deactivation_hook( __FILE__, "deactivate_mpConns" );

/**
 * Begin plugin execution
 * @return null
 */
function mpConns_run() {

    // Ensure Memberpress is active
    if ( ! is_plugin_active( "memberpress/memberpress.php" ) ) return;

    // Initialise plugin hooks
    MPConns_Hooks::initialise();

}

// Run the plugin!
mpConns_run();
