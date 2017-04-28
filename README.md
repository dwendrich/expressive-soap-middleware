# expressive-soap-middleware
Middleware for handling soap webservice requests based on zend expressive 2.0.

[![Build Status](https://travis-ci.org/dwendrich/expressive-soap-middleware.svg?branch=master)](https://travis-ci.org/dwendrich/expressive-soap-middleware)
[![Coverage Status](https://img.shields.io/codecov/c/github/dwendrich/expressive-soap-middleware.svg?style=flat)](https://codecov.io/gh/dwendrich/expressive-soap-middleware)
[![Latest Stable Version](http://img.shields.io/packagist/v/dwendrich/expressive-soap-middleware.svg?style=flat)](https://packagist.org/packages/dwendrich/expressive-soap-middleware)

## Requirements
* PHP 7.0 or above
* [zend expressive 2.0](https://docs.zendframework.com/zend-expressive/)

## Installation
Install the latest version with composer. For information on how to get composer or how to use it, please refer to [getcomposer.org](http://getcomposer.org).
```sh
$ composer require dwendrich/expressive-soap-middleware
```

## Basic usage
Webservice methods are provided by a class implementing public methods. Once you have your service class in place,
set up a route which delegates http requests to your service using `GET` or `POST` request methods to the
SoapController middleware:
```php
$app->route(
    '/path-to-service',
    'MyServiceClass\SoapController',
    ['GET', 'POST'],
    'my_service'
);
```

Provide soap controller configuration, to tell where to find the service class containing the webservice methods:
```php
return [
    'soap_controller' => [
       'MyServiceClass\SoapController' => [
           // provide the fully qualified class name which implements the service methods
           'class' => MyServiceClass::class,

           // provide optional soap server options
           'server_options' => [],
       ],
   ],
];
```

Http requests to `/path-to-service` using `GET` request method will now deliver a wsdl document describing your soap
service based on the public methods implemented in `MyServiceClass`.

Calls to your soap service are handled via http request method `POST`.

## Documentation
- [Soap controller component](doc/01-soap-controller.md)
- [Soap description component](doc/02-soap-description.md)