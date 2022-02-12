<?php
/**
 * Fired when the plugin is uninstalled
 * 
 * /memberpress-connections/uninstall.php
 * 
 * Copyright (C) 2020 wadawe
 */

// Only allow Wordpress to uninstall plugin
if ( ! defined( "WP_UNINSTALL_PLUGIN" ) ) exit();

// Remove admin page content tab
remove_submenu_page( "memberpress", "memberpress-connections" );

// Delete stored plugin options
require plugin_dir_path( __FILE__ ) . "classes/options.php";
$pluginOptionsStructure = MPConns_Options::$structure;
foreach ( $pluginOptionsStructure as $connectionKey => $connectionStructure ) {
    delete_option( "mpconns_" . $connectionKey );
}
