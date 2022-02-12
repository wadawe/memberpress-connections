<?php

/**
 * The MemberpressConnections options class
 * 
 * /memberpress-connections/classes/options.php
 * 
 * Copyright (C) 2020 wadawe
 */
class MPConns_Options {
    
    /**
     * Define options structure
     * @return null
     */
    public static $structure = array(
        "discord" => array(
            "enabled" =>                array( "editable" => true,     "default" => false,             "type" => "Checkbox" ),
            "client_id" =>              array( "editable" => true,     "default" => "",                "type" => "Textbox" ),
            "client_secret" =>          array( "editable" => true,     "default" => "",                "type" => "Textbox" ),
            "client_token" =>           array( "editable" => true,     "default" => "",                "type" => "Textbox" ),
            "memberpress_field" =>      array( "editable" => true,     "default" => "",                "type" => "FieldSelection" ),
            "button_color" =>           array( "editable" => false,    "default" => "7289DA" ),
            "oauth_login_link" =>       array( "editable" => false,    "default" => "https://discordapp.com/api/oauth2/authorize" ),
            "oauth_login_scope" =>      array( "editable" => false,    "default" => "identify" ),
            "oauth_token_link" =>       array( "editable" => false,    "default" => "https://discordapp.com/api/oauth2/token" ),
            "oauth_account_link" =>     array( "editable" => false,    "default" => "https://discordapp.com/api/users/@me" ),
            "oauth_account_field" =>    array( "editable" => false,    "default" => "id" )
        )
    );

    /**
     * Register all plugin options
     * @return null
     */
    public static function registerOptions() {

        // Iterate options group structures
        $pluginOptionsStructure = self::$structure;
        foreach ( $pluginOptionsStructure as $connectionKey => $connectionStructure ) {
            
            // Create array of default options values
            $connectionOptions = array();
            foreach ( $pluginOptionsStructure[ $connectionKey ] as $optionKey => $optionStructure ) {
                $connectionOptions[ $optionKey ] = $optionStructure[ "default" ];
            }
            
            // Register options group with wordpress
            add_option( "mpconns_" . $connectionKey, $connectionOptions );

        }

    }

    /**
     * Update a plugin options group
     * @return null
     */
    public static function update() {

        // Verify options group is valid
        $pluginOptionsStructure = self::$structure;
        $connectionKey = ( isset( $_REQUEST[ "group" ] ) ? $_REQUEST[ "group" ] : "" );
        if ( ! array_key_exists( $connectionKey, $pluginOptionsStructure ) ) return "<div class='error below-h2'><ul><li><strong>Invalid options group.</strong></li></ul></div>";

        // Iterate option keys
        $connectionOptions = array();
        foreach ( $pluginOptionsStructure[ $connectionKey ] as $optionKey => $optionStructure ) {

            // Use default value to start with
            $connectionOptions[ $optionKey ] = $optionStructure[ "default" ];

            // Check if option is editable and present in post values
            if ( isset( $_REQUEST[ $optionKey ], $optionStructure[ "editable" ] ) && $optionStructure[ "editable" ] ) {

                // Verify that post value is valid
                if ( ! self::validate( $optionKey, $_REQUEST[ $optionKey ] ) ) {
                    $validationError = "<div class='error below-h2'><ul><li><strong>Value for '" . $optionKey . "' is invalid.</strong></li></ul></div>";
                    break;
                }

                // Update array for updated options values
                $connectionOptions[ $optionKey ] = self::getValue( $optionKey, $_REQUEST[ $optionKey ] );

            }

        }

        // Verify no error occurred in above loop
        if ( isset( $validationError ) ) return $validationError;

        // Update options group
        update_option( "mpconns_" . $connectionKey, $connectionOptions );
        return "<div class='updated notice notice-success below-h2'><ul><li><strong>Update successful.</strong></li></ul></div>";

    }

    /**
     * Validate an option value
     * @param string $optionKey The option key to validate
     * @param string $optionValue The option value to validate
     * @return boolean Whether the option value is valid for the corresponding option key
     */
    private static function validate( $optionKey, $optionValue ) {
        switch( $optionKey ) {

            case "enabled":
                return true; 

            case "client_id":
                if ( ! isset( $optionValue ) ) return false;
                if ( $optionValue == "" ) return true;
                if ( ! preg_match( "/^[\d]{16,32}$/", $optionValue ) ) return false;
                return true;

            case "client_secret":
                if ( ! isset( $optionValue ) ) return false;
                if ( $optionValue == "" ) return true;
                if ( ! preg_match( "/^[\w]{16,32}$/", $optionValue ) ) return false;
                return true;

            case "client_token":
                if ( ! isset( $optionValue ) ) return false;
                if ( $optionValue == "" ) return true;
                if ( ! preg_match( "/^[\w\.\-]{32,64}$/", $optionValue ) ) return false;
                return true;

            case "memberpress_field":
                if ( ! isset( $optionValue ) ) return false;
                if ( $optionValue == "" ) return true;
                if ( ! preg_match( "/^[\w]{0,32}$/", $optionValue ) ) return false;
                foreach ( MeprOptions::fetch()->custom_fields as $customField ) {
                    if ( $customField->field_key == $optionValue ) return true;
                }
                return false;

            default:
                return false;

        }
    }

    /**
     * Retrieve an option value from a variable
     * @param string $optionKey The option key to retrieve
     * @param string $optionValue The option value to retrieve from
     * @return string The retrieved option value for the corresponding option key
     */
    private static function getValue( $optionKey, $optionValue ) {
        switch( $optionKey ) {

            case "enabled":
                if ( ! isset( $optionValue ) ) return false;
                if ( $optionValue == "on" ) return true;
                return false;

            default:
                return $optionValue;

        }
    }

}
