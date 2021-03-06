<?php

namespace SoapMiddleware\SoapController\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\InjectContentTypeTrait;
use Zend\Diactoros\Stream;

/**
 * Class XmlResponse
 *
 * @package SoapMiddleware\SoapController\Response
 * @author Daniel Wendrich <daniel.wendrich@gmail.com>
 */
class XmlResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Create a soap response.
     *
     * Produces a soap response with a Content-Type of application/xml
     * and a default status of 200.
     *
     * @param string|StreamInterface $xml XML or stream for the message body.
     * @param int $status Integer status code for the response; 200 by default.
     * @param array $headers Array of headers to use at initialization.
     * @throws InvalidArgumentException if $wsdl is neither a string or stream.
     */
    public function __construct($xml, $status = 200, array $headers = [])
    {
        parent::__construct(
            $this->createBody($xml),
            $status,
            $this->injectContentType('application/xml', $headers)
        );
    }

    /**
     * Create the message body.
     *
     * @param string|StreamInterface $xml
     * @return StreamInterface
     * @throws InvalidArgumentException if $xml is neither a string or stream.
     */
    private function createBody($xml)
    {
        if ($xml instanceof StreamInterface) {
            return $xml;
        }

        if (! is_string($xml)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($xml) ? get_class($xml) : gettype($xml)),
                __CLASS__
            ));
        }

        $body = new Stream('php://temp', 'wb+');
        $body->write($xml);
        $body->rewind();
        return $body;
    }
}
