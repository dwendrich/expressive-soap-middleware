# Soap controller component
The soap controller component handles http requests to interact with your webservice based on the soap protocol.

It responds to either http `GET` or `POST` request methods. Other request methods will be ignored by the soap controller
component and are delegated to the next callable middleware, if available.

In case an http request with method `GET` is received, the controller creates and delivers a wsdl documentation of your
webservice in xml format.

The wsdl will be created by `zendframwork/zend-soap-autodiscover` component. It is therefore necessary, that your
service class provides documentation in phpdoc-format for all service methods that should be available.

To properly set up the controller for your webservice, you need to provide configuration below the `soap_controller`
configuration key, for example in the `ConfigProvider` class as part of your webservice package of your expressive
application:
```php
use MyApp\MyServiceClass;
 
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
    
            // configure soap controller
            'soap_controller' => [
                'MyServiceClass\SoapController' => [
                    // provide the fully qualified class name which implements the service methods
                    'class' => MyServiceClass::class,
    
                    // provide optional soap server options
                    'server_options' => [],
                ],
            ],
        ];
    }
}
```

You can now register a route to the application which delegates calls to the configured soap controller component,
for example:
```php
$app->route(
    '/path-to-service',
    'MyServiceClass\SoapController',
    ['GET', 'POST'],
    'my_service'
);
```
