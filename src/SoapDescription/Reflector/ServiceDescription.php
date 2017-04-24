<?php declare(strict_types=1);
namespace SoapMiddleware\SoapDescription\Reflector;

use SoapMiddleware\SoapDescription\Reflector\MethodDescription;

/**
 * Interface ServiceDescriptionInterface
 *
 * @package SoapMiddleware\SoapDescription\Reflector
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class ServiceDescription
{
    /**
     * @var string
     */
    private $fullyQualifiedClassName;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $classDescription;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var array
     */
    private $methods = [];

    public function addMethodDescription(MethodDescription $description)
    {
        $this->methods[$description->getName()] = $description;
    }

    /**
     * @param string $fullyQualifiedClassName
     */
    public function setFullyQualifiedClassName(string $fullyQualifiedClassName)
    {
        $this->fullyQualifiedClassName = $fullyQualifiedClassName;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className)
    {
        $this->className = $className;
    }

    /**
     * @param string $classDescription
     */
    public function setClassDescription(string $classDescription)
    {
        $this->classDescription = $classDescription;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;
    }


    /**
     * @return string the fully qualified service class name
     */
    public function getFullyQualifiedClassName() : string
    {
        return (string)$this->fullyQualifiedClassName;
    }

    /**
     * @return string the service class name
     */
    public function getClassName() : string
    {
        return (string)$this->className;
    }

    /**
     * @return string the full path and filename of the service class
     */
    public function getFileName() : string
    {
        return (string)$this->fileName;
    }

    /**
     * @return string the description as given in the service class doc-block
     */
    public function getClassDescription() : string
    {
        return (string)$this->classDescription;
    }

    /**
     * @return array containing a description of all public available service methods
     *               alphabetically sorted by method name
     */
    public function getMethodDescriptionCollection() : array
    {
        // TODO: this is a poor design decision
        ksort($this->methods, SORT_REGULAR);
        return $this->methods;
    }
}
