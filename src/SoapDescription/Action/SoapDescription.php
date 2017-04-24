<?php

namespace SoapMiddleware\SoapDescription\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Soap\Client;
use SoapMiddleware\SoapController\Response\XmlResponse;
use SoapMiddleware\SoapDescription\Reflector\ServiceDescription;

/**
 * Class SoapDescription
 *
 * @package SoapMiddleware\SoapDescription\Action
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class SoapDescription implements MiddlewareInterface
{
    /**
     * @var TemplateRendererInterface
     */
    protected $renderer;

    /**
     * @var ServiceDescription
     */
    protected $description;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $enableInvocation;

    /**
     * @var string
     */
    protected $templateService = 'description::service';

    /**
     * @var string
     */
    protected $templateMethod = 'description::method';

    /**
     * @var string
     */
    protected $templateResponse = 'description::response';

    /**
     * Action constructor.
     *
     * @param Client $client
     * @param ServiceDescription $description
     * @param bool $enableInvocation
     * @param TemplateRendererInterface|null $renderer
     */
    public function __construct(
        Client $client,
        ServiceDescription $description,
        $enableInvocation = false,
        TemplateRendererInterface $renderer = null
    ) {
        $this->client = $client;
        $this->description = $description;
        $this->enableInvocation = (bool) $enableInvocation;
        $this->renderer = $renderer;
    }

    /**
     * Process an incoming  request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return $this->createDescriptionResponse();

            case 'POST':
                return $this->handleInvocation($request);

            default:
                return $delegate->process($request);
        }
    }

    private function handleInvocation(ServerRequestInterface $request)
    {
        // if invocation is not allowed return http status 403
        if (!$this->enableInvocation) {
            $response = new Response();
            return $response->withStatus(403);  // http status forbidden
        }

        $postData = $request->getParsedBody();

        $method = isset($postData['method']) ? (string) $postData['method'] : 'empty';
        $params = isset($postData['params']) ? $postData['params'] : [];
        $xmlOut = isset($postData['output_xml']) ? (bool) $postData['output_xml'] : false;

        try {
            call_user_func_array([$this->client, $method], $params);
        } catch (\SoapFault $f) {
            // do nothing as soap fault will be presented as last response anyway
        }

        /*
         * if output is requested to be in xml or if no
         * renderer instance is available we return an
         * xml response object
         */
        if ($xmlOut || $this->renderer === null) {
            $xml = $this->client->getLastResponse();

            // TODO: handle empty xml string in case the method return type is void

            $response = new XmlResponse($xml);
            return $response;
        }

        $data = [
            'method'           => $method,
            'protocol'         => ($this->client->getSoapVersion() == 1 ? 'SOAP 1.1' : 'SOAP 1.2'),
            'request_headers'  => $this->client->getLastRequestHeaders(),
            'request_xml'      => $this->client->getLastRequest(),
            'response_headers' => $this->client->getLastResponseHeaders(),
            'response_xml'     => $this->client->getLastResponse()
        ];

        return new HtmlResponse(
            $this->renderer->render($this->templateResponse, $data)
        );
    }

    private function createDescriptionResponse()
    {
        // fallback, in case no renderer instance is present
        if ($this->renderer === null) {
            return new Response\TextResponse(
                'No template renderer instance available to create the service description.',
                200
            );
        }

        $data = [
            'wsdl_uri'          => $this->client->getWSDL(),
            'enable_invocation' => $this->enableInvocation,
            'service'           => $this->description,
            'template_method'   => $this->templateMethod
        ];

        return new HtmlResponse(
            $this->renderer->render($this->templateService, $data)
        );
    }
}
