# Soap description component
The soap description component provides a description of your soap webservice in html format.

The description relies on a complete documentation in phpdoc-format for all your webservice methods
implemented as public class methods of your service class.

To enable the description of your webservice, you need to provide configuration below the `soap_description`
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
    
            // configure soap description
            'soap_description' => [
                'MyServiceClass\SoapDescription' => [
                    // provide the fully qualified class name which implements the service methods
                    'class' => MyServiceClass::class,

                    // provide optional soap client options
                    'client_options' => [],

                    // route key, to determine the webservice endpoint
                    'service_route' => 'my_service',

                    // enable or disable invocation from within the description
                    'enable_invocation' => true
                ],
            ],
        ];
    }
}
```
It responds to either http `GET` or `POST` request methods. Other request methods will be ignored and delegated to the
next callable middleware, if available.

In case an http request with method `GET` is received, the component creates and delivers the description in html
format.

Http requests with method `POST` will be treated as calls to the webservice itself, but will only be handled,
if invocation is allowed by the `enable_invocation` flag.

If the flag `enable_invocation` is set to `true` in addition to the description a form gets presented with which you can
input and directly call the webservice with parameters. You can choose to retrieve the raw xml response to your request
or otherwise get a full listing of the request (http headers and soap message in xml) and response (http headers and soap
response in xml).

To make the description available, you have to register a route to the application which delegates the call to describe
the webservice to the soap description component, for example:
```php
$app->route(
    '/path-to-description',
    'MyServiceClass\SoapDescription',
    ['GET', 'POST'],
    'my_service_description'
);
```
