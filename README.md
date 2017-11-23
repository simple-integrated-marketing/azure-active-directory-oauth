# Azure Active Directory OAuth plugin for Craft CMS 3.x

This is an OAuth provider for Azure Active Directory. It is supposed to be used with Social plugin from Dukt

![Screenshot](https://azurecomcdn.azureedge.net/cvt-4ba1ac63410bb2bbe9f1c2a7bedc57894bbe9754309d9d380deedcdf7850047e/images/shared/social/azure-icon-250x250.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require /azure-active-directory-oauth

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Azure Active Directory OAuth.

## Azure Active Directory OAuth Overview

This plugin adds Microsoft Azure Active Directory Provider to the [Social](https://github.com/dukt/social) plugin by Dukt.

## Configuring Azure Active Directory OAuth

You will need to config the provider the same way you do with some native providers in Social Plugin.

`config/social.php`

```php
<?php

return [
    'allowEmailMatch' => false,
    'lockDomains' => [],
    'loginProviders' => [
        "azureactivedirectory" => [
            "clientId" => "APPLICATION ID",
            "clientSecret" => "PRIVATE KEY",
            'userMapping' => [
                'email'=>"{{ mail }}",
                'username'=>"{{ mail }}",
                'firstName'=> "{{ givenName }}"
            ],
        ]
    ]
];
```

## Azure Active Directory OAuth Roadmap

Some things to do, and ideas for potential features:

* Release it

Brought to you by [Jinzhe Li] from Simple Integrated Marketing(https://simple.com.au)
