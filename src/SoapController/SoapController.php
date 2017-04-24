<?php

namespace SoapMiddleware\SoapController;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Soap\AutoDiscover as WsdlGenerator;
use Zend\Soap\Server as SoapServer;
use SoapMiddleware\SoapController\Response\XmlResponse;

/**
 * Class SoapController
 *
 * @package SoapMiddleware\SoapController
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class SoapController implements MiddlewareInterface
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var SoapServer
     */
    protected $soapServer;

    /**
     * @var WsdlGenerator
     */
    protected $wsdlGenerator;

    /**
     * SoapController constructor.
     *
     * @param SoapServer $soapServer
     * @param WsdlGenerator $wsdlGenerator
     */
    public function __construct(SoapServer $soapServer, WsdlGenerator $wsdlGenerator)
    {
        $this->soapServer = $soapServer;
        $this->wsdlGenerator = $wsdlGenerator;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        // generate a repsonse depending on the request method
        $response = $this->generateResponse($request);

        return $response === null
            ? $delegate->process($request)
            : $response;
    }

    private function generateResponse(ServerRequestInterface $request)
    {
        switch ($request->getMethod()) {
            case 'GET':
                /*
                 * In case of get request method the webservice
                 * wsdl should be returned.
                 */
                $wsdl = $this->wsdlGenerator->generate();
                $response = new XmlResponse($wsdl->toXML());
                unset($wsdl);
                break;

            case 'POST':
                /*
                 * In case of post request method handling
                 * of the request is delegated to the soap server.
                 */
                $this->soapServer->setReturnResponse(true);
                $soapResponse = $this->soapServer->handle();
                $response = new XmlResponse($soapResponse);
                break;

            default:
                /*
                 * Calling the SoapController with request methods other
                 * than get/post is not intended.
                 *
                 * This can be avoided by route definition where the only
                 * methods allowed to match the route are get and post.
                 *
                 * In case the middleware is added into the middleware pipeline
                 * the request method can not be restricted.
                 *
                 * If the request method is not get or post we will return
                 * null in order to delegate the request to a possible next
                 * middleware in the stack.
                 */
                $response = null;
                break;
        }

        return $response;
    }
}
