<?php

namespace SoapMiddleware\SoapController\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Soap\Server;
use Zend\Soap\AutoDiscover;
use SoapMiddleware\SoapController\SoapController;

class SoapControllerAbstractFactory implements AbstractFactoryInterface
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
        return fnmatch('*SoapController', $requestedName)
            && isset($config['class'])
            && class_exists($config['class']);
    }

    private function getConfig(ContainerInterface $container, $requestedName)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        return isset($config['soap_controller'][$requestedName])
            ? $config['soap_controller'][$requestedName]
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

        // an existing class is ensured through the canCreate method
        // which is always called prior to the invocation of this method
        $serviceClass = $config['class'];

        // create webservice uri based on current request uri
        $serverUrlHelper = $container->get(ServerUrlHelper::class);
        $wsdlUri = $serverUrlHelper->generate();

        // retrieve optional server options array
        $serverOptions = isset($config['server_options']) ? (array)$config['server_options'] : [];

        // create instance of soap server object
        $soapServer = new Server($wsdlUri, $serverOptions);

        /*
         * To run and execute the service methods upon invocation
         * the soap server needs an instance of the service class.
         *
         * As there my be dependencies to be resolved, first check
         * if the container knows about the class and how to instantiate
         * it. Otherwise set the class directly in the server.
         */
        if ( $container->has($serviceClass) ) {
            $soapServer->setObject($container->get($serviceClass));
        } else {
            $soapServer->setClass($serviceClass);
        }

        // create an instance of zend soap autodiscover for wsdl generation
        $wsdlGenerator = new AutoDiscover();
        $wsdlGenerator->setUri($wsdlUri);
        $wsdlGenerator->setClass($serviceClass);

        return new SoapController($soapServer, $wsdlGenerator);
    }
}