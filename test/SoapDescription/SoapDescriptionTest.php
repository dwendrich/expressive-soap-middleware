<?php
namespace SoapMiddlewareTest\SoapDescription;

use SoapMiddleware\SoapDescription\Action\SoapDescription;
use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;
use SoapMiddleware\SoapController\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Expressive\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Soap\Client;

class SoapDescriptionTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    protected $request;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new ServerRequest([], [], '/', 'GET');

        $this->client = $this->prophesize(Client::class);
        $this->serviceReflection = $this->prophesize(ServiceDescription::class);

        $this->clientMock = $this->getMockFromWsdl(
            '../fixtures/service.wsdl',
            Client::class,
            '',
            ['getVersion']
        );

        $this->renderer = $this->prophesize(TemplateRendererInterface::class);
        $this->renderer->render(Argument::cetera())->willReturn(
            'renderedHtml'
        );

        $this->delegate = $this->prophesize(DelegateInterface::class);
        $this->delegate->process(Argument::any())->will(function () {
            return new Response();
        });
    }

    public function testRequestMethodPostWithRendererInstanceReturnsHtmlResponse()
    {
        $middleware = new SoapDescription(
            $this->clientMock,
            $this->serviceReflection->reveal(),
            true,
            $this->renderer->reveal()
        );

        $this->request = $this->request
            ->withMethod('POST')
            ->withParsedBody(['method' => 'getVersion']);

        $this->clientMock
            ->expects($this->once())
            ->method('getVersion');

        $response = $middleware->process($this->request, $this->delegate->reveal());

        $this->renderer->render('description::response', Argument::any())->shouldHaveBeenCalled();

        $this->assertInstanceOf(Response\HtmlResponse::class, $response);
        $this->assertEquals('renderedHtml', $response->getBody()->getContents());
    }

    public function testRequestMethodPostWithoutRendererInstanceReturnsXmlResponse()
    {
        $middleware = new SoapDescription(
            $this->clientMock,
            $this->serviceReflection->reveal(),
            true,
            null
        );

        $this->request = $this->request
            ->withMethod('POST')
            ->withParsedBody(['method' => 'getVersion']);

        $this->clientMock
            ->expects($this->once())
            ->method('getVersion');

        $response = $middleware->process($this->request, $this->delegate->reveal());
        $this->assertInstanceOf(XmlResponse::class, $response);
    }

    public function testRequestMethodPostWithRendererInstanceAndXmlResponseFlagReturnsXmlResponse()
    {
        $middleware = new SoapDescription(
            $this->clientMock,
            $this->serviceReflection->reveal(),
            true,
            $this->renderer->reveal()
        );

        $this->request = $this->request
            ->withMethod('POST')
            ->withParsedBody([
                'method' => 'getVersion',
                'output_xml' => true
            ]);

        $this->clientMock
            ->expects($this->once())
            ->method('getVersion');

        $response = $middleware->process($this->request, $this->delegate->reveal());
        $this->assertInstanceOf(XmlResponse::class, $response);
    }

    public function testRequestMethodPostButInvocationNotAllowedReturnsResponseWithStatus403()
    {
        $middleware = new SoapDescription(
            $this->client->reveal(),
            $this->serviceReflection->reveal(),
            false,
            null
        );

        $this->request = $this->request
            ->withMethod('POST');

        $response = $middleware->process($this->request, $this->delegate->reveal());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testRequestMethodGetWithoutRendererReturnsTextResponseWithNotice()
    {
        $middleware = new SoapDescription(
            $this->client->reveal(),
            $this->serviceReflection->reveal(),
            false,
            null
        );

        $response = $middleware->process($this->request, $this->delegate->reveal());
        $this->assertInstanceOf(Response\TextResponse::class, $response);
        $this->assertContains(
            'no template renderer instance available',
            $response->getBody()->getContents(),
            '',
            true
        );
    }

    public function testRequestMethodGetWithRendererInstanceReturnsHtmlResponse()
    {
        $middleware = new SoapDescription(
            $this->client->reveal(),
            $this->serviceReflection->reveal(),
            false,
            $this->renderer->reveal()
        );

        $response = $middleware->process($this->request, $this->delegate->reveal());
        $this->assertInstanceOf(Response\HtmlResponse::class, $response);
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testRequestMethodOtherThanGetOrPostInvokesNext($requestMethod)
    {
        $invoked = false;
        $response = new Response();
        $request  = $this->request->withMethod($requestMethod);

        $this->delegate->process(Argument::any())->will(function () use (&$invoked, $response) {
            $invoked = true;
            return $response;
        });

        $middleware = new SoapDescription(
            $this->client->reveal(),
            $this->serviceReflection->reveal(),
            false,
            null
        );

        $returnedResponse = $middleware->process($request, $this->delegate->reveal());
        $this->assertSame($response, $returnedResponse);
        $this->assertTrue($invoked);
    }

    public function requestMethodProvider()
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