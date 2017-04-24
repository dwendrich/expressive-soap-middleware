<?php
namespace SoapMiddleware\SoapDescription\Reflector;

use Zend\Code\Reflection\DocBlockReflection;
use Zend\Code\Reflection\DocBlock\Tag;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflectorInterface;
use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;

/**
 * Class ServiceReflector
 *
 * @package SoapMiddleware\SoapDescription\Reflector
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class ServiceReflector implements ServiceReflectorInterface
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    public function __construct()
    {
    }

    /**
     * @return \ReflectionMethod[]
     */
    protected function getPublicMethods()
    {
        $methods = [];

        foreach ($this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // exclude constructor, destructor, abstract and magic methods
            if ($method->isConstructor() ||
                $method->isDestructor() ||
                $method->isAbstract() ||
                fnmatch('__*', $method->getName())
            ) {
                continue;
            }
            $methods[] = $method;
        }

        return $methods;
    }

    /**
     * @param string $className
     * @return ServiceDescription
     */
    public function getServiceDescription($className)
    {
        $this->reflection = new \ReflectionClass($className);

        $serviceDescription = new ServiceDescription();
        $serviceDescription->setClassName($this->reflection->getShortName());
        $serviceDescription->setFullyQualifiedClassName($this->reflection->getName());
        $serviceDescription->setFileName($this->reflection->getFileName());

        if (!empty($this->reflection->getDocComment())) {
            $scanner = new DocBlockReflection($this->reflection->getDocComment());
            $serviceDescription->setClassDescription($scanner->getLongDescription());
        }

        foreach ($this->getMethodDescriptions() as $methodDescription) {
            $serviceDescription->addMethodDescription($methodDescription);
        }

        $this->reflection = null;
        return $serviceDescription;
    }

    /**
     * @param \ReflectionMethod $method
     * @param array $paramTags
     * @return array
     */
    protected function getParameterDescription(\ReflectionMethod $method, array &$paramTags)
    {
        $params = [];

        foreach ($method->getParameters() as $parameter) {
            /** @var \ReflectionParameter $parameter */
            $parameterDescription = new ParameterDescription();
            $parameterDescription->setName($parameter->getName());

            if ($parameter->hasType()) {
                $parameterDescription->setType($parameter->getType());
            }

            $parameterDescription->isOptional($parameter->isOptional());
            if ($parameter->isOptional()) {
                $parameterDescription->setDefaultValue(
                    $parameter->allowsNull()
                        ? 'null'
                        : $parameter->getDefaultValue()
                );
            }

            foreach ($paramTags as $tag) {
                if ($tag->getVariableName() == '$' . $parameter->getName()) {
                    $parameterDescription->setDescription($tag->getDescription());
                    break;
                }
            }

            $params[] = $parameterDescription;
        }

        return $params;
    }

    /**
     * @return array
     */
    protected function getMethodDescriptions()
    {
        $pm = [];

        foreach ($this->getPublicMethods() as $method) {
            $methodDescription = new MethodDescription();
            $methodDescription->setName($method->getName());

            $docBlockParameterTags = [];

            /*
             * Method description and return type description depend
             * on method doc-block comment.
             */
            if (!empty($method->getDocComment())) {
                $scanner = new DocBlockReflection($method->getDocComment());

                // set method description
                $methodDescription->setDescription($scanner->getShortDescription());

                // parse return type description from tag description
                $docBlockParameterTags = $scanner->getTags();
                foreach ($docBlockParameterTags as $tag) {
                    if ($tag instanceof Tag\ReturnTag) {
                        $methodDescription->setReturnTypeDescription(
                            $tag->getDescription()
                        );
                        break;
                    }
                }
            }

            // set return type
            $methodDescription->setReturnType(
                $method->hasReturnType()
                    ? (string) $method->getReturnType()
                    : 'void'
            );

            foreach ($this->getParameterDescription($method, $docBlockParameterTags) as $parameterDescription) {
                $methodDescription->addParameter($parameterDescription);
            }

            $pm[] = $methodDescription;
        }

        return $pm;
    }
}
