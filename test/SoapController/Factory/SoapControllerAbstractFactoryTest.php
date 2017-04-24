<?php
namespace SoapMiddlewareTest\SoapController\Factory;

use SoapMiddleware\SoapController\SoapController;
use PHPUnit\Framework\TestCase;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\ServiceManager\ServiceManager;
use SoapMiddlewareTest\fixtures\TestService;
use SoapMiddleware\SoapController\Factory\SoapControllerAbstractFactory;

class SoapControllerAbstractFactoryTest extends TestCase
{
    protected $config;
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $this->config = [
            'soap_controller' => [
                'TestService\SoapController' => [
                    'class' => TestService::class,
                    'server_options' => [],
                ],
            ],

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

        $this->container->has('SoapMiddlewareTest\fixtures\TestService')->willReturn(false);

        $serverUrlHelper = $this->prophesize(ServerUrlHelper::class);
        $serverUrlHelper->generate()->willReturn('http://test.service');
        $this->container->get(ServerUrlHelper::class)->willReturn(
            $serverUrlHelper->reveal()
        );
    }

    public function canCreateSoapControllerProvider()
    {
        return [
            ['TestService\SoapController', true],
            ['DummyService\SoapController', false]
        ];
    }

    /**
     * @dataProvider canCreateSoapControllerProvider
     */
    public function testCanCreateSoapController($name, $expected)
    {
        $abstractFactory = new SoapControllerAbstractFactory();
        $bool = $abstractFactory->canCreate($this->container->reveal(), $name);
        $this->assertSame($bool, $expected);
    }

    public function testCreateSoapControllerInstance()
    {
        $abstractFactory = new SoapControllerAbstractFactory();

        /** @var SoapController $soapController */
        $soapController = $abstractFactory->__invoke(
            $this->container->reveal(),
            'TestService\SoapController'
        );

        $this->assertInstanceOf(SoapController::class, $soapController);
    }
}