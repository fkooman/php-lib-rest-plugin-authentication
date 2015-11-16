[![Build Status](https://travis-ci.org/fkooman/php-lib-rest-plugin-authentication.svg)](https://travis-ci.org/fkooman/php-lib-rest-plugin-authentication)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/php-lib-rest-plugin-authentication/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/php-lib-rest-plugin-authentication/?branch=master)

# Introduction
This is an authentication plugin for `fkooman/rest`.

# Features
This plugin supports various authentication backends:

* Basic (`fkooman/rest-plugin-authentication-basic`)
* Bearer (`fkooman/rest-plugin-authentication-bearer`)
* Tls (`fkooman/rest-plugin-authentication-tls`)
* Mellon (`fkooman/rest-plugin-authentication-mellon`)
* IndieAuth (`fkooman/rest-plugin-authentication-indieauth`)
* Form (`fkooman/rest-plugin-authentication-form`)

# Installation
To install the main plugin:

    $ composer require fkooman/rest-plugin-authentication

To install the additional plugins:

    $ composer require fkooman/rest-plugin-authentication-<name>

# Development
It is quite easy to develop your own plugin. Authentication plugins can for
example also register endpoints in your REST application to e.g. receive 
authorization codes.

Check the code of the existing plugins to get inspiration :-)
