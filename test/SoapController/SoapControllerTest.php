<?php
namespace SoapMiddlewareTest\SoapController;

use SoapMiddleware\SoapController\SoapController;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Soap\Server as SoapServer;
use Zend\Soap\AutoDiscover as WsdlGenerator;
use SoapMiddleware\SoapController\Response\XmlResponse;
use Zend\Soap\Wsdl;

class SoapControllerTest extends TestCase
{
    /**
     * @var ServerRequest
     */
    protected $request;

    /**
     * @var SoapController
     */
    protected $middleware;

    /**
     * @var string
     */
    protected $mockedWsdlContent;

    /**
     * @var string
     */
    protected $mockedServerResponse;

    protected function setUp()
    {
        parent::setUp();

        $this->mockedWsdlContent = file_get_contents(__DIR__ . '/../fixtures/service.wsdl');
        $this->mockedServerResponse = file_get_contents(__DIR__ . '/../fixtures/server-response.xml');

        $this->soapServer = $this->prophesize(SoapServer::class);
        $this->soapServer->setReturnResponse(Argument::any())->willReturn(null);
        $this->soapServer->handle()->willReturn(
            $this->mockedServerResponse
        );

        $this->wsdlGenerator = $this->prophesize(WsdlGenerator::class);

        $this->wsdl = $this->prophesize(Wsdl::class);
        $this->wsdl->toXML()->willReturn(
            $this->mockedWsdlContent
        );

        $this->wsdlGenerator->generate()->willReturn(
            $this->wsdl->reveal()
        );

        $this->request = new ServerRequest([], [], '/', 'GET');

        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->delegate->process(Argument::any())->will(function () {
            return new Response();
        });

        $this->middleware = new SoapController(
            $this->soapServer->reveal(),
            $this->wsdlGenerator->reveal()
        );
    }

    public function testRequestMethodGetReturnsWsdlAsXmlResponse()
    {
        $this->wsdlGenerator->generate()->shouldBeCalled();
        $this->wsdl->toXML()->shouldBeCalled();

        $response = $this->middleware->process(
            $this->request,
            $this->delegate->reveal()
        );

        $prophet = new Prophet();
        $prophet->checkPredictions();

        $this->assertInstanceOf(XmlResponse::class, $response);
        $this->assertEquals(
            $this->mockedWsdlContent,
            $response->getBody()->getContents()
        );
    }

    public function testRequestMethodPostReturnsSoapResponseAsXmlResponse()
    {
        $this->soapServer->handle()->shouldBeCalled();

        $request = $this->request
            ->withMethod('POST');

        $response = $this->middleware->process(
            $request,
            $this->delegate->reveal()
        );

        $this->assertInstanceOf(XmlResponse::class, $response);
        $this->assertEquals(
            $this->mockedServerResponse,
            $response->getBody()->getContents()
        );
    }

    /**
     * @dataProvider httpRequestMethodProvider
     */
    public function testRequestMethodOtherThanGetOrPostInvokeNext($requestMethod)
    {
        $invoked = false;
        $response = new Response();
        $this->request = $this->request->withMethod($requestMethod);

        $this->delegate->process(Argument::any())->will(function () use (&$invoked, $response) {
            $invoked = true;
            return $response;
        });

        $returnedResponse = $this->middleware->process($this->request, $this->delegate->reveal());
        $this->assertSame($response, $returnedResponse);
        $this->assertTrue($invoked);
    }

    public function httpRequestMethodProvider()
    {
        return [
            [ 'HEAD' ],
            [ 'PUT' ],
            [ 'DELETE' ],
            [ 'CONNECT' ],
            [ 'OPTIONS' ],
            [ 'TRACE' ],
            [ 'PATH' ],
        ];
    }
}