# MODX Elements API

This extra enables you to use the VSCode extension MODX: Elements.

It's a simple REST API to allow creating and editing elements on your MODX site through VSCode without using FTP.

The REST API uses a plugin to open an endpoint which MODX: Elements can communicate with.

## Installation
Simply download through Package Management, and install.

### Setup options
`api_token` Specify an API Token to use when you add the site to MODX Elements in VSCode.

`api_url` Define URL for the endpoint. Default is `modx-elements/` (https://www.example.com/modx-elements/)

## What's in this extra?

### Plugin
#### `VSCode Modx Elements`
A plugin for event `OnMODXInit` and checks if the first part of the request URL is `api_url` (default: `modx-elements/`) and redirects the query to the API.

### System Settings
#### `api_token`
Set a unique token for authentication with VSCode. You can use a Token Generator like [it-tools.tech/token-generator](https://it-tools.tech/token-generator).
#### `api_url`
The URL endpoint for the API. Set it to whatever you want, but do the same for the sites configuration in VSCode.
#### `debug_log`
Enable this to log API responses to MODX Error log.

### Core
#### `core/components/vscodemodxelements/src/Controller/Api.php`
This file handles all API requests.

