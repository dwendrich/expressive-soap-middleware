<?php

namespace SoapMiddleware\SoapDescription\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Soap\Client;
use SoapMiddleware\SoapDescription\Action\SoapDescription;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflectorInterface;

class SoapDescriptionAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Can the factory create an instance for the service?
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container, $requestedName);
        return fnmatch('*SoapDescription', $requestedName)
            && isset($config['class'])
            && class_exists($config['class']);
    }

    private function getConfig(ContainerInterface $container, $requestedName)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        return isset($config['soap_description'][$requestedName])
            ? $config['soap_description'][$requestedName]
            : [];
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container, $requestedName);

        $enableInvocation = isset($config['enable_invocation'])
            ? (bool) $config['enable_invocation']
            : false;

        $serviceClass = $config['class'];

        $serviceRouteKey = isset($config['service_route']) ? $config['service_route'] : null;
        if ( empty($serviceRouteKey) ) {
            throw new \InvalidArgumentException("You have to provide a 'service_route' parameter to determine the webservice endpoint.");
        }

        $helper = $container->get(UrlHelper::class);
        $wsdlUri = sprintf(
            '%s://%s%s',
            isset($_SERVER['HTTPS']) ? 'https' : 'http',
            $_SERVER['HTTP_HOST'],
            $helper->generate($serviceRouteKey)
        );

        // fetch instance of template renderer (if present)
        $template = ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null;

        // create service reflector instance
        $reflector = $container->get(ServiceReflectorInterface::class);

        // retrieve optional client options array
        $clientOptions = isset($config['client_options']) ? (array)$config['client_options'] : [];

        // create a soap client instance
        $client = new Client($wsdlUri, $clientOptions);

        // return service description action
        return new SoapDescription(
            $client,
            $reflector->getServiceDescription($serviceClass),
            $enableInvocation,
            $template
        );
    }
}