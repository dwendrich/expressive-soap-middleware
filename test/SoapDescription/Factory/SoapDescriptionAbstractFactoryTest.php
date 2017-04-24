<?php
namespace SoapMiddlewareTest\SoapController\Factory;

use PHPUnit\Framework\TestCase;
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

    /**
     * @codeCoverageIgnore
     */
    public function CreateSoapDescriptionInstance()
    {
        $abstractFactory = new SoapDescriptionAbstractFactory();

        /** @var SoapDescription $soapDescription */
        $soapDescription = $abstractFactory->__invoke(
            $this->container->reveal(),
            'TestService\SoapDescription'
        );

        $this->assertInstanceOf(SoapDescription::class, $soapDescription);
    }
}