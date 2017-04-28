<?php
namespace SoapMiddlewareTest\SoapDescription\Reflector;

use SoapMiddleware\SoapDescription\Reflector\ParameterDescription;
use PHPUnit\Framework\TestCase;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflector;
use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;
use SoapMiddleware\SoapDescription\Reflector\MethodDescription;
use SoapMiddlewareTest\fixtures\TestService;

class ReflectorTest extends TestCase
{
    /**
     * @var ServiceReflector
     */
    protected $reflector;

    /**
     * @var ServiceDescription
     */
    protected $description;

    protected function setUp()
    {
        parent::setUp();

        $reflector = new ServiceReflector();
        $this->description = $reflector->getServiceDescription(TestService::class);
    }

    public function testReflectionCreatingAServiceDescriptionWorks()
    {
        $this->assertInstanceOf(ServiceDescription::class, $this->description);

        $this->assertEquals(
            $this->description->getFullyQualifiedClassName(),
            TestService::class
        );

        $class = substr(
            TestService::class,
            strrpos(TestService::class, '\\') + 1
        );

        $this->assertEquals(
            $this->description->getClassName(),
            $class
        );

        $this->assertEquals(
            $this->description->getClassDescription(),
            'This service is built to demonstrate how to easily setup a webservice based on zend-expressive 2.0.'
        );

        $this->assertContains(
            $class . '.php',
            $this->description->getFileName()
        );
    }

    /**
     * @depends testReflectionCreatingAServiceDescriptionWorks
     */
    public function testServiceReflectionContainsMethodDescriptionCollection()
    {
        $collection = $this->description->getMethodDescriptionCollection();
        $this->assertTrue(is_array($collection));
        $this->assertArrayHasKey('getVersion', $collection);
        $this->assertArrayHasKey('getRandomInt', $collection);
        $this->assertArrayHasKey('reverseString', $collection);
        $this->assertArrayHasKey('doNothing', $collection);
        $this->assertArrayHasKey('testMethod', $collection);
        $this->assertArrayHasKey('testReturnString', $collection);

        $this->assertEquals('int', $collection['getRandomInt']->getReturnType());
        $this->assertEquals('string', $collection['getVersion']->getReturnType());
        $this->assertEquals('string', $collection['reverseString']->getReturnType());
        $this->assertEquals('void', $collection['doNothing']->getReturnType());
        $this->assertEquals('void', $collection['testMethod']->getReturnType());
        $this->assertEquals('string', $collection['testReturnString']->getReturnType());

        $this->assertEquals('', $collection['getRandomInt']->getReturnTypeDescription());
        $this->assertEquals('Returns the current version number as string.', $collection['getVersion']->getReturnTypeDescription());
        $this->assertEquals('The reversed string input.', $collection['reverseString']->getReturnTypeDescription());
        $this->assertEmpty($collection['doNothing']->getReturnTypeDescription());
        $this->assertEmpty($collection['testMethod']->getReturnTypeDescription());
        $this->assertEmpty($collection['testReturnString']->getReturnTypeDescription());

        $this->assertEquals('Create a random positive integer value between min and max.', $collection['getRandomInt']->getDescription());
        $this->assertEquals('Get the current version number.', $collection['getVersion']->getDescription());
        $this->assertEquals('Reverses the given string input.', $collection['reverseString']->getDescription());
        $this->assertEquals('', $collection['doNothing']->getDescription());
        $this->assertEquals('', $collection['testMethod']->getDescription());
        $this->assertEquals('', $collection['testReturnString']->getDescription());

        return $collection;
    }

    /**
     * @depends testServiceReflectionContainsMethodDescriptionCollection
     */
    public function testMethodTestReturnStringParameterDescriptionCollectionIsEmpty(array $methodDescriptionCollection)
    {
        $parameterCollection = $methodDescriptionCollection['testReturnString']->getParameter();
        $this->assertTrue(empty($parameterCollection));
    }

    /**
     * @depends testServiceReflectionContainsMethodDescriptionCollection
     */
    public function testMethodTestMethodParameterDescriptionCollection(array $methodDescriptionCollection)
    {
        $parameterCollection = $methodDescriptionCollection['testMethod']->getParameter();
        $this->assertTrue(is_array($parameterCollection));
        $this->assertArrayHasKey('testValue', $parameterCollection);

        return $parameterCollection['testValue'];
    }

    /**
     * @depends testMethodTestMethodParameterDescriptionCollection
     */
    public function testTestValueParameterDescriptionIsValid(ParameterDescription $parameterDescription)
    {
        $this->assertEquals('testValue', $parameterDescription->getName());
        $this->assertEquals('string', $parameterDescription->getType());
        $this->assertEmpty($parameterDescription->getDescription());
        $this->assertTrue($parameterDescription->isOptional());
        $this->assertEquals('test', $parameterDescription->getDefaultValue());
    }

    /**
     * @depends testServiceReflectionContainsMethodDescriptionCollection
     */
    public function testMethodReverseStringParameterDescriptionCollection(array $methodDescriptionCollection)
    {
        $parameterCollection = $methodDescriptionCollection['reverseString']->getParameter();
        $this->assertTrue(is_array($parameterCollection));
        $this->assertArrayHasKey('input', $parameterCollection);

        return $parameterCollection['input'];
    }

    /**
     * @depends testMethodReverseStringParameterDescriptionCollection
     */
    public function testInputParameterDescriptionIsValid(ParameterDescription $parameterDescription)
    {
        $this->assertEquals('input', $parameterDescription->getName());
        $this->assertEquals('string', $parameterDescription->getType());
        $this->assertEquals('The string to be reversed.', $parameterDescription->getDescription());
        $this->assertFalse($parameterDescription->isOptional());
    }

    /**
     * @depends testServiceReflectionContainsMethodDescriptionCollection
     */
    public function testMethodGetRandomIntParameterDescriptionCollection(array $methodDescriptionCollection)
    {
        $parameterCollection = $methodDescriptionCollection['getRandomInt']->getParameter();
        $this->assertTrue(is_array($parameterCollection));
        $this->assertArrayHasKey('min', $parameterCollection);
        $this->assertArrayHasKey('max', $parameterCollection);

        return $parameterCollection['min'];
    }

    /**
     * @depends testMethodGetRandomIntParameterDescriptionCollection
     */
    public function testMinParameterDescriptionIsValid(ParameterDescription $parameterDescription)
    {
        $this->assertEquals('min', $parameterDescription->getName());
        $this->assertEquals('int', $parameterDescription->getType());
        $this->assertEquals('The minimum value.', $parameterDescription->getDescription());
        $this->assertTrue($parameterDescription->isOptional());
        $this->assertEquals(0, $parameterDescription->getDefaultValue());
    }
}