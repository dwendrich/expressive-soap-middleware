<?php

namespace SoapMiddleware;

use SoapMiddleware\SoapController\Factory\SoapControllerAbstractFactory;
use SoapMiddleware\SoapDescription\Factory\SoapDescriptionAbstractFactory;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflector;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflectorInterface;

/**
 * The configuration provider for the SoapMiddleware module
 *
 * @see https://docs.zendframework.com/zend-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'invokables' => [
                ServiceReflectorInterface::class => ServiceReflector::class,
            ],
            'factories'  => [
            ],
            'abstract_factories' => [
                SoapControllerAbstractFactory::class,
                SoapDescriptionAbstractFactory::class,
            ]
        ];
    }

    /**
     * Returns the templates configuration
     *
     * @return array
     */
    public function getTemplates()
    {
        return [
            'paths' => [
                'description' => [__DIR__ . '/../templates/description'],
                'error'       => [__DIR__ . '/../templates/error'],
                'layout'      => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }
}
