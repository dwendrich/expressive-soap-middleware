<?php declare(strict_types=1);
namespace SoapMiddleware\SoapDescription\Reflector;

use SoapMiddleware\SoapDescription\Reflector\ParameterDescription;

/**
 * Class MethodDescription
 *
 * @package SoapMiddleware\SoapDescription\Reflector
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class MethodDescription
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $returnType = 'void';

    /**
     * @var string
     */
    private $returnTypeDescription;

    /**
     * @var array
     */
    private $parameter = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getReturnType(): string
    {
        return (string)$this->returnType;
    }

    /**
     * @param string $returnType
     */
    public function setReturnType(string $returnType)
    {
        $this->returnType = $returnType;
    }

    /**
     * @return string
     */
    public function getReturnTypeDescription(): string
    {
        return (string)$this->returnTypeDescription;
    }

    /**
     * @param string $returnTypeDescription
     */
    public function setReturnTypeDescription(string $returnTypeDescription)
    {
        $this->returnTypeDescription = $returnTypeDescription;
    }

    /**
     * @return array
     */
    public function getParameter(): array
    {
        return $this->parameter;
    }

    /**
     * @param ParameterDescription $parameter
     */
    public function addParameter(ParameterDescription $parameter)
    {
        $this->parameter[$parameter->getName()] = $parameter;
    }
}
