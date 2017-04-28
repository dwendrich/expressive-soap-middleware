<?php
namespace SoapMiddlewareTest\SoapController\Factory;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;
use SoapMiddleware\SoapDescription\Reflector\ServiceReflectorInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\ServiceManager\ServiceManager;
use SoapMiddlewareTest\fixtures\TestService;
use SoapMiddleware\SoapDescription\Action\SoapDescription;
use SoapMiddleware\SoapDescription\Factory\SoapDescriptionAbstractFactory;

class SoapDescriptionAbstractFactoryTest extends TestCase
{
    protected $config;
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->config = [
            'soap_description' => [
                'TestService\SoapDescription' => [
                    'class' => TestService::class,
                    'client_options' => [],
                    'service_route' => 'test',
                    'enable_invocation' => true
                ],
            ],
        ];

        $this->container = $this->prophesize(ServiceManager::class);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($this->config);
    }

    public function testGetConfigReturnsConfig()
    {
        $abstractFactory = new SoapDescriptionAbstractFactory();

        $reflection = new \ReflectionClass(get_class($abstractFactory));
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);

        $config = $method->invokeArgs($abstractFactory, [
            $this->container->reveal(),
            'TestService\SoapDescription'
        ]);

        $this->assertEquals($config, $this->config['soap_description']['TestService\SoapDescription']);
    }

    public function canCreateSoapDescriptionProvider()
    {
        return [
            ['TestService\SoapDescription', true],
            ['DummyService\SoapDescription', false]
        ];
    }

    /**
     * @dataProvider canCreateSoapDescriptionProvider
     */
    public function testCanCreateSoapDescription($name, $expected)
    {
        $abstractFactory = new SoapDescriptionAbstractFactory();
        $bool = $abstractFactory->canCreate($this->container->reveal(), $name);
        $this->assertSame($bool, $expected);
    }

    public function testCreateSoapDescriptionInstance()
    {
        $abstractFactory = new SoapDescriptionAbstractFactory();

        $urlHelper = $this->prophesize(UrlHelper::class);
        $urlHelper->generate(Argument::any())->willReturn('test');
        $this->container->get(UrlHelper::class)->willReturn(
            $urlHelper->reveal()
        );

        // provide HTTP_HOST for testing purpose
        $_SERVER['HTTP_HOST'] = 'test.local';

        $this->container->has(TemplateRendererInterface::class)->willReturn(false);

        $serviceDescription = $this->prophesize(ServiceDescription::class);
        $serviceReflector = $this->prophesize(ServiceReflectorInterface::class);
        $serviceReflector->getServiceDescription(Argument::any())->willReturn(
            $serviceDescription->reveal()
        );
        $this->container->get(ServiceReflectorInterface::class)->willReturn(
            $serviceReflector->reveal()
        );

        /** @var SoapDescription $soapDescription */
        $soapDescription = $abstractFactory->__invoke(
            $this->container->reveal(),
            'TestService\SoapDescription'
        );

        $this->assertInstanceOf(SoapDescription::class, $soapDescription);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You have to provide a 'service_route' parameter to determine the webservice endpoint.
     */
    public function testCreateSoapDescriptionWithoutServiceRouteKeyThrowsException()
    {
        $config = $this->config;
        unset($config['soap_description']['TestService\SoapDescription']['service_route']);
        $this->container->get('config')->willReturn($config);

        $abstractFactory = new SoapDescriptionAbstractFactory();

        /** @var SoapDescription $soapDescription */
        $soapDescription = $abstractFactory->__invoke(
            $this->container->reveal(),
            'TestService\SoapDescription'
        );
    }
}