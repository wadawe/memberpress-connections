<?php

/**
 * The MemberpressConnections auth class
 * 
 * /memberpress-connections/classes/auth.php
 * 
 * Copyright (C) 2020 wadawe
 */
class MPConns_Auth {

    /**
     * Start OAuth flow for a connection
     * @param object $user The Memberpress user executing the auth flow
     * @return string The HTML result from the auth flow
     */
    public static function flow( $user ) {

        // Verify that session state is set & matches
        if ( ! isset( $_SESSION[ "mpconns_link_state" ] ) ) return "";
        if ( ! isset( $_REQUEST[ "state" ] ) ) return "";
        if ( $_SESSION[ "mpconns_link_state" ] != $_REQUEST[ "state" ] ) return "";
        unset( $_SESSION[ "mpconns_link_state" ] );

        // Verify error passed from oauth
        if ( isset( $_REQUEST[ "error" ] ) ) return "<span style='color: red;'>Connection aborted.</span>";

        // Verify auth code from oauth
        if ( ! isset( $_REQUEST[ "code" ] ) > 0 ) return "<span style='color: red;'>Missing auth code.</span>";

        // Load plugin options from Wordpress
        // Defined group *should* already be verified
        $connectionOptions = get_option( "mpconns_" . $_REQUEST[ "group" ] );
        if ( $connectionOptions[ "enabled" ] != true ) return "";

        // Validate defined connection custom field
        $customFieldValues = get_user_meta( $user->ID );
        foreach ( MeprOptions::fetch()->custom_fields as $customField ) {
            if ( $customField->field_key == $connectionOptions[ "memberpress_field" ] ) {
                $connectionFieldName = $customField->field_name;
                break;
            }
        }
        if ( ! isset( $connectionFieldName ) ) return "<span style='color: red;'>Field option error.</span>";
        $connectionFieldValue = isset( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) && count( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) == 1 ? $customFieldValues[ $connectionOptions[ "memberpress_field" ] ][ 0 ] : "";

        // Get and verify user access token
        $accessToken = self::getAccessToken( $connectionOptions, $_REQUEST[ "code" ] );
        if ( ! isset( $accessToken ) ) return "<span style='color: red;'>Token request error.</span>";

        // Retrieve user field value from oauth
        $newFieldValue = self::getAccountFieldValue( $connectionOptions, $accessToken );
        if ( ! isset( $newFieldValue ) ) return "<span style='color: red;'>Account request error.</span>";
    
        // Verify user field value is different from current value
        if ( $newFieldValue != $connectionFieldValue ) {

            // Update user custom field value
            if ( ! update_user_meta( $user->ID, $connectionOptions[ "memberpress_field" ], sanitize_text_field( $newFieldValue ) ) ) return "<span style='color: red;'>Field update error.</span>";

        }

        // Send Memberpress event and succeed
        MeprEvent::record( "member-account-updated", $user );
        return "<span style='color: limegreen;'>Link successful.</span>";

    }

    /**
     * Update a user OAuth connection
     * @param object $user The Memberpress user executing the auth flow
     * @return string The HTML result from the auth flow
     */
    public static function update( $user ) {

        // Verify update is "unlink"
        if ( $_REQUEST[ "update" ] != "unlink" ) return "<span style='color: red;'>Invalid update request.</span>";

        // Load plugin options from Wordpress
        // Defined group *should* already be verified
        $connectionOptions = get_option( "mpconns_" . $_REQUEST[ "group" ] );
        if ( $connectionOptions[ "enabled" ] != true ) return "";

        // Validate defined connection custom field
        $customFieldValues = get_user_meta( $user->ID );
        foreach ( MeprOptions::fetch()->custom_fields as $customField ) {
            if ( $customField->field_key == $connectionOptions[ "memberpress_field" ] ) {
                $connectionFieldName = $customField->field_name;
                break;
            }
        }
        if ( ! isset( $connectionFieldName ) ) return "<span style='color: red;'>Field option error.</span>";
        $connectionFieldValue = isset( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) && count( $customFieldValues[ $connectionOptions[ "memberpress_field" ] ] ) == 1 ? $customFieldValues[ $connectionOptions[ "memberpress_field" ] ][ 0 ] : "";
    
        // Verify user field value is different from current value
        $newFieldValue = "";
        if ( $newFieldValue != $connectionFieldValue ) {

            // Update user custom field value
            if ( ! update_user_meta( $user->ID, $connectionOptions[ "memberpress_field" ], sanitize_text_field( $newFieldValue ) ) ) return "<span style='color: red;'>Field update error.</span>";

        }

        // Succeed
        return "<span style='color: limegreen;'>Update successful.</span>";

    }

    /**
     * Retrieve a users access token
     * @param array $connectionOptions The indexed array options for the given connection
     * @param string $authCode The auth code returned from an auth flow
     * @return string The users access token for the connection
     */
    private static function getAccessToken( $connectionOptions, $authCode ) {
        
        // Create curl instance with group options
        $ch = curl_init( $connectionOptions[ "oauth_token_link" ] );
        curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => array( 'Content-type: application/x-www-form-urlencoded' ), 
            CURLOPT_POSTFIELDS => http_build_query( array( 
                "client_id" => $connectionOptions[ "client_id" ],
                "client_secret" => $connectionOptions[ "client_secret" ],
                "grant_type" => "authorization_code",
                "code" => $authCode,
                "redirect_uri" => MeprOptions::fetch()->account_page_url( "action=connections" ) . "&group=" . $_REQUEST[ "group" ]
            ) )
        ) );

        // Execute curl request to connection
        try {
            $tokenResponse = json_decode( curl_exec( $ch ), true );
            curl_close( $ch );
        } catch( Exception $e ) {
            return null;
        }

        // Verify access token and token scope
        if ( ! isset( $tokenResponse[ "access_token" ] ) ) return null;
        if ( ! isset( $tokenResponse[ "scope" ] ) || $tokenResponse[ "scope" ] != $connectionOptions[ "oauth_login_scope" ] ) return null;

        // Return access token
        return $tokenResponse[ "access_token" ];

    }

    /**
     * Retrieve a users account field value
     * @param array $connectionOptions The indexed array options for the given connection
     * @param string $accessToken The access token returned from an auth flow
     * @return string The users account field value for the connection
     */
    private static function getAccountFieldValue( $connectionOptions, $accessToken ) {

        // Create curl instance with group options
        $ch = curl_init( $connectionOptions[ "oauth_account_link" ] );
        curl_setopt_array( $ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_POST => 0,
            CURLOPT_HTTPHEADER => array( 
                'Content-type: application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $accessToken
            ), 
        ) );

        // Execute curl request to connection
        try {
            $accountResponse = json_decode( curl_exec( $ch ), true );
            curl_close( $ch );
        } catch( Exception $e ) {
            return null;
        }

        // Verify user account and field name
        if ( ! isset( $accountResponse[ $connectionOptions[ "oauth_account_field" ] ] ) ) return null;

        // Return accountfield value
        return $accountResponse[ $connectionOptions[ "oauth_account_field" ] ];

    }

}
