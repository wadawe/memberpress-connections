<?php

/**
 * The MemberpressConnections hooks class
 * 
 * /memberpress-connections/classes/hooks.php
 * 
 * Copyright (C) 2020 wadawe
 */
class MPConns_Hooks {
    
    /**
     * Initialise plugin hooks
     * @return null
     */
    public static function initialise() {
        add_action( "init", array( __CLASS__, "startSession" ), 1 );
        add_action( "wp_enqueue_scripts", array( __CLASS__, "enqueueStyles" ), 100 );
        add_action( "mepr_menu", array( __CLASS__, "addAdminMenu" ), 1 );
        add_action( "mepr_account_nav", array( __CLASS__, "addConnectionsTab" ), 1 );
        add_action( "mepr_account_nav_content", array( __CLASS__, "addConnectionsTabContent" ), 1 );
    }
    
    /**
     * Start a php session if one does not exist
     * @return null
     */
    public static function startSession() {
        if ( ! session_id() ) session_start();
    }
    
    /**
     * Load plugin stylesheets
     * @return null
     */
    public static function enqueueStyles() {
        wp_enqueue_style( MPCONNS_SLUG, MPCONNS_ROOT_URL . "/css/account.css", array( "mp-account" ), MPCONNS_VERSION );
    }

    /**
     * Add the Connections tab to the memberpress admin options menu
     * @return null
     */
    public static function addAdminMenu() {

        // Verify user has access to view admin page
        if ( ! current_user_can( "manage_options" ) ) return;

        // Create Memberpress submenu entry
        add_submenu_page(
            "memberpress",
            "Connections",
            "Connections",
            "administrator",
            MPCONNS_SLUG,
            array( __CLASS__, "displayAdminPage" )
        );

    }

    /**
     * Display the Connections tab admin options page
     * @return null
     */
    public static function displayAdminPage() {

        // Verify user has access to view admin page
        if ( ! current_user_can( "manage_options" ) ) return;
        
        // Display admin page header
        MPConns_Components::adminPageHeader();

        // Handle options update
        // Display response message
        if ( isset( $_REQUEST[ "action" ] ) && $_REQUEST[ "action" ] == "process-form" ) {
            echo MPConns_Options::update(); 
        }

        // Display admin page content
        MPConns_Components::adminPageContent();

    }

    /**
     * Add the connections tab to the memberpress account page
     * @param object $user The Memberpress user viewing the accounts page
     * @return null
     */
    public static function addConnectionsTab( $user ) {

        // Ensure user is logged in
        if ( ! is_user_logged_in() ) return;

        // Check if connections tab is currently being viewed
        $isTabActive = ( isset( $_REQUEST[ "action" ] ) && $_REQUEST[ "action" ] == "connections" );

        // Display connections tab on account page
        MPConns_Components::connectionsTab( $isTabActive );

    }

    /**
     * Add the connections tab content to the memberpress account page
     * @param string $pageAction The page action from the url
     * @return null
     */
    public static function addConnectionsTabContent( $pageAction ) {

        // Verify connections tab needs to be displayed
        if ( $pageAction != "connections" ) return;

        // Verify user is logged in
        $userLoggedIn = MeprUtils::is_user_logged_in();
        if ( ! $userLoggedIn ) return;
        ?><div class="mp_wrapper"><?php
            $user = MeprUtils::get_currentuserinfo();

            // Get plugin options structure
            $pluginOptionsStructure = MPConns_Options::$structure;

            // Handle redirect from oauth or update button
            if ( isset( $_REQUEST[ "group" ] ) && array_key_exists( $_REQUEST[ "group" ], $pluginOptionsStructure ) ) {
                if ( isset( $_REQUEST[ "update" ] ) ) { 
                    $requestResult = MPConns_Auth::update( $user ); 
                } else { 
                    $requestResult = MPConns_Auth::flow( $user ); 
                }
            }

            // Retrieve custom user fields
            // Done AFTER oauth or update to ensure updated field values are pulled
            $customFieldValues = get_user_meta( $user->ID );

            // Create random session state
            // Do this after handling oauth as state is required for security in oauth flow
            $_SESSION[ "mpconns_link_state" ] = bin2hex( random_bytes( 22 ) );

            // Iterate each connection in plugin options structure
            // Display connection if setup and enabled
            foreach ( $pluginOptionsStructure as $connectionKey => $connectionStructure ) {

                // Load connection options
                // Verify if connection is enabled
                $connectionOptions = get_option( "mpconns_" . $connectionKey );
                if ( $connectionOptions[ "enabled" ] != true ) continue;

                // Validate defined connection custom field
                foreach ( MeprOptions::fetch()->custom_fields as $customField ) {
                    if ( $customField->field_key == $connectionOptions[ "memberpress_field" ] ) {
                        $connectionFieldName = $customField->field_name;
                        break;
                    }
                }
                if ( ! isset( $connectionFieldName ) ) continue;
                $connectionFieldValue = isset( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) && count( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) == 1 ? $customFieldValues[ $connectionOptions[ "memberpress_field" ] ][ 0 ] : "";

                // Display connection on page
                MPConns_Components::connectionTitleAndField( $connectionKey, $connectionFieldName, $connectionFieldValue );
                MPConns_Components::connectionLinkButton( $connectionKey, $connectionOptions );
                if ( $connectionFieldValue != "" ) {
                    MPConns_Components::connectionUnlinkButton( $connectionKey );
                }
                if ( isset( $_REQUEST[ "group" ], $requestResult ) && $_REQUEST[ "group" ] == $connectionKey ) {
                    MPConns_Components::connectionFeedback( $requestResult );
                }

            }

        ?></div><?php

    }

}
