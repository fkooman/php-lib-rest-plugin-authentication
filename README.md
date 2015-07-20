[![Build Status](https://travis-ci.org/fkooman/php-lib-rest-plugin-authentication.svg)](https://travis-ci.org/fkooman/php-lib-rest-plugin-authentication)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fkooman/php-lib-rest-plugin-authentication/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fkooman/php-lib-rest-plugin-authentication/?branch=master)

# Introduction
This is an authentication plugin for `fkooman/rest`.

# Features
This plugin supports various authentication backends:

* Basic (`fkooman/rest-plugin-basic`)
* Bearer (`fkooman/rest-plugin-bearer`)
* Tls (`fkooman/rest-plugin-tls`)
* Mellon (`fkooman/rest-plugin-mellon`)
* IndieAuth (`fkooman/rest-plugin-indieauth`)

Furthermore it allows you the ability to allow for multiple authentication 
methods supported on one endpoint, e.g. support both `Basic` and `Bearer` 
authentication on one endpoint:

    $userAuth = new BasicAuthentication(...);
    $clientAuth = new BasicAuthentication(...);

    $authenticationPlugin->register($userAuth, 'user');
    $authenticationPlugin->register($clientAuth, 'client');

    ...

    $this->get(
        '/',
        function() {
            return 'Hello World!';
        },
        array(
            'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
                'or' => array('user', 'client')
            )
        )
    );

It also allows you to register multiple authentication backends of the same
type with different configurations for different endpoints. For example to 
allow Basic authentication on two endpoints, but with different user and 
password databases:

    $userAuth = new BasicAuthentication(...);
    $clientAuth = new BasicAuthentication(...);

    $authenticationPlugin->register($userAuth, 'user');
    $authenticationPlugin->register($clientAuth, 'client');

    ...

    $this->get(
        '/',
        function() {
            return 'Hello World!';
        },
        array(
            'fkooman\Rest\Plugin\Authentication\AuthenticationPlugin' => array(
                'only' => array('user')
            )
        )
    );

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
