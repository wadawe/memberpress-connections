<?php

/**
 * The MemberpressConnections components class
 * 
 * /memberpress-connections/classes/components.php
 * 
 * Copyright (C) 2020 wadawe
 */
class MPConns_Components {
    
    /**
     * Display the admin options page header
     * @return null
     */
    public static function adminPageHeader() {
        ?>
        <h1>Memberpress Connections</h1>
        <p>v<?php echo MPCONNS_VERSION; ?> by <a target="_blank" href="<?php echo MPCONNS_AUTHOR_URI; ?>"><?php echo MPCONNS_AUTHOR; ?></a></p>
        <div class="mpdt_spacer"></div>
        <?php 
    }

    /**
     * Display the admin options page content
     * @return null
     */
    public static function adminPageContent() {
        $pluginOptionsStructure = MPConns_Options::$structure;
        $defaultOptionsKey = array_key_first( $pluginOptionsStructure );
        ?>
        <h2 id="mepr-reports-column-selector" class="nav-tab-wrapper">
            <?php
            foreach ( $pluginOptionsStructure as $connectionKey => $connectionStructure ) {
                ?><a class="nav-tab <?php echo ( $connectionKey == $defaultOptionsKey ) ? "nav-tab-active" : ""; ?>" id="mpconns-<?php echo $connectionKey; ?>" href="#<?php echo $connectionKey; ?>" onclick="switchTabs( this )"><?php echo ucwords( $connectionKey ); ?></a><?php
            }
            ?>
        </h2>
        <?php 
        foreach ( $pluginOptionsStructure as $connectionKey => $connectionStructure ) {
            ?>
            <form name="mpconns_<?php echo $connectionKey; ?>_form" id="mpconns_<?php echo $connectionKey; ?>_form" class="mepr-form" method="post" action="<?php echo $_SERVER[ "REQUEST_URI" ]; ?>" enctype="multipart/form-data">
                <table class="form-table"><tbody>
                    <input type="hidden" name="action" value="process-form">
                    <input type="hidden" name="group" value="<?php echo $connectionKey; ?>">
                    <?php
                    $connectionOptions = get_option( "mpconns_" . $connectionKey );
                    foreach ( $connectionStructure as $optionKey => $optionStructure ) {
                        if ( $optionStructure[ "editable" ] && isset( $optionStructure[ "type" ] ) ) {
                            self::{ "input" . $optionStructure[ "type" ] }( $optionKey, $connectionOptions[ $optionKey ] );
                        }
                    }
                    ?>
                    <tr valign="top">
                        <th scope="row"><label for="field">Redirect URI:</label></th>
                        <td><p class="regular-text"><?php echo MeprOptions::fetch()->account_page_url( "action=connections" ) . "&group=" . $connectionKey; ?></p></td>
                    </tr>
                </tbody></table>
                <?php submit_button( "Save" ); ?>
            </form>
            <?php
        }
        ?>
        <script>
            function switchTabs( element ) {
                var activeTabs = element.parentElement.getElementsByClassName( "nav-tab-active" );
                var tabPane = document.getElementById( "mpconns_" + element.id.replace( "mpconns-", "" ) + "_form" );
                var activePanes = document.getElementsByClassName( "mepr-form" );
                for ( var tabIndex = 0; tabIndex < activeTabs.length; tabIndex++ ) {
                    activeTabs[ tabIndex ].classList.remove( "nav-tab-active" );
                }
                for ( var tabIndex = 0; tabIndex < activePanes.length; i++ ) {
                    if ( ! activePanes[ tabIndex ].classList.contains( "mepr-hidden" ) ) activePanes[ tabIndex ].classList.add( "mepr-hidden" );
                }
                element.classList.add( "nav-tab-active" );
                tabPane.classList.remove( "mepr-hidden" );
            }
        </script>
        <?php
    }

    /**
     * Display a checkbox input field
     * @param string $optionKey The option key for the displayed input field
     * @param string $optionValue The option value for the displayed input field
     * @return null
     */
    private static function inputCheckbox( $optionKey, $optionValue ) {
        ?>
        <tr valign="top">
            <th scope="row">
                <label for="<?php echo $optionKey; ?>"><?php echo ucwords( str_replace( "_", " ", $optionKey ) ); ?>:</label>
            </th>
            <td>
                <input type="checkbox" name="<?php echo $optionKey; ?>" <?php echo $optionValue == true ? "checked" : ""; ?>/>
            </td>
        </tr>
        <?php
    }

