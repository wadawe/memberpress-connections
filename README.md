# About

**Link accounts to Memberpress members on a WordPress website using OAuth Login.**

This repository houses the content of a custom Wordpress Plugin. This plugin implements Memberpress Connections for:

-   Discord
-   More to come...?

# License

**This repository and its contents is licensed under the GPL v3 or later. A copy of the license is included in the root directory of the repository. The file is named LICENSE.**

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 3, as published by the Free Software Foundation. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

# Project

**Created for:**

-   [DFS Army](https://dfsarmy.com/)
-   [Sports Bet Army](https://sportsbetarmy.com/)

# Links

-   [Contribution Guidelines](/CONTRIBUTING.md)

# Usage

You will need an understanding of how to manage a Wordpress website to use this repository; along with an understanding of setting up OAuth Credentials for different OAuth Service providers.

## Requirements

This project was developed and tested with:

-   **Wordpress** `^5.5.3`
-   **Memberpress** `^1.9.0`

## Setup

Create a compressed `memberpress-connections.zip` from the `memberpress-connections` folder.

> `$ zip -r memberpress-connections memberpress-connections.zip`

## Upload

Upload the created `memberpress-connections.zip` as a new plugin using the Wordpress `Plugins` management section.

## Activate

Activate the plugin from the `Installed Plugins` page under the `Plugins` management section.

## OAuth

Create OAuth Credentials for your chosen OAuth service provider.

## Custom Fields

Create a `Custom User Information Field` under the `Memberpress` management section.

-   Set `Show at Signup` to Disabled.
-   Set `Show in Account` to Disabled.
-   Set `Required` to Disabled.

## Configure

Navigate to the `Connections` page under the `Memberpress` management section. Fill in all relevant fields for your chosen Memberpress Connection.

## Complete

Navigate to the `Account` page of your WordPress website. You should see a new `Connections` tab along the `Account` page menu. Your members can use this page to link their Memberpress Account to their other OAuth enabled services!
