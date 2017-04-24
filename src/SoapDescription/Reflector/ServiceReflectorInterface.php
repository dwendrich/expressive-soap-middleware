<?php
namespace SoapMiddleware\SoapDescription\Reflector;

use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;

/**
 * Interface ServiceReflectorInterface
 *
 * @package SoapMiddleware\SoapDescription\Reflector
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
interface ServiceReflectorInterface
{
    /**
     * Creates and returns a service description object
     * for a given class name.
     *
     * @param string $className
     * @return ServiceDescription
     */
    public function getServiceDescription($className);
}