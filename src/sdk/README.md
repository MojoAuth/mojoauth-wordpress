# PHP-SDK
PHP SDK based on MojoAuth APIs.

![Home Image](https://mojoauth-cdn.netlify.app/assets/images/coming-soon.png)


## Introduction

MojoAuth PHP is a wrapper which provides access to MojoAuth Platform APIs.

MojoAuth provides a secure and delightful experience to your customer with passwordless.
Here, you'll find comprehensive guides and documentation to help you to start working with MojoAuth APIs.

Please visit [here](http://www.mojoauth.com/) for more information.

# Quickstart Guide

## Configuration
After successful install, you need to define the following MojoAuth Account info in your project anywhere before using the MojoAuth SDK or in the config file of your project:

```
<?php
require_once(__DIR__."mojoAuthAPI.php");
$client = new mojoAuthAPI("MOJOAUTH_APIKEY");// mojoauth apikey replace at "MOJOAUTH_APIKEY"
```                

## Documentation

[Getting Started](https://mojoauth.com/docs/) - Everything you need to begin using this SDK.