    /**
     * Display a textbox input field
     * @param string $optionKey The option key for the displayed input field
     * @param string $optionValue The option value for the displayed input field
     * @return null
     */
    private static function inputTextbox( $optionKey, $optionValue ) {
        ?>
        <tr valign="top">
            <th scope="row">
                <label for="<?php echo $optionKey; ?>"><?php echo ucwords( str_replace( "_", " ", $optionKey ) ); ?>:</label>
            </th>
            <td>
                <input type="text" class="regular-text" name="<?php echo $optionKey; ?>" value="<?php echo $optionValue; ?>"/>
            </td>
        </tr>
        <?php
    }

    /**
     * Display a field selection input field
     * @param string $optionKey The option key for the displayed input field
     * @param string $optionValue The option value for the displayed input field
     * @return null
     */
    private static function inputFieldSelection( $optionKey, $optionValue ) {
        ?>
        <tr valign="top">
            <th scope="row">
                <label for="<?php echo $optionKey; ?>"><?php echo ucwords( str_replace( "_", " ", $optionKey ) ); ?>:</label>
            </th>
            <td>
                <select name="<?php echo $optionKey; ?>" class="mepr-dropdown">
                    <option value="">-</option>
                    <?php
                    foreach ( MeprOptions::fetch()->custom_fields as $customField ) {
                        ?><option value="<?php echo $customField->field_key; ?>" <?php echo $customField->field_key == $optionValue ? "selected" : ""; ?>><?php echo $customField->field_key; ?></option><?php
                    }
                    ?>
                </select>
            </td>
        </tr>
        <?php
    }

    /**
     * Display the account page connections tab
     * @param boolean $isTabActive Whether the tab should be displayed as active
     * @return null
     */
    public static function connectionsTab( $isTabActive ) {
        ?>
        <span class="mepr-nav-item connections <?php echo $isTabActive ? "mepr-active-nav-tab" : ""; ?>">
            <a href="<?php echo MeprOptions::fetch()->account_page_url( "action=connections" ); ?>">Connections</a>
        </span>
        <?php
    }

    /**
     * Display a connections tab title and value field
     * @param string $connectionKey The group key for the displayed connection field
     * @param string $connectionFieldName The title for the displayed connection field
     * @param string $connectionFieldValue The value for the displayed connection field
     * @return null
     */
    public static function connectionTitleAndField( $connectionKey, $connectionFieldName, $connectionFieldValue ) {
        ?>
        <div class="mp-form-label">
            <label for="mpconns-connect-<?php echo $connectionKey; ?>"><?php echo $connectionFieldName; ?></label>
        </div>
        <input type="text" name="mpconns-connect-<?php echo $connectionKey; ?>" class="mepr-form-input" disabled value="<?php echo $connectionFieldValue; ?>"/>
        <?php
    }

    /**
     * Display a connections tab link button
     * @param string $connectionKey The group key for the displayed connection button
     * @param array $connectionOptions The indexed array options for the given connection
     * @return null
     */
    public static function connectionLinkButton( $connectionKey, $connectionOptions ) {
        ?>
        <form class="mpconns-button" method="get" action="<?php echo $connectionOptions[ "oauth_login_link" ]; ?>">
            <input type="hidden" name="response_type" value="code">
            <input type="hidden" name="client_id" value="<?php echo $connectionOptions[ "client_id" ]; ?>">
            <input type="hidden" name="scope" value="<?php echo $connectionOptions[ "oauth_login_scope" ]; ?>">
            <input type="hidden" name="state" value="<?php echo $_SESSION[ "mpconns_link_state" ]; ?>">
            <input type="hidden" name="redirect_uri" value="<?php echo MeprOptions::fetch()->account_page_url( "action=connections" ); ?>&group=<?php echo $connectionKey; ?>">
            <input style="background-color: #<?php echo $connectionOptions[ "button_color" ]; ?>" class="mepr-submit mpconns-submit-button" type="submit" value="LINK <?php echo strtoupper( $connectionKey ); ?>"/>
        </form>
        <?php
    }

    /**
     * Display a connections tab unlink button
     * @param string $connectionKey The group key for the displayed connection button
     * @return null
     */
    public static function connectionUnlinkButton( $connectionKey ) {
        ?>
        <form class="mpconns-button" method="get" action="<?php echo MeprOptions::fetch()->account_page_url(); ?>">
            <input type="hidden" name="action" value="connections">
            <input type="hidden" name="update" value="unlink">
            <input type="hidden" name="group" value="<?php echo $connectionKey; ?>">
            <input style="background-color: red;" class="mepr-submit" type="submit" value="UNLINK"/>
        </form>
        <?php
    }

    /**
     * Display a connections tab feedback message
     * @param string $feedbackMessage The HTML feedback message to display
     * @return null
     */
    public static function connectionFeedback( $feedbackMessage ) {
        ?>
        <form class="mpconns-feedback">
            <?php echo $feedbackMessage; ?>
        </form>
        <?php
    }

}
